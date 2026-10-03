import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/media_url_resolver.dart';
import '../../../complaints/application/complaints_providers.dart';
import '../../../complaints/domain/entities/complaint_entity.dart';
import '../../application/field_work_state.dart';
import '../../application/providers/field_work_providers.dart';
import '../../domain/entities/field_assignment_entity.dart';

class TaskDetailsPage extends ConsumerStatefulWidget {
  final int assignmentId;
  final FieldWorkAssignmentEntity? initialAssignment;

  const TaskDetailsPage({
    super.key,
    required this.assignmentId,
    this.initialAssignment,
  });

  @override
  ConsumerState<TaskDetailsPage> createState() => _TaskDetailsPageState();
}

class _TaskDetailsPageState extends ConsumerState<TaskDetailsPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref
          .read(fieldWorkNotifierProvider.notifier)
          .selectAssignment(widget.assignmentId);

      final state = ref.read(fieldWorkNotifierProvider);
      if (state.assignments.isEmpty) {
        ref.read(fieldWorkNotifierProvider.notifier).loadAssignments();
      }
    });
  }

  Future<void> _handleAccept(FieldWorkAssignmentEntity assignment) async {
    final notifier = ref.read(fieldWorkNotifierProvider.notifier);
    final success = await notifier.acceptAssignment(assignment.id);

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('تم قبول المهمة بنجاح، يمكنك الآن الانتقال لموقع البلاغ لبدء التنفيذ.'),
          backgroundColor: AppColors.yemenEmerald,
        ),
      );
    } else {
      final error = ref.read(fieldWorkNotifierProvider).errorMessage;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error ?? 'فشل قبول المهمة.'),
          backgroundColor: AppColors.yemenRed,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(fieldWorkNotifierProvider);

    FieldWorkAssignmentEntity? assignment =
        widget.initialAssignment ?? state.selectedAssignment;
    if (assignment == null || assignment.id != widget.assignmentId) {
      final match = state.assignments.where((a) => a.id == widget.assignmentId);
      if (match.isNotEmpty) {
        assignment = match.first;
      }
    }

    if (assignment == null) {
      if (state.isLoading) {
        return const Scaffold(
          backgroundColor: AppColors.backgroundSubtle,
          body: Center(
            child: CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppColors.yemenRed),
            ),
          ),
        );
      }

      return Scaffold(
        backgroundColor: AppColors.backgroundSubtle,
        appBar: AppBar(
          title: const Text('تفاصيل المهمة'),
          backgroundColor: Colors.white,
          elevation: 0,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.assignment_late_outlined,
                  size: 48, color: AppColors.textMuted),
              const SizedBox(height: 12),
              const Text(
                'لم يتم العثور على المهمة المحددة',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => context.pop(),
                child: const Text('العودة للمهام'),
              ),
            ],
          ),
        ),
      );
    }

    final complaintAsync =
        ref.watch(complaintDetailsProvider(assignment.complaintId));

    // Prefer freshly loaded complaint details from network, fallback to eager-loaded complaint
    final effectiveComplaint = complaintAsync.value ?? assignment.complaint;

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
          'مهمة صيانة #${assignment.id}',
          style: const TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'تحديث البيانات',
            icon: const Icon(Icons.refresh_rounded, color: AppColors.yemenBlack),
            onPressed: () {
              ref.invalidate(complaintDetailsProvider(assignment!.complaintId));
              ref.read(fieldWorkNotifierProvider.notifier).loadAssignments();
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Obsidian Meta Banner
              _buildTopMetaBanner(assignment, effectiveComplaint),

              const SizedBox(height: 16),

              // 2. Complaint Info Card
              if (effectiveComplaint != null)
                _buildComplaintCard(effectiveComplaint)
              else if (complaintAsync.isLoading)
                _buildComplaintSkeleton()
              else
                _buildFallbackComplaintCard(assignment),

              const SizedBox(height: 16),

              // 3. Location & Coordinates Card
              if (effectiveComplaint != null)
                _buildLocationCard(effectiveComplaint)
              else if (complaintAsync.isLoading)
                _buildLocationSkeleton(),

              const SizedBox(height: 16),

              // 4. Evidence Attachments Card
              if (effectiveComplaint != null)
                _buildEvidenceCard(effectiveComplaint)
              else if (complaintAsync.isLoading)
                _buildEvidenceSkeleton(),

              const SizedBox(height: 16),

              // 5. Assignment Workflow & Timeline Card
              _buildWorkflowCard(assignment),

              const SizedBox(height: 80),
            ],
          ),
        ),
      ),
      bottomNavigationBar: _buildBottomActionBar(assignment, state),
    );
  }

  Widget _buildTopMetaBanner(
      FieldWorkAssignmentEntity assignment, ComplaintEntity? effectiveComplaint) {
    Color statusColor;
    IconData statusIcon;

    switch (assignment.status) {
      case 'pending':
        statusColor = AppColors.yemenGold;
        statusIcon = Icons.hourglass_top_rounded;
        break;
      case 'accepted':
        statusColor = const Color(0xFF2563EB);
        statusIcon = Icons.thumb_up_alt_rounded;
        break;
      case 'in_progress':
        statusColor = AppColors.yemenRed;
        statusIcon = Icons.engineering_rounded;
        break;
      case 'completed':
        statusColor = AppColors.yemenEmerald;
        statusIcon = Icons.verified_rounded;
        break;
      default:
        statusColor = AppColors.textSecondary;
        statusIcon = Icons.info_outline_rounded;
    }

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [
            AppColors.yemenBlackLight,
            AppColors.yemenBlack,
          ],
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.15),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  'تكليف ميداني #${assignment.id}',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.25),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: statusColor.withValues(alpha: 0.5)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(statusIcon, size: 14, color: statusColor),
                    const SizedBox(width: 4),
                    Text(
                      assignment.statusArabic,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: statusColor,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 14),

          Text(
            effectiveComplaint?.title ??
                assignment.complaint?.title ??
                'بلاغ #${assignment.complaintId}',
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w800,
              color: Colors.white,
              height: 1.3,
            ),
          ),

          const SizedBox(height: 10),

          Row(
            children: [
              const Icon(Icons.schedule_rounded,
                  size: 14, color: AppColors.textMuted),
              const SizedBox(width: 6),
              Text(
                'تاريخ الإسناد: ${_formatDate(assignment.createdAt)}',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.white.withValues(alpha: 0.7),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildComplaintCard(ComplaintEntity complaint) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'تفاصيل البلاغ والمعاينة',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: AppColors.deepBlack,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: AppColors.backgroundSubtle,
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: AppColors.borderSubtle),
                ),
                child: Text(
                  complaint.complaintNumber,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),
              ),
            ],
          ),

          const Divider(height: 20, color: AppColors.borderSubtle),

          if (complaint.category != null) ...[
            Row(
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.blue.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.category_outlined,
                          size: 14, color: Color(0xFF1D4ED8)),
                      const SizedBox(width: 4),
                      Text(
                        complaint.category!.name,
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF1D4ED8),
                        ),
                      ),
                    ],
                  ),
                ),
                if (complaint.departmentName != null) ...[
                  const SizedBox(width: 8),
                  Flexible(
                    child: Text(
                      complaint.departmentName!,
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ],
            ),
            const SizedBox(height: 12),
          ],

          const Text(
            'شرح المشكلة والمطلوب معالجته:',
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.bold,
              color: AppColors.textMuted,
            ),
          ),
          const SizedBox(height: 6),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.backgroundSubtle,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppColors.borderSubtle),
            ),
            child: Text(
              complaint.description,
              style: const TextStyle(
                fontSize: 13,
                color: AppColors.textPrimary,
                height: 1.5,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLocationCard(ComplaintEntity complaint) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.pin_drop_rounded, size: 20, color: Color(0xFF2563EB)),
                  SizedBox(width: 8),
                  Text(
                    'الموقع الجغرافي للبلاغ',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: AppColors.deepBlack,
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.blue.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Text(
                  'نطاق 500م',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1D4ED8),
                  ),
                ),
              ),
            ],
          ),

          const Divider(height: 20, color: AppColors.borderSubtle),

          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.blue.withValues(alpha: 0.04),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: Colors.blue.withValues(alpha: 0.2)),
            ),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'خط العرض (Latitude):',
                      style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                    ),
                    Text(
                      complaint.latitude.toStringAsFixed(6),
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: AppColors.deepBlack,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'خط الطول (Longitude):',
                      style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                    ),
                    Text(
                      complaint.longitude.toStringAsFixed(6),
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: AppColors.deepBlack,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          const Row(
            children: [
              Icon(Icons.info_outline_rounded, size: 14, color: AppColors.textMuted),
              SizedBox(width: 6),
              Expanded(
                child: Text(
                  'يتطلب النظام التواجد في الموقع الفعلي والتحقق بالـ GPS للتمكن من مباشرة التنفيذ.',
                  style: TextStyle(fontSize: 11, color: AppColors.textMuted),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildEvidenceCard(ComplaintEntity complaint) {
    if (complaint.attachments.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.borderSubtle),
        ),
        child: const Row(
          children: [
            Icon(Icons.photo_outlined, size: 20, color: AppColors.textMuted),
            SizedBox(width: 8),
            Text(
              'لا توجد صور أولية مرفقة مع هذا البلاغ.',
              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'الصور والأدلة الأولية المرفقة',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: AppColors.deepBlack,
                ),
              ),
              Text(
                '${complaint.attachments.length} مرفقات',
                style: const TextStyle(fontSize: 12, color: AppColors.textMuted),
              ),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 110,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: complaint.attachments.length,
              separatorBuilder: (context, index) => const SizedBox(width: 10),
              itemBuilder: (context, index) {
                final attachment = complaint.attachments[index];
                final url = MediaUrlResolver.resolve(
                    attachment.fileUrl ?? attachment.filePath);

                return ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: InkWell(
                    onTap: url != null
                        ? () => _showImageDialog(context, url)
                        : null,
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      width: 110,
                      height: 110,
                      color: AppColors.backgroundSubtle,
                      child: url != null
                          ? Image.network(
                              url,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) =>
                                  const Center(
                                child: Icon(Icons.broken_image_rounded,
                                    color: AppColors.textMuted),
                              ),
                            )
                          : const Center(
                              child: Icon(Icons.image_not_supported_rounded,
                                  color: AppColors.textMuted),
                            ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildWorkflowCard(FieldWorkAssignmentEntity assignment) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'سجل التكليف والمتابعة',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: AppColors.deepBlack,
            ),
          ),
          const Divider(height: 20, color: AppColors.borderSubtle),
          _buildMetaRow('الموظف المكلف:', assignment.workerName ?? 'أنت (المسند إليه)'),
          const SizedBox(height: 10),
          _buildMetaRow('جهة الإسناد:', assignment.assignedByName ?? 'الإدارة المختصة'),
          const SizedBox(height: 10),
          _buildMetaRow('وقت الإسناد:', _formatDate(assignment.createdAt)),
          if (assignment.startedAt != null) ...[
            const SizedBox(height: 10),
            _buildMetaRow('وقت البدء:', _formatDate(assignment.startedAt)),
          ],
          if (assignment.completedAt != null) ...[
            const SizedBox(height: 10),
            _buildMetaRow('وقت الإنجاز:', _formatDate(assignment.completedAt)),
          ],
          if (assignment.notes != null && assignment.notes!.isNotEmpty) ...[
            const SizedBox(height: 12),
            const Text(
              'تقرير الإنجاز الميداني:',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: AppColors.textMuted,
              ),
            ),
            const SizedBox(height: 4),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.backgroundSubtle,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                assignment.notes!,
                style: const TextStyle(fontSize: 13, height: 1.4),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildMetaRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
        ),
        Text(
          value,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
      ],
    );
  }

  Widget _buildFallbackComplaintCard(FieldWorkAssignmentEntity assignment) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'بيانات البلاغ',
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.bold,
              color: AppColors.deepBlack,
            ),
          ),
          const Divider(height: 20, color: AppColors.borderSubtle),
          Text(
            assignment.complaint?.title ?? 'بلاغ رقم #${assignment.complaintId}',
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
          if (assignment.complaint?.description != null &&
              assignment.complaint!.description.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(
              assignment.complaint!.description,
              style: const TextStyle(
                fontSize: 13,
                color: AppColors.textSecondary,
                height: 1.4,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildComplaintSkeleton() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                width: 120,
                height: 16,
                decoration: BoxDecoration(
                  color: AppColors.backgroundSubtle,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
              Container(
                width: 60,
                height: 16,
                decoration: BoxDecoration(
                  color: AppColors.backgroundSubtle,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Container(
            width: double.infinity,
            height: 14,
            decoration: BoxDecoration(
              color: AppColors.backgroundSubtle,
              borderRadius: BorderRadius.circular(4),
            ),
          ),
          const SizedBox(height: 8),
          Container(
            width: 200,
            height: 14,
            decoration: BoxDecoration(
              color: AppColors.backgroundSubtle,
              borderRadius: BorderRadius.circular(4),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLocationSkeleton() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Row(
        children: [
          const Icon(Icons.pin_drop_rounded, size: 20, color: Color(0xFF2563EB)),
          const SizedBox(width: 8),
          Container(
            width: 140,
            height: 14,
            decoration: BoxDecoration(
              color: AppColors.backgroundSubtle,
              borderRadius: BorderRadius.circular(4),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEvidenceSkeleton() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Row(
        children: [
          const Icon(Icons.photo_outlined, size: 20, color: AppColors.textMuted),
          const SizedBox(width: 8),
          Container(
            width: 120,
            height: 14,
            decoration: BoxDecoration(
              color: AppColors.backgroundSubtle,
              borderRadius: BorderRadius.circular(4),
            ),
          ),
        ],
      ),
    );
  }

  void _showImageDialog(BuildContext context, String imageUrl) {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(16),
        child: Stack(
          alignment: Alignment.center,
          children: [
            InteractiveViewer(
              panEnabled: true,
              minScale: 0.5,
              maxScale: 4.0,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Image.network(
                  imageUrl,
                  fit: BoxFit.contain,
                  loadingBuilder: (context, child, progress) {
                    if (progress == null) return child;
                    return const Center(
                      child: CircularProgressIndicator(color: AppColors.yemenRed),
                    );
                  },
                  errorBuilder: (context, error, stackTrace) => Container(
                    padding: const EdgeInsets.all(32),
                    color: Colors.white,
                    child: const Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.broken_image_rounded,
                            size: 48, color: AppColors.textMuted),
                        SizedBox(height: 12),
                        Text('تعذر تحميل الصورة بدقة كاملة',
                            style: TextStyle(color: AppColors.textSecondary)),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            Positioned(
              top: 10,
              right: 10,
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.6),
                  shape: BoxShape.circle,
                ),
                child: IconButton(
                  icon: const Icon(Icons.close_rounded, color: Colors.white),
                  onPressed: () => Navigator.of(ctx).pop(),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget? _buildBottomActionBar(
      FieldWorkAssignmentEntity assignment, FieldWorkState state) {
    if (assignment.isCompleted) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: AppColors.borderSubtle)),
        ),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            color: AppColors.yemenEmerald.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.check_circle_rounded,
                  color: AppColors.yemenEmerald, size: 20),
              SizedBox(width: 8),
              Text(
                'تم إتمام هذه المهمة وحل البلاغ بنجاح',
                style: TextStyle(
                  color: AppColors.yemenEmerald,
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (assignment.isPending) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: AppColors.borderSubtle)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: ElevatedButton.icon(
          onPressed: state.isAccepting ? null : () => _handleAccept(assignment),
          icon: state.isAccepting
              ? const SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                )
              : const Icon(Icons.thumb_up_alt_rounded, size: 20),
          label: Text(
            state.isAccepting ? 'جارٍ قبول المهمة...' : 'قبول المهمة الميدانية',
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
          ),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.yemenGold,
            foregroundColor: Colors.black,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
      );
    }

    if (assignment.isAccepted || assignment.isInProgress) {
      final isProgress = assignment.isInProgress;
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: AppColors.borderSubtle)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: ElevatedButton.icon(
          onPressed: () {
            context.push('/tasks/${assignment.id}/execute', extra: assignment);
          },
          icon: Icon(
            isProgress ? Icons.engineering_rounded : Icons.radar_rounded,
            size: 20,
          ),
          label: Text(
            isProgress ? 'متابعة العمل الميداني' : 'الانتقال للتنفيذ والتحقق الجغرافي',
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
          ),
          style: ElevatedButton.styleFrom(
            backgroundColor:
                isProgress ? AppColors.yemenRed : const Color(0xFF2563EB),
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
      );
    }

    return null;
  }

  String _formatDate(DateTime? date) {
    if (date == null) return '—';
    try {
      return DateFormat('yyyy/MM/dd  HH:mm', 'ar').format(date);
    } catch (_) {
      return DateFormat('yyyy/MM/dd  HH:mm').format(date);
    }
  }
}
