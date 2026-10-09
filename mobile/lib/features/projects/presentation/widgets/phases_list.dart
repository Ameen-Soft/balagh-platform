import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../../core/constants/app_colors.dart';
import '../../domain/entities/project_phase_entity.dart';

class PhasesList extends StatelessWidget {
  final List<ProjectPhaseEntity> phases;

  const PhasesList({
    super.key,
    required this.phases,
  });

  @override
  Widget build(BuildContext context) {
    if (phases.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.borderSubtle),
        ),
        child: const Center(
          child: Text(
            'لم يتم إدراج مراحل تنفيذية لهذا المشروع بعد.',
            style: TextStyle(
              fontSize: 13,
              color: AppColors.textSecondary,
            ),
          ),
        ),
      );
    }

    final dateFormatter = DateFormat('yyyy/MM/dd', 'ar');

    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: phases.length,
      separatorBuilder: (context, index) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final phase = phases[index];
        final isCompleted = phase.isCompleted;
        final isInProgress = phase.isInProgress;

        Color indicatorColor;
        IconData indicatorIcon;
        String statusLabel;

        if (isCompleted) {
          indicatorColor = AppColors.yemenEmerald;
          indicatorIcon = Icons.check_circle_rounded;
          statusLabel = 'مكتملة';
        } else if (isInProgress) {
          indicatorColor = AppColors.yemenGold;
          indicatorIcon = Icons.radio_button_checked_rounded;
          statusLabel = 'قيد التنفيذ';
        } else {
          indicatorColor = AppColors.textMuted;
          indicatorIcon = Icons.radio_button_off_rounded;
          statusLabel = 'في الانتظار';
        }

        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isInProgress
                  ? AppColors.yemenGold.withValues(alpha: 0.5)
                  : AppColors.borderSubtle,
              width: isInProgress ? 1.5 : 1.0,
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(indicatorIcon, color: indicatorColor, size: 22),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'المرحلة ${index + 1}: ${phase.name}',
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: AppColors.yemenBlack,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: indicatorColor.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Text(
                                statusLabel,
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: indicatorColor,
                                ),
                              ),
                            ),
                          ],
                        ),
                        if (phase.description != null &&
                            phase.description!.isNotEmpty) ...[
                          const SizedBox(height: 6),
                          Text(
                            phase.description!,
                            style: const TextStyle(
                              fontSize: 12,
                              color: AppColors.textSecondary,
                              height: 1.3,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),

              // Phase progress bar
              Row(
                children: [
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: LinearProgressIndicator(
                        value: (phase.completionPercentage / 100.0)
                            .clamp(0.0, 1.0),
                        backgroundColor: AppColors.borderSubtle,
                        valueColor:
                            AlwaysStoppedAnimation<Color>(indicatorColor),
                        minHeight: 6,
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Text(
                    '${phase.completionPercentage}%',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: indicatorColor,
                    ),
                  ),
                ],
              ),

              // Dates info
              if (phase.startDate != null || phase.endDate != null) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.calendar_today_outlined,
                        size: 13, color: AppColors.textMuted),
                    const SizedBox(width: 4),
                    Text(
                      '${phase.startDate != null ? dateFormatter.format(phase.startDate!) : "غير محدد"} - ${phase.endDate != null ? dateFormatter.format(phase.endDate!) : "غير محدد"}',
                      style: const TextStyle(
                        fontSize: 11,
                        color: AppColors.textMuted,
                      ),
                    ),
                  ],
                ),
              ],
            ],
          ),
        );
      },
    );
  }
}
