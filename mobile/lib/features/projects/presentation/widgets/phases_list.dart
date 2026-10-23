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
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppColors.borderSubtle),
        ),
        child: const Column(
          children: [
            Icon(Icons.timeline_rounded, size: 36, color: AppColors.textMuted),
            SizedBox(height: 8),
            Text(
              'لم يتم إدراج مراحل تنفيذية لهذا المشروع بعد.',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
          ],
        ),
      );
    }

    final dateFormatter = DateFormat('yyyy/MM/dd', 'ar');

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: EdgeInsets.zero,
      itemCount: phases.length,
      itemBuilder: (context, index) {
        final phase = phases[index];
        final isLast = index == phases.length - 1;
        final isCompleted = phase.isCompleted;
        final isInProgress = phase.isInProgress;

        Color indicatorColor;
        Color indicatorBg;
        IconData indicatorIcon;
        String statusLabel;

        if (isCompleted) {
          indicatorColor = AppColors.yemenEmerald;
          indicatorBg = AppColors.yemenEmeraldLight;
          indicatorIcon = Icons.check_rounded;
          statusLabel = 'مكتملة';
        } else if (isInProgress) {
          indicatorColor = AppColors.yemenRed;
          indicatorBg = AppColors.yemenRedLight;
          indicatorIcon = Icons.sync_rounded;
          statusLabel = 'قيد التنفيذ';
        } else {
          indicatorColor = AppColors.textMuted;
          indicatorBg = const Color(0xFFF3F4F6);
          indicatorIcon = Icons.schedule_rounded;
          statusLabel = 'في الانتظار';
        }

        return IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Timeline Node & Vertical Connector Line
              SizedBox(
                width: 36,
                child: Column(
                  children: [
                    Container(
                      width: 32,
                      height: 32,
                      decoration: BoxDecoration(
                        color: isCompleted || isInProgress
                            ? indicatorColor
                            : Colors.white,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: indicatorColor,
                          width: 2,
                        ),
                        boxShadow: isInProgress
                            ? [
                                BoxShadow(
                                  color: indicatorColor.withValues(alpha: 0.25),
                                  blurRadius: 8,
                                  offset: const Offset(0, 2),
                                ),
                              ]
                            : null,
                      ),
                      child: Center(
                        child: isCompleted || isInProgress
                            ? Icon(indicatorIcon, color: Colors.white, size: 16)
                            : Text(
                                '${index + 1}',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: indicatorColor,
                                ),
                              ),
                      ),
                    ),
                    if (!isLast)
                      Expanded(
                        child: Container(
                          width: 2,
                          margin: const EdgeInsets.symmetric(vertical: 4),
                          color: isCompleted
                              ? AppColors.yemenEmerald.withValues(alpha: 0.5)
                              : AppColors.borderSubtle,
                        ),
                      ),
                  ],
                ),
              ),
              const SizedBox(width: 10),

              // 2. Phase Details Card
              Expanded(
                child: Container(
                  margin: EdgeInsets.only(bottom: isLast ? 0 : 14),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(
                      color: isInProgress
                          ? AppColors.yemenRed.withValues(alpha: 0.35)
                          : AppColors.borderSubtle,
                      width: isInProgress ? 1.4 : 1.0,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.015),
                        blurRadius: 8,
                        offset: const Offset(0, 3),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header: Title & Status Badge
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Text(
                              'المرحلة ${index + 1}: ${phase.name}',
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w800,
                                color: AppColors.yemenBlack,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: indicatorBg,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Text(
                              statusLabel,
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w800,
                                color: indicatorColor,
                              ),
                            ),
                          ),
                        ],
                      ),

                      if (phase.description != null &&
                          phase.description!.trim().isNotEmpty) ...[
                        const SizedBox(height: 8),
                        Text(
                          phase.description!,
                          style: const TextStyle(
                            fontSize: 12.5,
                            color: AppColors.textSecondary,
                            height: 1.5,
                          ),
                        ),
                      ],

                      const SizedBox(height: 12),

                      // Progress Bar & Percentage
                      Row(
                        children: [
                          Expanded(
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(6),
                              child: LinearProgressIndicator(
                                value: (phase.completionPercentage / 100.0)
                                    .clamp(0.0, 1.0),
                                backgroundColor: const Color(0xFFF1F5F9),
                                valueColor: AlwaysStoppedAnimation<Color>(
                                    indicatorColor),
                                minHeight: 7,
                              ),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Text(
                            '${phase.completionPercentage}%',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w900,
                              color: indicatorColor,
                            ),
                          ),
                        ],
                      ),

                      // Dates Footer
                      if (phase.startDate != null || phase.endDate != null) ...[
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            const Icon(Icons.date_range_rounded,
                                size: 14, color: AppColors.textMuted),
                            const SizedBox(width: 6),
                            Text(
                              '${phase.startDate != null ? dateFormatter.format(phase.startDate!) : "غير محدد"}  ←  ${phase.endDate != null ? dateFormatter.format(phase.endDate!) : "غير محدد"}',
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
            ],
          ),
        );
      },
    );
  }
}