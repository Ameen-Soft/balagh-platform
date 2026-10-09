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
    final validPercentage = progressPercentage.isNaN || progressPercentage.isInfinite
        ? 0.0
        : progressPercentage.clamp(0.0, 100.0);
    final ratio = (validPercentage / 100.0).clamp(0.0, 1.0);

    // Color tone depending on completion
    final progressColor = validPercentage >= 100
        ? AppColors.yemenEmerald
        : (validPercentage >= 50 ? AppColors.yemenGold : AppColors.yemenRed);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      mainAxisSize: MainAxisSize.min,
      children: [
        if (showAmounts) ...[
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.monetization_on_outlined,
                      size: 16, color: AppColors.textSecondary),
                  const SizedBox(width: 4),
                  Text(
                    'تم جمع: ${currencyFormatter.format(currentAmount)} ريال',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: AppColors.yemenBlack,
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: progressColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '${validPercentage.toStringAsFixed(1)}%',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: progressColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
        ],
        // Progress Bar
        Container(
          height: isCompact ? 8 : 12,
          decoration: BoxDecoration(
            color: AppColors.borderSubtle,
            borderRadius: BorderRadius.circular(10),
          ),
          clipBehavior: Clip.antiAlias,
          child: LayoutBuilder(
            builder: (context, constraints) {
              return Stack(
                children: [
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 500),
                    curve: Curves.easeOutCubic,
                    width: constraints.maxWidth * ratio,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          progressColor.withValues(alpha: 0.85),
                          progressColor,
                        ],
                      ),
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ],
              );
            },
          ),
        ),
        if (showAmounts && targetAmount > 0) ...[
          const SizedBox(height: 6),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'الهدف: ${currencyFormatter.format(targetAmount)} ريال',
                style: const TextStyle(
                  fontSize: 12,
                  color: AppColors.textSecondary,
                ),
              ),
              if (currentAmount >= targetAmount)
                const Text(
                  'تم اكتمال التمويل بنجاح 🎉',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppColors.yemenEmerald,
                  ),
                )
              else
                Text(
                  'المتبقي: ${currencyFormatter.format((targetAmount - currentAmount).clamp(0, double.infinity))} ريال',
                  style: const TextStyle(
                    fontSize: 11,
                    color: AppColors.textMuted,
                  ),
                ),
            ],
          ),
        ],
      ],
    );
  }
}
