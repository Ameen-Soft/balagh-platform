import 'package:flutter/material.dart';

import '../../../../core/constants/app_colors.dart';
import '../../../../core/utils/app_date_formatter.dart';
import '../../domain/entities/timeline_entity.dart';

class TimelineWidget extends StatelessWidget {
  final List<TimelineEntity> events;

  const TimelineWidget({super.key, required this.events});

  @override
  Widget build(BuildContext context) {
    if (events.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.borderSubtle, width: 0.8),
        ),
        child: const Center(
          child: Text(
            'لا توجد تحديثات مسجلة في السجل الزمني حتى الآن',
            style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary),
          ),
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.fromLTRB(18, 20, 18, 18),
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
      child: ListView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        padding: EdgeInsets.zero,
        itemCount: events.length,
        itemBuilder: (context, index) {
          final event = events[index];
          final isLast = index == events.length - 1;
          return _buildTimelineItem(event, isLast);
        },
      ),
    );
  }

  Widget _buildTimelineItem(TimelineEntity event, bool isLast) {
    final config = _getEventConfig(event.eventType);
    final formattedDate = event.createdAt != null
        ? AppDateFormatter.formatDateTime(event.createdAt)
        : null;

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 1. Minimalist Node & Thin Vertical Connector
          SizedBox(
            width: 26,
            child: Column(
              children: [
                Container(
                  width: 24,
                  height: 24,
                  decoration: BoxDecoration(
                    color: config.color.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: config.color.withValues(alpha: 0.45),
                      width: 1.0,
                    ),
                  ),
                  child: Icon(config.icon, size: 13, color: config.color),
                ),
                if (!isLast)
                  Expanded(
                    child: Container(
                      width: 1.2,
                      margin: const EdgeInsets.symmetric(vertical: 4),
                      color: AppColors.borderSubtle,
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 12),

          // 2. Event Details
          Expanded(
            child: Padding(
              padding: EdgeInsets.only(bottom: isLast ? 0 : 20.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Expanded(
                        child: Text(
                          config.title,
                          style: const TextStyle(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w800,
                            color: AppColors.yemenBlack,
                          ),
                        ),
                      ),
                      if (formattedDate != null)
                        Text(
                          formattedDate,
                          style: const TextStyle(
                            fontSize: 11,
                            color: AppColors.textMuted,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    event.description,
                    style: const TextStyle(
                      fontSize: 12.5,
                      color: AppColors.textSecondary,
                      height: 1.5,
                    ),
                  ),
                  if (event.performerName != null &&
                      event.performerName!.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      'بواسطة: ${event.performerName}',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textMuted,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  static _TimelineEventConfig _getEventConfig(String eventType) {
    switch (eventType) {
      case 'created':
        return const _TimelineEventConfig(
          title: 'تم تسجيل البلاغ',
          color: AppColors.yemenGold,
          icon: Icons.add_rounded,
        );
      case 'verified':
        return const _TimelineEventConfig(
          title: 'تم التحقق من البلاغ',
          color: Color(0xFF2563EB),
          icon: Icons.verified_outlined,
        );
      case 'assigned':
        return const _TimelineEventConfig(
          title: 'إسناد البلاغ للميدان',
          color: Color(0xFF2563EB),
          icon: Icons.assignment_ind_outlined,
        );
      case 'transferred':
        return const _TimelineEventConfig(
          title: 'تحويل البلاغ بين الجهات',
          color: Color(0xFF0284C7),
          icon: Icons.swap_horiz_rounded,
        );
      case 'status_changed':
        return const _TimelineEventConfig(
          title: 'تحديث حالة البلاغ',
          color: Color(0xFFEA580C),
          icon: Icons.sync_rounded,
        );
      case 'evidence_uploaded':
        return const _TimelineEventConfig(
          title: 'رفع أدلة ميدانية',
          color: Color(0xFF0D9488),
          icon: Icons.photo_camera_outlined,
        );
      case 'resolved':
        return const _TimelineEventConfig(
          title: 'تم إنجاز المعالجة',
          color: AppColors.yemenEmerald,
          icon: Icons.check_rounded,
        );
      case 'closed':
        return const _TimelineEventConfig(
          title: 'تم إغلاق البلاغ',
          color: Color(0xFF64748B),
          icon: Icons.lock_outline_rounded,
        );
      case 'reopened':
        return const _TimelineEventConfig(
          title: 'أُعيد فتح البلاغ',
          color: Color(0xFF9333EA),
          icon: Icons.replay_rounded,
        );
      case 'rejected':
        return const _TimelineEventConfig(
          title: 'تم رفض البلاغ',
          color: AppColors.yemenRed,
          icon: Icons.close_rounded,
        );
      default:
        return _TimelineEventConfig(
          title: eventType,
          color: AppColors.textSecondary,
          icon: Icons.info_outline_rounded,
        );
    }
  }
}

class _TimelineEventConfig {
  final String title;
  final Color color;
  final IconData icon;

  const _TimelineEventConfig({
    required this.title,
    required this.color,
    required this.icon,
  });
}
