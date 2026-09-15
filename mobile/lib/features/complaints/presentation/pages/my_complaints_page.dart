import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../application/complaints_providers.dart';
import '../../domain/entities/complaint_entity.dart';

class MyComplaintsPage extends ConsumerWidget {
  const MyComplaintsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final complaintsAsync = ref.watch(myComplaintsProvider);

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
              context.go('/home');
            }
          },
        ),
        title: const Text(
          'سجل بلاغاتي',
          style: TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'تحديث',
            icon: const Icon(Icons.refresh_rounded, color: AppColors.yemenBlack),
            onPressed: () => ref.refresh(myComplaintsProvider),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: RefreshIndicator(
          color: AppColors.yemenRed,
          onRefresh: () async => ref.refresh(myComplaintsProvider.future),
          child: complaintsAsync.when(
            data: (complaints) {
              if (complaints.isEmpty) {
                return _buildEmptyState(context);
              }
              return ListView.separated(
                padding: const EdgeInsets.all(16),
                itemCount: complaints.length,
                separatorBuilder: (context, index) => const SizedBox(height: 14),
                itemBuilder: (context, index) {
                  return _buildComplaintCard(context, complaints[index]);
                },
              );
            },
            loading: () => const Center(
              child: CircularProgressIndicator(color: AppColors.yemenRed),
            ),
            error: (err, _) => Center(
              child: Padding(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.error_outline_rounded,
                        size: 48, color: AppColors.yemenRed),
                    const SizedBox(height: 16),
                    const Text(
                      'تعذر استرجاع قائمة البلاغات',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: AppColors.yemenBlack,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      err.toString(),
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                          fontSize: 12, color: AppColors.textSecondary),
                    ),
                    const SizedBox(height: 20),
                    ElevatedButton.icon(
                      onPressed: () => ref.refresh(myComplaintsProvider),
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
            ),
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.yemenRed,
        foregroundColor: Colors.white,
        onPressed: () => context.push('/create-complaint'),
        icon: const Icon(Icons.add_rounded),
        label: const Text(
          'تقديم بلاغ',
          style: TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(32),
      children: [
        const SizedBox(height: 60),
        CircleAvatar(
          radius: 44,
          backgroundColor: AppColors.yemenRedLight,
          child: const Icon(
            Icons.inbox_rounded,
            size: 44,
            color: AppColors.yemenRed,
          ),
        ),
        const SizedBox(height: 20),
        const Text(
          'لا توجد بلاغات مسجلة حتى الآن',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            color: AppColors.yemenBlack,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'يمكنك تقديم بلاغ جديد والمساهمة في رصد ومعالجة المشكلات التنموية في مجتمعك.',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 13,
            color: AppColors.textSecondary,
            height: 1.5,
          ),
        ),
        const SizedBox(height: 28),
        Center(
          child: ElevatedButton.icon(
            onPressed: () => context.push('/create-complaint'),
            icon: const Icon(Icons.add_circle_outline_rounded, size: 20),
            label: const Text('تقديم بلاغ جديد الآن'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.yemenRed,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildComplaintCard(BuildContext context, ComplaintEntity item) {
    final statusColor = _getStatusColor(item.status);
    final priorityColor = _getPriorityColor(item.priority);

    return Container(
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
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header: Code & Status Badge
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                item.complaintNumber,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w800,
                  color: AppColors.yemenBlack,
                  fontFamily: 'monospace',
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  item.statusArabic,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Title
          Text(
            item.title,
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w700,
              color: AppColors.yemenBlack,
            ),
          ),
          const SizedBox(height: 6),

          // Category & Ministry
          Row(
            children: [
              const Icon(Icons.business_rounded,
                  size: 14, color: AppColors.textSecondary),
              const SizedBox(width: 4),
              Expanded(
                child: Text(
                  '${item.ministryName ?? "الجهة المعنية"} • ${item.category?.name ?? "عام"}',
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.textSecondary,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const Divider(height: 20),

          // Footer: Date & Priority
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              if (item.createdAt != null)
                Text(
                  '${item.createdAt!.year}-${item.createdAt!.month.toString().padLeft(2, '0')}-${item.createdAt!.day.toString().padLeft(2, '0')}',
                  style: const TextStyle(
                    fontSize: 11,
                    color: AppColors.textMuted,
                  ),
                )
              else
                const SizedBox.shrink(),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: priorityColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  item.priorityArabic,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: priorityColor,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'new':
        return AppColors.yemenGold;
      case 'under_review':
      case 'assigned':
      case 'in_progress':
        return const Color(0xFF2563EB); // Blue
      case 'resolved':
      case 'closed':
        return AppColors.yemenEmerald;
      case 'rejected':
        return AppColors.yemenRed;
      case 'reopened':
        return const Color(0xFF9333EA); // Purple
      default:
        return AppColors.textSecondary;
    }
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
