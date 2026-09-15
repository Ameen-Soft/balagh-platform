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
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/my-complaints');
            }
          },
        ),
        title: Text(
          initialComplaint?.complaintNumber ??
              'CMP-${complaintId.toString().padLeft(6, '0')}',
          style: const TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 16,
            letterSpacing: 0.5,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'تحديث التفاصيل',
            icon: const Icon(Icons.refresh_rounded, color: AppColors.yemenBlack),
            onPressed: () =>
                ref.invalidate(complaintDetailsProvider(complaintId)),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: complaintAsync.when(
          data: (complaint) => _buildDetailsContent(context, ref, complaint),
          loading: () {
            if (initialComplaint != null) {
              // Optimistic preview while fresh details (timeline & attachments) are loading
              return _buildDetailsContent(context, ref, initialComplaint!,
                  isLoadingFresh: true);
            }
            return const Center(
              child: CircularProgressIndicator(color: AppColors.yemenRed),
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
                    const Icon(Icons.error_outline_rounded,
                        size: 48, color: AppColors.yemenRed),
                    const SizedBox(height: 16),
                    const Text(
                      'تعذر تحميل تفاصيل البلاغ',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: AppColors.yemenBlack,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      error.toString(),
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                          fontSize: 12, color: AppColors.textSecondary),
                    ),
                    const SizedBox(height: 20),
                    ElevatedButton.icon(
                      onPressed: () => ref.invalidate(
                          complaintDetailsProvider(complaintId)),
                      icon: const Icon(Icons.refresh_rounded, size: 18),
                      label: const Text('إعادة المحاولة'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.yemenBlack,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
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
      BuildContext context, WidgetRef ref, ComplaintEntity complaint,
      {bool isLoadingFresh = false}) {
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
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (isLoadingFresh) ...[
              const LinearProgressIndicator(
                color: AppColors.yemenRed,
                backgroundColor: AppColors.yemenRedLight,
                minHeight: 3,
              ),
              const SizedBox(height: 12),
            ],

            // 1. Status & Meta Card
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.borderSubtle),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.03),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      StatusBadge(
                        status: complaint.status,
                        isLarge: true,
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: priorityColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          'أولوية: ${complaint.priorityArabic}',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: priorityColor,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    complaint.title,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                      color: AppColors.yemenBlack,
                      height: 1.3,
                    ),
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      const Icon(Icons.access_time_rounded,
                          size: 14, color: AppColors.textMuted),
                      const SizedBox(width: 5),
                      Text(
                        'تاريخ التقديم: $formattedDate',
                        style: const TextStyle(
                          fontSize: 12,
                          color: AppColors.textMuted,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // 2. Complaint Description Card
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.borderSubtle),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.description_outlined,
                          size: 18, color: AppColors.yemenBlack),
                      SizedBox(width: 8),
                      Text(
                        'تفاصيل وموضوع البلاغ',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: AppColors.yemenBlack,
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 20),
                  Text(
                    complaint.description.isNotEmpty
                        ? complaint.description
                        : 'لا يوجد وصف إضافي متوفر.',
                    style: const TextStyle(
                      fontSize: 14,
                      color: AppColors.textPrimary,
                      height: 1.6,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // 3. Organization & Taxonomy Card
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.borderSubtle),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.account_balance_outlined,
                          size: 18, color: AppColors.yemenBlack),
                      SizedBox(width: 8),
                      Text(
                        'جهة الاختصاص والتصنيف',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: AppColors.yemenBlack,
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 20),
                  _buildMetaRow(
                    label: 'الجهة / الوزارة',
                    value: complaint.ministryName ?? 'غير محدد',
                    icon: Icons.business_rounded,
                  ),
                  const SizedBox(height: 10),
                  _buildMetaRow(
                    label: 'الإدارة المعنية',
                    value: complaint.departmentName ?? 'الإدارة المختصة',
                    icon: Icons.corporate_fare_rounded,
                  ),
                  const SizedBox(height: 10),
                  _buildMetaRow(
                    label: 'التصنيف الرئيسي والفرعي',
                    value: complaint.category?.name ?? 'عام',
                    icon: Icons.category_outlined,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // 4. Geolocation Card
            Container(
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
                          Icon(Icons.location_on_outlined,
                              size: 18, color: AppColors.yemenRed),
                          SizedBox(width: 8),
                          Text(
                            'الموقع الجغرافي المسجل',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                              color: AppColors.yemenBlack,
                            ),
                          ),
                        ],
                      ),
                      IconButton(
                        tooltip: 'نسخ الإحداثيات',
                        icon: const Icon(Icons.copy_rounded,
                            size: 18, color: AppColors.textSecondary),
                        onPressed: () {
                          Clipboard.setData(ClipboardData(
                            text:
                                '${complaint.latitude}, ${complaint.longitude}',
                          ));
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('تم نسخ الإحداثيات إلى الحافظة'),
                              duration: Duration(seconds: 2),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                  const Divider(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.backgroundSubtle,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: AppColors.borderSubtle),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'خط العرض (Latitude)',
                                style: TextStyle(
                                    fontSize: 11, color: AppColors.textMuted),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                complaint.latitude.toStringAsFixed(6),
                                style: const TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.yemenBlack,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.backgroundSubtle,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: AppColors.borderSubtle),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'خط الطول (Longitude)',
                                style: TextStyle(
                                    fontSize: 11, color: AppColors.textMuted),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                complaint.longitude.toStringAsFixed(6),
                                style: const TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.yemenBlack,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // 5. Attachments Section
            const Text(
              'الأدلة والمرفقات البصرية',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
                color: AppColors.yemenBlack,
              ),
            ),
            const SizedBox(height: 10),
            AttachmentViewer(attachments: complaint.attachments),
            const SizedBox(height: 24),

            // 6. Timeline Section
            const Text(
              'سجل الخط الزمني ومسار المعالجة',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
                color: AppColors.yemenBlack,
              ),
            ),
            const SizedBox(height: 12),
            TimelineWidget(events: complaint.timeline),
            const SizedBox(height: 32),
          ],
        ),
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
        Icon(icon, size: 16, color: AppColors.textSecondary),
        const SizedBox(width: 8),
        Text(
          '$label: ',
          style: const TextStyle(
            fontSize: 13,
            color: AppColors.textSecondary,
          ),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: AppColors.yemenBlack,
            ),
            overflow: TextOverflow.ellipsis,
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
