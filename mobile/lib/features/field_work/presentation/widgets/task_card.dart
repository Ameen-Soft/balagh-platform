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
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.borderSubtle),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.03),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header row: complaint title + status badge
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Task icon
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: _statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    _statusIcon,
                    color: _statusColor,
                    size: 22,
                  ),
                ),
                const SizedBox(width: 12),
                // Title and complaint info
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        assignment.complaint?.title ?? 'مهمة ميدانية #${assignment.id}',
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      if (assignment.complaint?.complaintNumber != null)
                        Text(
                          assignment.complaint!.complaintNumber,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppColors.textSecondary,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                // Status badge
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: _statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: _statusColor.withValues(alpha: 0.3),
                    ),
                  ),
                  child: Text(
                    assignment.statusArabic,
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: _statusColor,
                    ),
                  ),
                ),
              ],
            ),

            const Divider(height: 20, color: AppColors.borderSubtle),

            // Bottom info row
            Row(
              children: [
                // Category
                if (assignment.complaint?.category != null) ...[
                  Icon(Icons.category_outlined,
                      size: 14, color: AppColors.textMuted),
                  const SizedBox(width: 4),
                  Flexible(
                    child: Text(
                      assignment.complaint!.category!.name,
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 12),
                ],
                // Date
                Icon(Icons.schedule_outlined,
                    size: 14, color: AppColors.textMuted),
                const SizedBox(width: 4),
                Text(
                  _formatDate(assignment.createdAt),
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.textSecondary,
                  ),
                ),
                const Spacer(),
                // Arrow
                const Icon(
                  Icons.arrow_forward_ios_rounded,
                  size: 14,
                  color: AppColors.textMuted,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Color get _statusColor {
    switch (assignment.status) {
      case 'pending':
        return AppColors.yemenGold;
      case 'accepted':
        return const Color(0xFF2563EB); // blue
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
        return Icons.pending_actions_rounded;
      case 'accepted':
        return Icons.thumb_up_alt_rounded;
      case 'in_progress':
        return Icons.engineering_rounded;
      case 'completed':
        return Icons.check_circle_rounded;
      case 'failed':
        return Icons.error_outline_rounded;
      default:
        return Icons.assignment_rounded;
    }
  }

  String _formatDate(DateTime? date) {
    if (date == null) return '—';
    try {
      return DateFormat('dd/MM/yyyy', 'ar').format(date);
    } catch (_) {
      return DateFormat('dd/MM/yyyy').format(date);
    }
  }
}
