import 'package:flutter/material.dart';
import 'package:mobile/core/constants/app_colors.dart';
import 'package:mobile/features/onboarding/domain/entities/onboarding_item.dart';

/// Single onboarding slide view displaying illustration, title, and description
class OnboardingItemView extends StatelessWidget {
  final OnboardingItem item;

  const OnboardingItemView({
    super.key,
    required this.item,
  });

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 3D Isometric Illustration
          Expanded(
            flex: 6,
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 12.0),
              child: Image.asset(
                item.image,
                fit: BoxFit.contain,
                filterQuality: FilterQuality.medium,
              ),
            ),
          ),

          const SizedBox(height: 16),

          // Title
          Text(
            item.title,
            textAlign: TextAlign.right,
            style: const TextStyle(
              fontSize: 26,
              fontWeight: FontWeight.w800,
              color: AppColors.yemenBlack,
              letterSpacing: -0.5,
              height: 1.3,
            ),
          ),

          const SizedBox(height: 12),

          // Description
          Text(
            item.description,
            textAlign: TextAlign.right,
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w400,
              color: AppColors.textSecondary,
              height: 1.6,
            ),
          ),

          // Bottom breathing room above controls
          SizedBox(height: size.height * 0.04),
        ],
      ),
    );
  }
}
