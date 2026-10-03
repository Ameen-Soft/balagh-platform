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

  const TaskDetailsPage({
    super.key,
    required this.assignmentId,
  });

  @override
  ConsumerState<TaskDetailsPage> createState() => _TaskDetailsPageState();
}

class _TaskDetailsPageState extends ConsumerState<TaskDetailsPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final state = ref.read(fieldWorkNotifierProvider);
      if (state.assignments.isEmpty) {
        ref.read(fieldWorkNotifierProvider.notifier).loadAssignments().then((_) {
          if (mounted) {
            ref
                .read(fieldWorkNotifierProvider.notifier)
                .selectAssignment(widget.assignmentId);
          }
        });
      } else {
        ref
            .read(fieldWorkNotifierProvider.notifier)
            .selectAssignment(widget.assignmentId);
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
          content: Text('تم قبول المهمة بنجاح، يمكنك الآن الانتقال لموقع البلاغ.'),
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

    // Find the current assignment from state
    FieldWorkAssignmentEntity? assignment = state.selectedAssignment;
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

    // Also fetch the full complaint details for rich details (evidence, timeline)
    final complaintAsync =
        ref.watch(complaintDetailsProvider(assignment.complaintId));

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
          'مهمة ميدانية #${assignment.id}',
          style: const TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'تحديث',
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
              // 1. Status Banner
              _buildStatusBanner(assignment),

              const SizedBox(height: 16),

              // 2. Complaint Info Card
              complaintAsync.when(
                data: (complaint) => _buildComplaintCard(complaint),
                loading: () => Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.borderSubtle),
                  ),
                  child: const Center(
                    child: Padding(
                      padding: EdgeInsets.all(20.0),
                      child: CircularProgressIndicator(strokeWidth: 2),
                    ),
                  ),
                ),
                error: (error, stack) => _buildFallbackComplaintCard(assignment!),
              ),

              const SizedBox(height: 16),

              // 3. Evidence / Attachments Card
              complaintAsync.when(
                data: (complaint) => _buildEvidenceCard(complaint),
                loading: () => const SizedBox.shrink(),
                error: (error, stack) => const SizedBox.shrink(),
              ),

              const SizedBox(height: 16),

              // 4. Assignment Metadata Card
              _buildAssignmentMetaCard(assignment),

              const SizedBox(height: 80), // spacing for bottom bar
            ],
          ),
        ),
      ),
      bottomNavigationBar: _buildBottomActionBar(assignment, state),
    );
  }

  Widget _buildStatusBanner(FieldWorkAssignmentEntity assignment) {
    Color color;
    IconData icon;
    String description;

    switch (assignment.status) {
      case 'pending':
        color = AppColors.yemenGold;
        icon = Icons.pending_actions_rounded;
        description =
            'المهمة بانتظار قبولك. يرجى مراجعة التفاصيل والضغط على قبول المهمة.';
        break;
      case 'accepted':
        color = const Color(0xFF2563EB);
        icon = Icons.directions_run_rounded;
        description =
            'تم قبول المهمة. توجه إلى موقع البلاغ للتحقق الجغرافي والبدء.';
        break;
      case 'in_progress':
        color = AppColors.yemenRed;
        icon = Icons.engineering_rounded;
        description =
            'المهمة قيد التنفيذ الميداني حالياً. قم بإنجاز العمل والتقاط صورة الإثبات.';
        break;
      case 'completed':
        color = AppColors.yemenEmerald;
        icon = Icons.verified_rounded;
        description =
            'تم إتمام هذه المهمة بنجاح وحل البلاغ ورفع أدلة الإنجاز.';
        break;
      default:
        color = AppColors.textSecondary;
        icon = Icons.info_outline_rounded;
        description = 'حالة المهمة: ${assignment.statusArabic}';
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      'الحالة: ${assignment.statusArabic}',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: color,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  description,
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.textPrimary,
                    height: 1.4,
                  ),
                ),
              ],
            ),
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
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'بيانات البلاغ الميداني',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: AppColors.deepBlack,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.05),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  complaint.complaintNumber,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.deepBlack,
                  ),
                ),
              ),
            ],
          ),

          const Divider(height: 20, color: AppColors.borderSubtle),

          // Title
          Text(
            complaint.title,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),

          const SizedBox(height: 8),

          // Category & Department
          if (complaint.category != null) ...[
            Row(
              children: [
                const Icon(Icons.category_outlined,
                    size: 15, color: AppColors.textMuted),
                const SizedBox(width: 6),
                Text(
                  complaint.category!.name,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: AppColors.textSecondary,
                  ),
                ),
                if (complaint.departmentName != null) ...[
                  const Text(' • ',
                      style: TextStyle(color: AppColors.textMuted)),
                  Text(
                    complaint.departmentName!,
                    style: const TextStyle(
                      fontSize: 13,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ],
            ),
            const SizedBox(height: 12),
          ],

          // Description
          const Text(
            'وصف البلاغ:',
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
              complaint.description,
              style: const TextStyle(
                fontSize: 13,
                color: AppColors.textPrimary,
                height: 1.5,
              ),
            ),
          ),

          const SizedBox(height: 14),

          // Coordinates Box
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.blue.withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: Colors.blue.withValues(alpha: 0.2)),
            ),
            child: Row(
              children: [
                const Icon(Icons.location_on_rounded,
                    color: Color(0xFF2563EB), size: 20),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'إحداثيات موقع البلاغ (نطاق التحقق: 500م)',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF2563EB),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'خط العرض: ${complaint.latitude.toStringAsFixed(6)} ، خط الطول: ${complaint.longitude.toStringAsFixed(6)}',
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: AppColors.textPrimary,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
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
          const SizedBox(height: 8),
          Text(
            'رقم البلاغ في النظام: #${assignment.complaintId}',
            style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
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
                'الصور والأدلة المرفقة مع البلاغ',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: AppColors.deepBlack,
                ),
              ),
              Text(
                '${complaint.attachments.length} مرفقات',
                style: const TextStyle(
                  fontSize: 12,
                  color: AppColors.textMuted,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 100,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: complaint.attachments.length,
              separatorBuilder: (context, index) => const SizedBox(width: 10),
              itemBuilder: (context, index) {
                final attachment = complaint.attachments[index];
                final url = MediaUrlResolver.resolve(attachment.filePath);

                return ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    width: 100,
                    height: 100,
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
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAssignmentMetaCard(FieldWorkAssignmentEntity assignment) {
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
            'بيانات المهمة الميدانية',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: AppColors.deepBlack,
            ),
          ),
          const Divider(height: 20, color: AppColors.borderSubtle),
          _buildMetaRow(
            label: 'الموظف الميداني',
            value: assignment.workerName ?? 'أنت (المسند إليه)',
            icon: Icons.person_outline_rounded,
          ),
          const SizedBox(height: 10),
          _buildMetaRow(
            label: 'جهة الإسناد',
            value: assignment.assignedByName ?? 'الإدارة المختصة',
            icon: Icons.account_balance_outlined,
          ),
          const SizedBox(height: 10),
          _buildMetaRow(
            label: 'تاريخ الإسناد',
            value: _formatDate(assignment.createdAt),
            icon: Icons.calendar_today_outlined,
          ),
          if (assignment.startedAt != null) ...[
            const SizedBox(height: 10),
            _buildMetaRow(
              label: 'وقت بدء التنفيذ',
              value: _formatDate(assignment.startedAt),
              icon: Icons.timer_outlined,
            ),
          ],
          if (assignment.completedAt != null) ...[
            const SizedBox(height: 10),
            _buildMetaRow(
              label: 'وقت الإنجاز',
              value: _formatDate(assignment.completedAt),
              icon: Icons.check_circle_outline_rounded,
            ),
          ],
          if (assignment.notes != null && assignment.notes!.isNotEmpty) ...[
            const SizedBox(height: 12),
            const Text(
              'ملاحظات / تقرير الإنجاز:',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: AppColors.textMuted,
              ),
            ),
            const SizedBox(height: 4),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.backgroundSubtle,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                assignment.notes!,
                style: const TextStyle(fontSize: 12, height: 1.4),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildMetaRow({
    required String label,
    required String value,
    required IconData icon,
  }) {
    return Row(
      children: [
        Icon(icon, size: 16, color: AppColors.textMuted),
        const SizedBox(width: 8),
        Text(
          label,
          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
        ),
        const Spacer(),
        Text(
          value,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: AppColors.textPrimary,
          ),
        ),
      ],
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
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: AppColors.yemenEmerald.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(10),
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
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 8,
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
          label: Text(state.isAccepting ? 'جارٍ قبول المهمة...' : 'قبول المهمة الميدانية'),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.deepBlack,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 14),
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
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 8,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: ElevatedButton.icon(
          onPressed: () {
            context.push('/tasks/${assignment.id}/execute');
          },
          icon: Icon(
            isProgress ? Icons.engineering_rounded : Icons.radar_rounded,
            size: 20,
          ),
          label: Text(
            isProgress ? 'متابعة تنفيذ المهمة' : 'الانتقال للتنفيذ والتحقق الجغرافي',
          ),
          style: ElevatedButton.styleFrom(
            backgroundColor: isProgress ? AppColors.yemenRed : AppColors.deepBlack,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 14),
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
      return DateFormat('yyyy-MM-dd  HH:mm', 'ar').format(date);
    } catch (_) {
      return DateFormat('yyyy-MM-dd  HH:mm').format(date);
    }
  }
}
