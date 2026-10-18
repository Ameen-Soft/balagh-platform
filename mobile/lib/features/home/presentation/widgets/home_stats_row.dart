import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';

class HomeStatsRow extends StatelessWidget {
  final int newCount;
  final int underReviewCount;
  final int inProgressCount;
  final int resolvedCount;
  final void Function(String? statusFilter)? onStatTap;

  const HomeStatsRow({
    super.key,
    required this.newCount,
    required this.underReviewCount,
    required this.inProgressCount,
    required this.resolvedCount,
    this.onStatTap,
  });

  @override
  Widget build(BuildContext context) {
    // Exactly matches the mockup from right to left:
    // [جديدة] -> [قيد المراجعة] -> [قيد التنفيذ] -> [تم إنجازها]
    return Row(
      children: [
        // 1. جديدة (New)
        Expanded(
          child: _buildStatCard(
            label: 'جديدة',
            count: newCount,
            iconWidget: Container(
              width: 22,
              height: 22,
              decoration: const BoxDecoration(
                color: AppColors.yemenRed,
                shape: BoxShape.circle,
              ),
              alignment: Alignment.center,
              child: const Text(
                '!',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w900,
                  fontSize: 14,
                  height: 1.1,
                ),
              ),
            ),
            onTap: () => onStatTap?.call('new'),
          ),
        ),
        const SizedBox(width: 8),

        // 2. قيد المراجعة (Under Review)
        Expanded(
          child: _buildStatCard(
            label: 'قيد المراجعة',
            count: underReviewCount,
            iconWidget: const Icon(
              Icons.assignment_outlined,
              color: AppColors.yemenRed,
              size: 22,
            ),
            onTap: () => onStatTap?.call('under_review'),
          ),
        ),
        const SizedBox(width: 8),

        // 3. قيد التنفيذ (In Progress)
        Expanded(
          child: _buildStatCard(
            label: 'قيد التنفيذ',
            count: inProgressCount,
            iconWidget: const Icon(
              Icons.access_time_rounded,
              color: AppColors.yemenBlack,
              size: 22,
            ),
            onTap: () => onStatTap?.call('in_progress'),
          ),
        ),
        const SizedBox(width: 8),

        // 4. تم إنجازها (Resolved / Completed)
        Expanded(
          child: _buildStatCard(
            label: 'تم إنجازها',
            count: resolvedCount,
            iconWidget: const Icon(
              Icons.check_circle_rounded,
              color: AppColors.yemenEmerald,
              size: 22,
            ),
            onTap: () => onStatTap?.call('resolved'),
          ),
        ),
      ],
    );
  }

  Widget _buildStatCard({
    required String label,
    required int count,
    required Widget iconWidget,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 4),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: const Color(0xFFF1F3F5),
            width: 1.2,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.025),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Icon
            iconWidget,
            const SizedBox(height: 6),

            // Number
            Text(
              count.toString(),
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w900,
                color: AppColors.yemenBlack,
                height: 1.1,
              ),
            ),
            const SizedBox(height: 3),

            // Label
            Text(
              label,
              style: const TextStyle(
                fontSize: 11,
                color: Color(0xFF6B7280),
                fontWeight: FontWeight.w600,
              ),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}
