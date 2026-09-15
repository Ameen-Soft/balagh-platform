import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';

/// StatusBadge representing strictly the 8 authorized complaint statuses in Balagh Platform:
/// - `new`
/// - `under_review`
/// - `assigned`
/// - `in_progress`
/// - `resolved`
/// - `closed`
/// - `rejected`
/// - `reopened`
class StatusBadge extends StatelessWidget {
  final String status;
  final bool isLarge;
  final bool showIcon;

  const StatusBadge({
    super.key,
    required this.status,
    this.isLarge = false,
    this.showIcon = true,
  });

  @override
  Widget build(BuildContext context) {
    final config = _getStatusConfig(status);

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: isLarge ? 14 : 9,
        vertical: isLarge ? 6 : 3.5,
      ),
      decoration: BoxDecoration(
        color: config.color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: config.color.withValues(alpha: 0.28),
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          if (showIcon) ...[
            Icon(
              config.icon,
              size: isLarge ? 16 : 12,
              color: config.color,
            ),
            SizedBox(width: isLarge ? 6 : 4),
          ],
          Text(
            config.label,
            style: TextStyle(
              fontSize: isLarge ? 13 : 11,
              fontWeight: FontWeight.w700,
              color: config.color,
              height: 1.1,
            ),
          ),
        ],
      ),
    );
  }

  static _StatusConfig _getStatusConfig(String status) {
    switch (status) {
      case 'new':
        return const _StatusConfig(
          label: 'جديد وارد',
          color: AppColors.yemenGold,
          icon: Icons.fiber_new_rounded,
        );
      case 'under_review':
        return const _StatusConfig(
          label: 'قيد المراجعة',
          color: Color(0xFF4F46E5),
          icon: Icons.rate_review_outlined,
        );
      case 'assigned':
        return const _StatusConfig(
          label: 'مسند للميدان',
          color: Color(0xFF2563EB),
          icon: Icons.assignment_ind_outlined,
        );
      case 'in_progress':
        return const _StatusConfig(
          label: 'قيد التنفيذ',
          color: Color(0xFFEA580C),
          icon: Icons.engineering_outlined,
        );
      case 'resolved':
        return const _StatusConfig(
          label: 'تم الإنجاز',
          color: AppColors.yemenEmerald,
          icon: Icons.check_circle_outline_rounded,
        );
      case 'closed':
        return const _StatusConfig(
          label: 'مغلق',
          color: Color(0xFF64748B),
          icon: Icons.archive_outlined,
        );
      case 'rejected':
        return const _StatusConfig(
          label: 'مرفوض',
          color: AppColors.yemenRed,
          icon: Icons.cancel_outlined,
        );
      case 'reopened':
        return const _StatusConfig(
          label: 'أُعيد فتحها',
          color: Color(0xFF9333EA),
          icon: Icons.replay_rounded,
        );
      default:
        return _StatusConfig(
          label: status,
          color: AppColors.textSecondary,
          icon: Icons.info_outline_rounded,
        );
    }
  }
}

class _StatusConfig {
  final String label;
  final Color color;
  final IconData icon;

  const _StatusConfig({
    required this.label,
    required this.color,
    required this.icon,
  });
}
