import 'package:flutter/material.dart';
import 'package:mobile/core/constants/app_colors.dart';

/// Animated page indicator dots matching the design
class OnboardingDots extends StatelessWidget {
  final int count;
  final int currentIndex;

  const OnboardingDots({
    super.key,
    required this.count,
    required this.currentIndex,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(
        count,
        (index) {
          final isActive = index == currentIndex;
          return AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeInOut,
            margin: const EdgeInsets.symmetric(horizontal: 4.0),
            width: isActive ? 18.0 : 7.0,
            height: 7.0,
            decoration: BoxDecoration(
              color: isActive ? AppColors.yemenRed : AppColors.dotInactive,
              borderRadius: BorderRadius.circular(4.0),
            ),
          );
        },
      ),
    );
  }
}
