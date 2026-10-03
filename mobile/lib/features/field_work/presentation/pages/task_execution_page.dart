import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../complaints/application/complaints_providers.dart';
import '../../application/field_work_state.dart';
import '../../application/providers/field_work_providers.dart';
import '../../domain/entities/field_assignment_entity.dart';
import '../widgets/after_photo_capture.dart';
import '../widgets/location_verifier.dart';

class TaskExecutionPage extends ConsumerStatefulWidget {
  final int assignmentId;

  const TaskExecutionPage({
    super.key,
    required this.assignmentId,
  });

  @override
  ConsumerState<TaskExecutionPage> createState() => _TaskExecutionPageState();
}

class _TaskExecutionPageState extends ConsumerState<TaskExecutionPage> {
  final TextEditingController _reportController = TextEditingController();
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  double? _currentLatitude;
  double? _currentLongitude;
  List<String> _afterPhotos = [];
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref
          .read(fieldWorkNotifierProvider.notifier)
          .selectAssignment(widget.assignmentId);
    });
  }

  @override
  void dispose() {
    _reportController.dispose();
    super.dispose();
  }

  Future<void> _handleStart(FieldWorkAssignmentEntity assignment) async {
    final notifier = ref.read(fieldWorkNotifierProvider.notifier);
    final success = await notifier.startAssignment(assignment.id);

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('تم تسجيل بدء المهمة الميدانية بنجاح، باشر الإصلاح الآن.'),
          backgroundColor: AppColors.yemenEmerald,
        ),
      );
    } else {
      final error = ref.read(fieldWorkNotifierProvider).errorMessage;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error ?? 'تعذر تسجيل بدء المهمة.'),
          backgroundColor: AppColors.yemenRed,
        ),
      );
    }
  }

  Future<void> _handleComplete(FieldWorkAssignmentEntity assignment) async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_currentLatitude == null || _currentLongitude == null) {
      // If coordinates not cached yet, try to obtain them now
      try {
        final loc =
            await ref.read(locationServiceProvider).getCurrentLocation();
        _currentLatitude = loc.latitude;
        _currentLongitude = loc.longitude;
      } catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
                'تعذر الحصول على الموقع الجغرافي الحالي لإتمام المهمة: ${e.toString()}'),
            backgroundColor: AppColors.yemenRed,
          ),
        );
        return;
      }
    }

    setState(() {
      _isSubmitting = true;
    });

    final notifier = ref.read(fieldWorkNotifierProvider.notifier);
    final success = await notifier.completeAssignment(
      assignmentId: assignment.id,
      report: _reportController.text.trim(),
      latitude: _currentLatitude!,
      longitude: _currentLongitude!,
      attachmentPaths: _afterPhotos.isNotEmpty ? _afterPhotos : null,
    );

    if (!mounted) return;

    setState(() {
      _isSubmitting = false;
    });

    if (success) {
      // Invalidate complaints providers so tracking shows updated status 'resolved'
      ref.invalidate(complaintDetailsProvider(assignment.complaintId));
      ref.invalidate(myComplaintsProvider);
      ref.invalidate(recentComplaintsProvider);

      if (!mounted) return;

      // Show success modal then navigate back
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.yemenEmerald.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.check_circle_rounded,
                  size: 48,
                  color: AppColors.yemenEmerald,
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'تم إتمام المهمة بنجاح!',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: AppColors.deepBlack,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'تم توثيق إنجاز المعالجة الميدانية، وتحديث حالة البلاغ إلى "تم الحل" بنجاح.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  color: AppColors.textSecondary,
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () {
                  Navigator.of(ctx).pop();
                  context.go('/tasks');
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.deepBlack,
                  foregroundColor: Colors.white,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                child: const Text('العودة لقائمة المهام'),
              ),
            ],
          ),
        ),
      );
    } else {
      final error = ref.read(fieldWorkNotifierProvider).errorMessage;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error ?? 'فشل إتمام المهمة.'),
          backgroundColor: AppColors.yemenRed,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(fieldWorkNotifierProvider);

    FieldWorkAssignmentEntity? assignment = state.selectedAssignment;
    if (assignment == null || assignment.id != widget.assignmentId) {
      final match = state.assignments.where((a) => a.id == widget.assignmentId);
      if (match.isNotEmpty) {
        assignment = match.first;
      }
    }

    if (assignment == null) {
      return const Scaffold(
        backgroundColor: AppColors.backgroundSubtle,
        body: Center(child: CircularProgressIndicator()),
      );
    }

    final isLocationVerified = state.isLocationVerified;
    final isAlreadyInProgress = assignment.isInProgress;
    final isCompleted = assignment.isCompleted;

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded,
              color: AppColors.yemenBlack, size: 20),
          onPressed: () => context.pop(),
        ),
        title: Text(
          'تنفيذ المهمة #${assignment.id}',
          style: const TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
      ),
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // 1. Task Brief Header
                _buildHeader(assignment),

                const SizedBox(height: 16),

                // 2. Step 1: Location Verification
                _buildSectionCard(
                  stepNumber: '1',
                  title: 'التحقق من الموقع الجغرافي',
                  subtitle: 'يجب التواجد في نطاق 500 متر من موقع البلاغ',
                  isCompleted: isLocationVerified || isAlreadyInProgress,
                  child: LocationVerifier(
                    assignmentId: assignment.id,
                    onLocationVerified: (loc) {
                      setState(() {
                        _currentLatitude = loc.latitude;
                        _currentLongitude = loc.longitude;
                      });
                    },
                  ),
                ),

                const SizedBox(height: 16),

                // 3. Step 2: Start Execution Action
                _buildSectionCard(
                  stepNumber: '2',
                  title: 'بدء التنفيذ الميداني',
                  subtitle: 'تسجيل بدء العمل بعد استيفاء التحقق الجغرافي',
                  isCompleted: isAlreadyInProgress || isCompleted,
                  child: _buildStartSection(assignment, state, isLocationVerified),
                ),

                const SizedBox(height: 16),

                // 4. Step 3: After Repair Evidence (Camera)
                _buildSectionCard(
                  stepNumber: '3',
                  title: 'توثيق ما بعد الإصلاح',
                  subtitle: 'التقاط صور إثبات المعالجة الميدانية عبر الكاميرا',
                  isCompleted: _afterPhotos.isNotEmpty || isCompleted,
                  child: AfterPhotoCapture(
                    initialPhotos: _afterPhotos,
                    onPhotosChanged: (photos) {
                      setState(() {
                        _afterPhotos = photos;
                      });
                    },
                    onLocationCaptured: (lat, lng) {
                      _currentLatitude = lat;
                      _currentLongitude = lng;
                    },
                  ),
                ),

                const SizedBox(height: 16),

                // 5. Step 4: Completion Notes / Report
                _buildSectionCard(
                  stepNumber: '4',
                  title: 'تقرير الإنجاز الميداني',
                  subtitle: 'وصف الإجراءات والأعمال التي تمت لمعالجة البلاغ',
                  isCompleted: _reportController.text.trim().length >= 10,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      TextFormField(
                        controller: _reportController,
                        maxLines: 4,
                        minLines: 3,
                        maxLength: 3000,
                        enabled: !isCompleted && !_isSubmitting,
                        decoration: InputDecoration(
                          hintText:
                              'اكتب تقريراً موجزاً يوضح ما تم إنجازه في الميدان (10 أحرف على الأقل)...',
                          hintStyle: const TextStyle(
                            fontSize: 12,
                            color: AppColors.textMuted,
                          ),
                          filled: true,
                          fillColor: AppColors.backgroundSubtle,
                          contentPadding: const EdgeInsets.all(14),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide:
                                const BorderSide(color: AppColors.borderSubtle),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide:
                                const BorderSide(color: AppColors.borderSubtle),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide:
                                const BorderSide(color: AppColors.deepBlack),
                          ),
                        ),
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'تقرير الإنجاز مطلوب لإتمام المهمة.';
                          }
                          if (value.trim().length < 10) {
                            return 'التقرير يجب ألا يقل عن 10 أحرف لتوضيح العمل.';
                          }
                          return null;
                        },
                        onChanged: (_) => setState(() {}),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 24),

                // 6. Complete Task Button
                _buildCompleteButton(assignment, state),

                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(FieldWorkAssignmentEntity assignment) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.deepBlack.withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.assignment_outlined,
              size: 24,
              color: AppColors.deepBlack,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  assignment.complaint?.title ?? 'بلاغ #${assignment.complaintId}',
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 2),
                Text(
                  'الحالة الحالية: ${assignment.statusArabic}',
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionCard({
    required String stepNumber,
    required String title,
    required String subtitle,
    required bool isCompleted,
    required Widget child,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Container(
                width: 26,
                height: 26,
                decoration: BoxDecoration(
                  color: isCompleted
                      ? AppColors.yemenEmerald
                      : AppColors.deepBlack,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: isCompleted
                      ? const Icon(Icons.check, size: 16, color: Colors.white)
                      : Text(
                          stepNumber,
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    Text(
                      subtitle,
                      style: const TextStyle(
                        fontSize: 11,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const Divider(height: 20, color: AppColors.borderSubtle),
          child,
        ],
      ),
    );
  }

  Widget _buildStartSection(
    FieldWorkAssignmentEntity assignment,
    FieldWorkState state,
    bool isLocationVerified,
  ) {
    if (assignment.isInProgress || assignment.isCompleted) {
      return Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: AppColors.yemenEmerald.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
              color: AppColors.yemenEmerald.withValues(alpha: 0.3)),
        ),
        child: const Row(
          children: [
            Icon(Icons.check_circle_rounded,
                color: AppColors.yemenEmerald, size: 20),
            SizedBox(width: 10),
            Expanded(
              child: Text(
                'تم بدء المهمة رسمياً في النظام، والعمل الميداني جارٍ حالياً.',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: AppColors.yemenEmerald,
                ),
              ),
            ),
          ],
        ),
      );
    }

    // Still in accepted / pending state
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (!isLocationVerified)
          Container(
            padding: const EdgeInsets.all(10),
            margin: const EdgeInsets.only(bottom: 12),
            decoration: BoxDecoration(
              color: AppColors.yemenGold.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(
                  color: AppColors.yemenGold.withValues(alpha: 0.3)),
            ),
            child: const Row(
              children: [
                Icon(Icons.lock_clock_rounded,
                    size: 16, color: AppColors.deepBlack),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'زر بدء التنفيذ معطّل حتى يتم التحقق الجغرافي من تواجدك بموقع البلاغ.',
                    style: TextStyle(fontSize: 11, color: AppColors.deepBlack),
                  ),
                ),
              ],
            ),
          ),
        ElevatedButton.icon(
          onPressed: (!isLocationVerified || state.isStarting)
              ? null
              : () => _handleStart(assignment),
          icon: state.isStarting
              ? const SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                )
              : const Icon(Icons.play_arrow_rounded, size: 22),
          label: Text(
            state.isStarting ? 'جارٍ تسجيل البدء...' : 'بدء تنفيذ المهمة',
          ),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.deepBlack,
            foregroundColor: Colors.white,
            disabledBackgroundColor: Colors.black.withValues(alpha: 0.08),
            disabledForegroundColor: AppColors.textMuted,
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildCompleteButton(
    FieldWorkAssignmentEntity assignment,
    FieldWorkState state,
  ) {
    if (assignment.isCompleted) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.yemenEmerald.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
              color: AppColors.yemenEmerald.withValues(alpha: 0.3)),
        ),
        child: const Center(
          child: Text(
            'هذه المهمة مكتملة ومحفوظة في النظام.',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: AppColors.yemenEmerald,
            ),
          ),
        ),
      );
    }

    final canComplete = assignment.isInProgress && !_isSubmitting;

    return ElevatedButton.icon(
      onPressed: canComplete ? () => _handleComplete(assignment) : null,
      icon: _isSubmitting
          ? const SizedBox(
              width: 18,
              height: 18,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
              ),
            )
          : const Icon(Icons.check_circle_outline_rounded, size: 22),
      label: Text(
        _isSubmitting ? 'جارٍ إتمام المهمة ورفع الإثبات...' : 'إتمام المهمة وحل البلاغ',
        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
      ),
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.yemenEmerald,
        foregroundColor: Colors.white,
        disabledBackgroundColor: Colors.black.withValues(alpha: 0.1),
        disabledForegroundColor: AppColors.textMuted,
        padding: const EdgeInsets.symmetric(vertical: 16),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
    );
  }
}
