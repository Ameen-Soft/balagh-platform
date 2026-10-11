import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/utils/app_date_formatter.dart';
import '../../domain/entities/notification_entity.dart';

class NotificationCard extends StatelessWidget {
  final NotificationEntity notification;
  final VoidCallback? onMarkAsRead;

  const NotificationCard({
    super.key,
    required this.notification,
    this.onMarkAsRead,
  });

  void _handleTap(BuildContext context) {
    if (!notification.isRead && onMarkAsRead != null) {
      onMarkAsRead!();
    }

    if (notification.complaintId != null) {
      context.push('/complaints/${notification.complaintId}');
    } else if (notification.projectId != null) {
      context.push('/projects/${notification.projectId}');
    } else if (notification.assignmentId != null) {
      context.push('/tasks/${notification.assignmentId}');
    }
  }

  String _formatRelativeTime(DateTime? date) {
    if (date == null) return '';
    final now = DateTime.now();
    final difference = now.difference(date);

    if (difference.inSeconds < 60) {
      return 'الآن';
    } else if (difference.inMinutes < 60) {
      return 'منذ ${difference.inMinutes} دقيقة';
    } else if (difference.inHours < 24) {
      return 'منذ ${difference.inHours} ساعة';
    } else if (difference.inDays < 7) {
      return 'منذ ${difference.inDays} يوم';
    } else {
      return AppDateFormatter.formatDate(date);
    }
  }

  ({IconData icon, Color color, Color bg}) _getTypeStyle(String type) {
    switch (type) {
      case 'complaint_created':
        return (
          icon: Icons.post_add_rounded,
          color: AppColors.yemenGold,
          bg: AppColors.yemenGoldLight,
        );
      case 'complaint_status_changed':
        return (
          icon: Icons.sync_alt_rounded,
          color: AppColors.yemenRed,
          bg: AppColors.yemenRedLight,
        );
      case 'complaint_transferred':
        return (
          icon: Icons.alt_route_rounded,
          color: const Color(0xFF2563EB),
          bg: const Color(0xFFEFF6FF),
        );
      case 'complaint_resolved':
        return (
          icon: Icons.verified_rounded,
          color: AppColors.yemenEmerald,
          bg: AppColors.yemenEmeraldLight,
        );
      case 'task_assigned':
        return (
          icon: Icons.assignment_ind_rounded,
          color: AppColors.yemenGold,
          bg: AppColors.yemenGoldLight,
        );
      case 'project_contribution_success':
        return (
          icon: Icons.volunteer_activism_rounded,
          color: AppColors.yemenEmerald,
          bg: AppColors.yemenEmeraldLight,
        );
      default:
        return (
          icon: Icons.notifications_active_rounded,
          color: AppColors.yemenRed,
          bg: AppColors.yemenRedLight,
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    final style = _getTypeStyle(notification.type);
    final isUnread = !notification.isRead;
    final hasAction = notification.complaintId != null ||
        notification.projectId != null ||
        notification.assignmentId != null;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: isUnread ? Colors.white : const Color(0xFFFAFAFA),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isUnread
              ? AppColors.yemenRed.withValues(alpha: 0.25)
              : AppColors.borderSubtle,
          width: isUnread ? 1.4 : 1.0,
        ),
        boxShadow: isUnread
            ? [
                BoxShadow(
                  color: AppColors.yemenRed.withValues(alpha: 0.06),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                ),
              ]
            : [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.02),
                  blurRadius: 4,
                  offset: const Offset(0, 1),
                ),
              ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => _handleTap(context),
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top Row: Type Icon, Title, and Unread Dot
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 42,
                      height: 42,
                      decoration: BoxDecoration(
                        color: style.bg,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        style.icon,
                        color: style.color,
                        size: 22,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  notification.title,
                                  style: TextStyle(
                                    fontSize: 15,
                                    fontWeight: isUnread
                                        ? FontWeight.w800
                                        : FontWeight.w600,
                                    color: isUnread
                                        ? AppColors.deepBlack
                                        : AppColors.textPrimary,
                                    height: 1.3,
                                  ),
                                ),
                              ),
                              if (isUnread) ...[
                                const SizedBox(width: 8),
                                Container(
                                  width: 8,
                                  height: 8,
                                  decoration: const BoxDecoration(
                                    color: AppColors.yemenRed,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _formatRelativeTime(notification.createdAt),
                            style: const TextStyle(
                              fontSize: 12,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 10),

                // Body text
                Text(
                  notification.body,
                  style: TextStyle(
                    fontSize: 13.5,
                    height: 1.5,
                    color: isUnread
                        ? AppColors.textPrimary.withValues(alpha: 0.9)
                        : AppColors.textSecondary,
                  ),
                ),

                // Bottom Action Footer if target entity is linked
                if (hasAction) ...[
                  const SizedBox(height: 12),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 5,
                        ),
                        decoration: BoxDecoration(
                          color: style.bg,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              notification.complaintId != null
                                  ? 'عرض تفاصيل البلاغ'
                                  : notification.projectId != null
                                      ? 'عرض المشروع التنموي'
                                      : 'عرض تفاصيل المهمة',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: style.color,
                              ),
                            ),
                            const SizedBox(width: 4),
                            Icon(
                              Icons.arrow_forward_ios_rounded,
                              size: 11,
                              color: style.color,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
