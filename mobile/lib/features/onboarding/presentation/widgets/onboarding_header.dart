import 'package:flutter/material.dart';
import 'package:mobile/core/constants/app_assets.dart';
import 'package:mobile/core/constants/app_colors.dart';

/// Top header widget displaying the brand logo in an elegant capsule with enhanced scale and contrast
class OnboardingHeader extends StatelessWidget {
  const OnboardingHeader({super.key});

  @override
  Widget build(BuildContext context) {
    return Padding(
      // التعديل هنا: إضافة حواف جانبية (left و right) بقيمة 20 لترك المسافة المطلوبة
      padding: const EdgeInsets.only(
        top: 10.0,
        bottom: 6.0,
        left: 20.0,
        right: 20.0,
      ),
      child: Center(
        child: Container(
          height: 56,
          // التعديل هنا: استخدام double.infinity ليأخذ العرض كاملاً المتبقي بعد الحواف الجانبية
          width: double.infinity,
          clipBehavior: Clip.antiAlias,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [AppColors.yemenWhite, AppColors.yemenBlack],
            ),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Center(
            child: Transform.scale(
              scale: 1.8,
              child: Image.asset(
                AppAssets.logoHorizontal,
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) => const Text(
                  'بادر',
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
