import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../../core/constants/app_colors.dart';
import '../../domain/entities/field_assignment_entity.dart';

class TaskCard extends StatelessWidget {
  final FieldWorkAssignmentEntity assignment;
  final VoidCallback? onTap;

  const TaskCard({
    super.key,
    required this.assignment,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Vertical status indicator line
              Container(
                width: 5,
                color: _statusColor,
              ),

              // Main Card Content
              Expanded(
                child: InkWell(
                  onTap: onTap,
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Top row: ID / Category + Status badge
                        Row(
                          children: [
                            // Complaint number or ID pill
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: AppColors.yemenBlack.withValues(alpha: 0.06),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                assignment.complaint?.complaintNumber ??
                                    'مهمة #${assignment.id}',
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.yemenBlack,
                                ),
                              ),
                            ),

                            if (assignment.complaint?.category != null) ...[
                              const SizedBox(width: 8),
                              Flexible(
                                child: Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: Colors.blue.withValues(alpha: 0.08),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    assignment.complaint!.category!.name,
                                    style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: Color(0xFF1D4ED8),
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ),
                            ],

                            const Spacer(),

                            // Status badge with icon
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: _statusColor.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: _statusColor.withValues(alpha: 0.3),
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(_statusIcon,
                                      size: 13, color: _statusColor),
                                  const SizedBox(width: 4),
                                  Text(
                                    assignment.statusArabic,
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: _statusColor,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: 10),

                        // Title
                        Text(
                          assignment.complaint?.title ??
                              'مهمة صيانة ومعاينة ميدانية #${assignment.id}',
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                            color: AppColors.textPrimary,
                            height: 1.3,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),

                        const SizedBox(height: 8),

                        // Location info if complaint available
                        if (assignment.complaint?.latitude != null) ...[
                          Row(
                            children: [
                              const Icon(Icons.location_on_outlined,
                                  size: 14, color: AppColors.textSecondary),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  'الموقع: (${assignment.complaint!.latitude.toStringAsFixed(4)}, ${assignment.complaint!.longitude.toStringAsFixed(4)})',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    color: AppColors.textSecondary,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                        ],

                        // Progress Stepper Line
                        _buildMiniStepper(),

                        const Divider(height: 20, color: AppColors.borderSubtle),

                        // Bottom Action Row
                        Row(
                          children: [
                            // Date
                            Icon(Icons.schedule_rounded,
                                size: 14, color: AppColors.textMuted),
                            const SizedBox(width: 4),
                            Text(
                              _formatDate(assignment.createdAt),
                              style: const TextStyle(
                                fontSize: 11,
                                color: AppColors.textSecondary,
                              ),
                            ),

                            const Spacer(),

                            // Quick Action Button
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 6),
                              decoration: BoxDecoration(
                                color: _actionButtonColor,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(
                                    _actionButtonText,
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: _actionButtonTextColor,
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  Icon(
                                    Icons.arrow_forward_ios_rounded,
                                    size: 10,
                                    color: _actionButtonTextColor,
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMiniStepper() {
    int activeStep = 0;
    if (assignment.isAccepted) activeStep = 1;
    if (assignment.isInProgress) activeStep = 2;
    if (assignment.isCompleted) activeStep = 3;

    final steps = ['إسناد', 'قبول', 'تنفيذ', 'إنجاز'];

    return Row(
      children: List.generate(steps.length, (index) {
        final isPassed = index <= activeStep;
        final isCurrent = index == activeStep;

        return Expanded(
          child: Row(
            children: [
              Container(
                width: 14,
                height: 14,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: isPassed ? _statusColor : AppColors.borderSubtle,
                  border: isCurrent
                      ? Border.all(color: Colors.white, width: 2)
                      : null,
                ),
                child: isPassed && index < activeStep
                    ? const Icon(Icons.check, size: 9, color: Colors.white)
                    : null,
              ),
              const SizedBox(width: 4),
              Text(
                steps[index],
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: isCurrent ? FontWeight.bold : FontWeight.w500,
                  color: isPassed ? AppColors.textPrimary : AppColors.textMuted,
                ),
              ),
              if (index < steps.length - 1)
                Expanded(
                  child: Container(
                    height: 2,
                    margin: const EdgeInsets.symmetric(horizontal: 4),
                    color: index < activeStep
                        ? _statusColor
                        : AppColors.borderSubtle,
                  ),
                ),
            ],
          ),
        );
      }),
    );
  }

  Color get _statusColor {
    switch (assignment.status) {
      case 'pending':
        return AppColors.yemenGold;
      case 'accepted':
        return const Color(0xFF2563EB); // Modern blue
      case 'in_progress':
        return AppColors.yemenRed;
      case 'completed':
        return AppColors.yemenEmerald;
      case 'failed':
        return const Color(0xFF9CA3AF);
      default:
        return AppColors.textSecondary;
    }
  }

  IconData get _statusIcon {
    switch (assignment.status) {
      case 'pending':
        return Icons.hourglass_top_rounded;
      case 'accepted':
        return Icons.thumb_up_alt_rounded;
      case 'in_progress':
        return Icons.engineering_rounded;
      case 'completed':
        return Icons.verified_rounded;
      case 'failed':
        return Icons.cancel_outlined;
      default:
        return Icons.assignment_rounded;
    }
  }

  String get _actionButtonText {
    switch (assignment.status) {
      case 'pending':
        return 'مراجعة وقبول المهمة';
      case 'accepted':
        return 'التحقق وبدء التنفيذ';
      case 'in_progress':
        return 'متابعة العمل الميداني';
      case 'completed':
        return 'عرض تقرير الإنجاز';
      default:
        return 'عرض التفاصيل';
    }
  }

  Color get _actionButtonColor {
    switch (assignment.status) {
      case 'pending':
        return AppColors.yemenGold.withValues(alpha: 0.15);
      case 'accepted':
        return const Color(0xFF2563EB).withValues(alpha: 0.12);
      case 'in_progress':
        return AppColors.yemenRed.withValues(alpha: 0.12);
      case 'completed':
        return AppColors.yemenEmerald.withValues(alpha: 0.12);
      default:
        return AppColors.yemenBlack.withValues(alpha: 0.08);
    }
  }

  Color get _actionButtonTextColor {
    switch (assignment.status) {
      case 'pending':
        return const Color(0xFFB45309);
      case 'accepted':
        return const Color(0xFF1D4ED8);
      case 'in_progress':
        return AppColors.yemenRed;
      case 'completed':
        return AppColors.yemenEmerald;
      default:
        return AppColors.textPrimary;
    }
  }

  String _formatDate(DateTime? date) {
    if (date == null) return '—';
    try {
      return DateFormat('yyyy/MM/dd  HH:mm', 'ar').format(date);
    } catch (_) {
      return DateFormat('yyyy/MM/dd  HH:mm').format(date);
    }
  }
}
