import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/utils/app_date_formatter.dart';
import '../../domain/entities/timeline_entity.dart';

/// TimelineWidget representing complaint lifecycle audit log based strictly on the 10 authorized events:
/// - `created`
/// - `verified`
/// - `assigned`
/// - `transferred`
/// - `status_changed`
/// - `evidence_uploaded`
/// - `resolved`
/// - `closed`
/// - `reopened`
/// - `rejected`
class TimelineWidget extends StatelessWidget {
  final List<TimelineEntity> events;

  const TimelineWidget({
    super.key,
    required this.events,
  });

  @override
  Widget build(BuildContext context) {
    if (events.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.borderSubtle),
        ),
        child: const Center(
          child: Text(
            'لا توجد تحديثات مسجلة في الخط الزمني حتى الآن.',
            style: TextStyle(
              fontSize: 13,
              color: AppColors.textSecondary,
            ),
          ),
        ),
      );
    }

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: events.length,
      itemBuilder: (context, index) {
        final event = events[index];
        final isLast = index == events.length - 1;
        return _buildTimelineItem(event, isLast);
      },
    );
  }

  Widget _buildTimelineItem(TimelineEntity event, bool isLast) {
    final config = _getEventConfig(event.eventType);
    final formattedDate = event.createdAt != null
        ? AppDateFormatter.formatDateTime(event.createdAt)
        : null;

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Step indicator column (Icon + Connecting Line)
          Column(
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: config.color.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: config.color,
                    width: 2,
                  ),
                ),
                child: Icon(
                  config.icon,
                  size: 16,
                  color: config.color,
                ),
              ),
              if (!isLast)
                Expanded(
                  child: Container(
                    width: 2,
                    margin: const EdgeInsets.symmetric(vertical: 4),
                    color: AppColors.borderSubtle,
                  ),
                ),
            ],
          ),
          const SizedBox(width: 14),

          // Content Box
          Expanded(
            child: Padding(
              padding: EdgeInsets.only(bottom: isLast ? 0 : 20.0),
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.borderSubtle),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Event Title & Timestamp
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Text(
                            config.title,
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w700,
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
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 6),

                    // Event Description
                    Text(
                      event.description,
                      style: const TextStyle(
                        fontSize: 13,
                        color: AppColors.textSecondary,
                        height: 1.4,
                      ),
                    ),

                    // Performer Name (if available)
                    if (event.performerName != null &&
                        event.performerName!.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          const Icon(
                            Icons.person_outline_rounded,
                            size: 13,
                            color: AppColors.textMuted,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            'بواسطة: ${event.performerName}',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: AppColors.textMuted,
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
          icon: Icons.add_circle_outline_rounded,
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
          icon: Icons.add_a_photo_outlined,
        );
      case 'resolved':
        return const _TimelineEventConfig(
          title: 'تم إنجاز المعالجة',
          color: AppColors.yemenEmerald,
          icon: Icons.check_circle_outline_rounded,
        );
      case 'closed':
        return const _TimelineEventConfig(
          title: 'تم إغلاق البلاغ',
          color: Color(0xFF64748B),
          icon: Icons.archive_outlined,
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
          icon: Icons.cancel_outlined,
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
