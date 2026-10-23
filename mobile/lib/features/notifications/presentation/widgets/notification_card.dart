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

  ({IconData icon, Color color, Color bg, String badgeText}) _getTypeStyle(
    String type,
  ) {
    switch (type) {
      case 'complaint_created':
        return (
          icon: Icons.description_outlined,
          color: AppColors.yemenGold,
          bg: AppColors.yemenGoldLight,
          badgeText: 'متوسط',
        );
      case 'complaint_status_changed':
        return (
          icon: Icons.sync_alt_rounded,
          color: AppColors.yemenRed,
          bg: AppColors.yemenRedLight,
          badgeText: 'عاجل',
        );
      case 'complaint_transferred':
        return (
          icon: Icons.file_copy_outlined,
          color: const Color(0xFF2563EB),
          bg: const Color(0xFFEFF6FF),
          badgeText: 'مسند للميدان',
        );
      case 'complaint_resolved':
        return (
          icon: Icons.verified_user_rounded,
          color: AppColors.yemenEmerald,
          bg: AppColors.yemenEmeraldLight,
          badgeText: 'تم الإنجاز',
        );
      case 'task_assigned':
        return (
          icon: Icons.assignment_ind_outlined,
          color: const Color(0xFF2563EB),
          bg: const Color(0xFFEFF6FF),
          badgeText: 'مهمة ميدانية',
        );
      case 'project_contribution_success':
        return (
          icon: Icons.campaign_outlined,
          color: AppColors.yemenRed,
          bg: AppColors.yemenRedLight,
          badgeText: 'عاجل',
        );
      default:
        return (
          icon: Icons.notifications_active_outlined,
          color: AppColors.yemenRed,
          bg: AppColors.yemenRedLight,
          badgeText: 'تنبيه',
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    final style = _getTypeStyle(notification.type);
    final isUnread = !notification.isRead;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        // خلفية وردية/حمراء خفيفة جداً للإشعار غير المقروء كما في التصميم، وبيضاء للمقروء
        color: isUnread ? const Color(0xFFFFF8F8) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isUnread
              ? AppColors.yemenRed.withValues(alpha: 0.2)
              : AppColors.borderSubtle.withValues(alpha: 0.7),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: isUnread
                ? AppColors.yemenRed.withValues(alpha: 0.04)
                : Colors.black.withValues(alpha: 0.015),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(20),
        child: InkWell(
          borderRadius: BorderRadius.circular(20),
          onTap: () => _handleTap(context),
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. Top Section: Icon (Right), Title & Time (Middle), Status Pill (Left)
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Rounded Square Icon Container
                    Container(
                      width: 46,
                      height: 46,
                      decoration: BoxDecoration(
                        color: style.bg,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Icon(style.icon, color: style.color, size: 23),
                    ),
                    const SizedBox(width: 12),

                    // Title & Relative Time
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [
                              Expanded(
                                child: Text(
                                  notification.title,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontSize: 14.5,
                                    fontWeight: isUnread
                                        ? FontWeight.w800
                                        : FontWeight.w700,
                                    color: AppColors.yemenBlack,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              // Status Badge Pill on the Left
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: style.bg,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    if (isUnread) ...[
                                      Container(
                                        width: 5,
                                        height: 5,
                                        decoration: BoxDecoration(
                                          color: style.color,
                                          shape: BoxShape.circle,
                                        ),
                                      ),
                                      const SizedBox(width: 4),
                                    ],
                                    Text(
                                      style.badgeText,
                                      style: TextStyle(
                                        fontSize: 10.5,
                                        fontWeight: FontWeight.w800,
                                        color: style.color,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _formatRelativeTime(notification.createdAt),
                            style: const TextStyle(
                              fontSize: 11.5,
                              color: AppColors.textSecondary,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 10),

                // 2. Bottom Section: Concise Body Text & Left Chevron Arrow
                Row(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Expanded(
                      child: Padding(
                        // محاذاة النص مع بداية العنوان تحت الأيقونة
                        padding: const EdgeInsets.only(right: 58.0),
                        child: Text(
                          notification.body,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 12.5,
                            height: 1.5,
                            color: isUnread
                                ? AppColors.textPrimary.withValues(alpha: 0.85)
                                : AppColors.textSecondary,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Icon(
                      Icons.chevron_left_rounded,
                      color: AppColors.yemenBlack.withValues(alpha: 0.6),
                      size: 22,
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
