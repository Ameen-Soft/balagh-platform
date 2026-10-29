import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/utils/app_date_formatter.dart';
import '../../application/complaints_providers.dart';
import '../../domain/entities/complaint_entity.dart';
import '../widgets/attachment_viewer.dart';
import '../widgets/status_badge.dart';
import '../widgets/timeline_widget.dart';

class ComplaintDetailsPage extends ConsumerWidget {
  final int complaintId;
  final ComplaintEntity? initialComplaint;

  const ComplaintDetailsPage({
    super.key,
    required this.complaintId,
    this.initialComplaint,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final complaintAsync = ref.watch(complaintDetailsProvider(complaintId));
    final ticketNumber =
        initialComplaint?.complaintNumber ??
        'CMP-${complaintId.toString().padLeft(6, '0')}';

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0.5,
        centerTitle: true,
        shape: Border(
          bottom: BorderSide(
            color: AppColors.borderSubtle.withValues(alpha: 0.8),
            width: 0.8,
          ),
        ),
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back_ios_new_rounded,
            color: AppColors.yemenBlack,
            size: 18,
          ),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/my-complaints');
            }
          },
        ),
        title: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'تفاصيل البلاغ',
              style: TextStyle(
                color: AppColors.yemenBlack,
                fontWeight: FontWeight.w800,
                fontSize: 15.5,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              ticketNumber,
              style: const TextStyle(
                color: AppColors.textSecondary,
                fontWeight: FontWeight.w600,
                fontSize: 11.5,
                fontFamily: 'monospace',
                letterSpacing: 0.6,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'تحديث',
            icon: const Icon(
              Icons.refresh_rounded,
              color: AppColors.yemenBlack,
              size: 20,
            ),
            onPressed: () =>
                ref.invalidate(complaintDetailsProvider(complaintId)),
          ),
          const SizedBox(width: 6),
        ],
      ),
      body: SafeArea(
        child: complaintAsync.when(
          data: (complaint) => _buildDetailsContent(context, ref, complaint),
          loading: () {
            if (initialComplaint != null) {
              return _buildDetailsContent(
                context,
                ref,
                initialComplaint!,
                isLoadingFresh: true,
              );
            }
            return const Center(
              child: SizedBox(
                width: 26,
                height: 26,
                child: CircularProgressIndicator(
                  color: AppColors.yemenRed,
                  strokeWidth: 2.2,
                ),
              ),
            );
          },
          error: (error, _) {
            if (initialComplaint != null) {
              return _buildDetailsContent(context, ref, initialComplaint!);
            }
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.info_outline_rounded,
                      size: 40,
                      color: AppColors.yemenRed,
                    ),
                    const SizedBox(height: 14),
                    const Text(
                      'تعذر تحميل تفاصيل البلاغ',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: AppColors.yemenBlack,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      error.toString(),
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary,
                      ),
                    ),
                    const SizedBox(height: 18),
                    OutlinedButton.icon(
                      onPressed: () =>
                          ref.invalidate(complaintDetailsProvider(complaintId)),
                      icon: const Icon(Icons.refresh_rounded, size: 16),
                      label: const Text('إعادة المحاولة'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.yemenBlack,
                        side: const BorderSide(
                          color: AppColors.borderSubtle,
                          width: 0.8,
                        ),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 20,
                          vertical: 12,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildDetailsContent(
    BuildContext context,
    WidgetRef ref,
    ComplaintEntity complaint, {
    bool isLoadingFresh = false,
  }) {
    final priorityColor = _getPriorityColor(complaint.priority);
    final formattedDate = complaint.createdAt != null
        ? AppDateFormatter.formatDateTime(complaint.createdAt)
        : 'غير محدد';

    return RefreshIndicator(
      color: AppColors.yemenRed,
      onRefresh: () async {
        ref.invalidate(complaintDetailsProvider(complaintId));
        await ref.read(complaintDetailsProvider(complaintId).future);
      },
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (isLoadingFresh) ...[
              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: const LinearProgressIndicator(
                  color: AppColors.yemenRed,
                  backgroundColor: AppColors.yemenRedLight,
                  minHeight: 2,
                ),
              ),
              const SizedBox(height: 14),
            ],

            // 1. Unified Executive Dossier Card (Status + Content + Metadata + Location)
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.borderSubtle, width: 0.8),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.015),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Top Bar: Status, Priority & Date
                  Padding(
                    padding: const EdgeInsets.fromLTRB(18, 16, 18, 14),
                    child: Row(
                      children: [
                        StatusBadge(status: complaint.status, isLarge: false),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 9,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: priorityColor.withValues(alpha: 0.08),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            complaint.priorityArabic,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: priorityColor,
                            ),
                          ),
                        ),
                        const Spacer(),
                        Icon(
                          Icons.schedule_rounded,
                          size: 13,
                          color: AppColors.textMuted.withValues(alpha: 0.9),
                        ),
                        const SizedBox(width: 4),
                        Text(
                          formattedDate,
                          style: const TextStyle(
                            fontSize: 11.5,
                            color: AppColors.textSecondary,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),

                  const Divider(
                    height: 1,
                    thickness: 0.8,
                    color: AppColors.borderSubtle,
                  ),

                  // Core Content: Title & Description
                  Padding(
                    padding: const EdgeInsets.all(18),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          complaint.title,
                          style: const TextStyle(
                            fontSize: 16.5,
                            fontWeight: FontWeight.w800,
                            color: AppColors.yemenBlack,
                            height: 1.35,
                          ),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          complaint.description.isNotEmpty
                              ? complaint.description
                              : 'لا يوجد وصف إضافي مرفق.',
                          style: const TextStyle(
                            fontSize: 13.5,
                            color: AppColors.textSecondary,
                            height: 1.65,
                          ),
                        ),
                      ],
                    ),
                  ),

                  const Divider(
                    height: 1,
                    thickness: 0.8,
                    color: AppColors.borderSubtle,
                  ),

                  // Structured Metadata Table (Ministry, Department, Category)
                  Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 18,
                      vertical: 14,
                    ),
                    child: Column(
                      children: [
                        _buildCleanMetaRow(
                          label: 'الجهة المختصة',
                          value: complaint.ministryName ?? 'غير محدد',
                        ),
                        const SizedBox(height: 10),
                        _buildCleanMetaRow(
                          label: 'الإدارة المعنية',
                          value: complaint.departmentName ?? 'الإدارة المختصة',
                        ),
                        const SizedBox(height: 10),
                        _buildCleanMetaRow(
                          label: 'التصنيف',
                          value: complaint.category?.name ?? 'عام',
                        ),
                      ],
                    ),
                  ),

                  const Divider(
                    height: 1,
                    thickness: 0.8,
                    color: AppColors.borderSubtle,
                  ),

                  // Sleek Integrated Location Bar
                  InkWell(
                    borderRadius: const BorderRadius.vertical(
                      bottom: Radius.circular(14),
                    ),
                    onTap: () => _copyCoordinates(context, complaint),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 18,
                        vertical: 12,
                      ),
                      decoration: const BoxDecoration(
                        color: Color(0xFFFAFAFA),
                        borderRadius: BorderRadius.vertical(
                          bottom: Radius.circular(14),
                        ),
                      ),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.location_on_outlined,
                            size: 16,
                            color: AppColors.yemenRed,
                          ),
                          const SizedBox(width: 8),
                          const Text(
                            'الإحداثيات',
                            style: TextStyle(
                              fontSize: 12,
                              color: AppColors.textSecondary,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const Spacer(),
                          Text(
                            '${complaint.latitude.toStringAsFixed(5)},${complaint.longitude.toStringAsFixed(5)}',
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: AppColors.yemenBlack,
                              fontFamily: 'monospace',
                            ),
                          ),
                          const SizedBox(width: 8),
                          const Icon(
                            Icons.copy_rounded,
                            size: 14,
                            color: AppColors.textSecondary,
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 22),

            // 2. Visual Attachments Section
            _buildSectionHeader(
              title: 'المرفقات البصرية',
              trailing: complaint.attachments.isNotEmpty
                  ? '${complaint.attachments.length} مرفق'
                  : null,
            ),
            const SizedBox(height: 10),
            AttachmentViewer(attachments: complaint.attachments),

            const SizedBox(height: 22),

            // 3. Timeline Audit Log Section
            _buildSectionHeader(
              title: 'سجل المعالجة',
              trailing: complaint.timeline.isNotEmpty
                  ? '${complaint.timeline.length} تحديثات'
                  : null,
            ),
            const SizedBox(height: 10),
            TimelineWidget(events: complaint.timeline),

            const SizedBox(height: 28),
          ],
        ),
      ),
    );
  }

  void _copyCoordinates(BuildContext context, ComplaintEntity complaint) {
    Clipboard.setData(
      ClipboardData(text: '${complaint.latitude},${complaint.longitude}'),
    );
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Text(
          'تم نسخ إحداثيات الموقع',
          style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600),
        ),
        backgroundColor: AppColors.yemenBlack,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  Widget _buildSectionHeader({required String title, String? trailing}) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 2),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 14.5,
              fontWeight: FontWeight.w800,
              color: AppColors.yemenBlack,
            ),
          ),
          if (trailing != null)
            Text(
              trailing,
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildCleanMetaRow({required String label, required String value}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 95,
          child: Text(
            label,
            style: const TextStyle(
              fontSize: 12.5,
              color: AppColors.textSecondary,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        Expanded(
          child: Text(
            value,
            textAlign: TextAlign.left,
            style: const TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w700,
              color: AppColors.yemenBlack,
            ),
          ),
        ),
      ],
    );
  }

  Color _getPriorityColor(String priority) {
    switch (priority) {
      case 'urgent':
      case 'high':
        return AppColors.yemenRed;
      case 'low':
        return AppColors.textSecondary;
      case 'medium':
      default:
        return AppColors.yemenGold;
    }
  }
}
