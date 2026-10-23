import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../../core/constants/app_colors.dart';

class ProjectProgressBar extends StatelessWidget {
  final double currentAmount;
  final double targetAmount;
  final double progressPercentage;
  final bool showAmounts;
  final bool isCompact;

  const ProjectProgressBar({
    super.key,
    required this.currentAmount,
    required this.targetAmount,
    required this.progressPercentage,
    this.showAmounts = true,
    this.isCompact = false,
  });

  @override
  Widget build(BuildContext context) {
    final currencyFormatter = NumberFormat('#,##0.##', 'ar');
    final validPercentage =
        progressPercentage.isNaN || progressPercentage.isInfinite
            ? 0.0
            : progressPercentage.clamp(0.0, 100.0);
    final ratio = (validPercentage / 100.0).clamp(0.0, 1.0);

    final isCompleted = validPercentage >= 100;
    final primaryColor =
        isCompleted ? AppColors.yemenEmerald : AppColors.yemenRed;
    final secondaryColor =
        isCompleted ? const Color(0xFF059669) : const Color(0xFFEA580C);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      mainAxisSize: MainAxisSize.min,
      children: [
        // Modern Thick Capsule Progress Bar with Embedded/Floating Percentage
        Container(
          height: isCompact ? 10 : 28,
          decoration: BoxDecoration(
            color: const Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(isCompact ? 8 : 12),
          ),
          clipBehavior: Clip.antiAlias,
          child: LayoutBuilder(
            builder: (context, constraints) {
              final fillWidth =
                  (constraints.maxWidth * ratio).clamp(0.0, constraints.maxWidth);

              return Stack(
                alignment: Alignment.centerRight,
                children: [
                  // Animated Gradient Fill
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 600),
                    curve: Curves.easeOutCubic,
                    width: fillWidth,
                    height: double.infinity,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.centerRight,
                        end: Alignment.centerLeft,
                        colors: [
                          primaryColor,
                          secondaryColor,
                        ],
                      ),
                      borderRadius: BorderRadius.circular(isCompact ? 8 : 12),
                    ),
                  ),

                  // Percentage Label inside bar (when not compact)
                  if (!isCompact)
                    Positioned(
                      left: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: ratio > 0.82
                              ? Colors.black.withValues(alpha: 0.25)
                              : Colors.white.withValues(alpha: 0.9),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          '${validPercentage.toStringAsFixed(1)}%',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w900,
                            color: ratio > 0.82 ? Colors.white : primaryColor,
                          ),
                        ),
                      ),
                    ),
                ],
              );
            },
          ),
        ),

        // Optional amounts row (used in list cards)
        if (showAmounts) ...[
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'الهدف: ${currencyFormatter.format(targetAmount)} ريال',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: AppColors.yemenBlack,
                ),
              ),
              if (currentAmount >= targetAmount && targetAmount > 0)
                const Text(
                  'مكتمل التمويل 🎉',
                  style: TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w800,
                    color: AppColors.yemenEmerald,
                  ),
                )
              else
                Text(
                  'تم جمع: ${currencyFormatter.format(currentAmount)} ريال',
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textSecondary,
                  ),
                ),
            ],
          ),
        ],
      ],
    );
  }
}