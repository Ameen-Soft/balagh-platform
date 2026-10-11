import 'package:flutter/material.dart';
import '../../../../core/constants/app_assets.dart';
import '../../../../core/constants/app_colors.dart';

/// Top header for the Login screen:
/// - Illustration (assets/images/Login_Signup_photo.jpg)
/// - Title: "مرحباً بك في بادر"
/// - Subtitle: "معاً نحو مجتمع أكثر أماناً وازدهاراً"
class LoginAuthHeader extends StatelessWidget {
  const LoginAuthHeader({super.key});

  @override
  Widget build(BuildContext context) {
    final screenHeight = MediaQuery.of(context).size.height;
    final imageWidth = (screenHeight * 0.25).clamp(150.0, 220.0);

    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        // Responsive illustration
        Center(
          child: Image.asset(
            AppAssets.authIllustration,
            height: imageWidth,
            fit: BoxFit.contain,
            errorBuilder: (context, error, stackTrace) => Container(
              height: imageWidth,
              width: imageWidth,
              decoration: BoxDecoration(
                color: const Color(0xFFF3F4F6),
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(
                Icons.image_outlined,
                color: AppColors.textMuted,
                size: 48,
              ),
            ),
          ),
        ),
        const SizedBox(height: 14),

        // Welcome Headline
        const Text(
          'مرحباً بك في بادر',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w800,
            color: AppColors.yemenBlack,
            letterSpacing: -0.3,
          ),
        ),
        const SizedBox(height: 6),

        // Subtitle
        const Text(
          'معاً نحو مجتمع أكثر أماناً وازدهاراً',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.normal,
            color: AppColors.textSecondary,
          ),
        ),
      ],
    );
  }
}

/// Top header for the Sign Up / Register screen:
/// - Top bar with Back Button
/// - Centered Title: "إنشاء حساب جديد"
/// - Centered Subtitle: "انضم إلى منصة بادر وكن جزءاً من التغيير"
/// - Illustration (assets/images/Login_Signup_photo.jpg)
class RegisterAuthHeader extends StatelessWidget {
  final VoidCallback onBack;

  const RegisterAuthHeader({super.key, required this.onBack});

  @override
  Widget build(BuildContext context) {
    final screenHeight = MediaQuery.of(context).size.height;
    final imageWidth = (screenHeight * 0.18).clamp(115.0, 165.0);

    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        // Top navigation bar with back arrow
        Stack(
          alignment: Alignment.center,
          children: [
            Align(
              alignment: Alignment.centerRight,
              child: IconButton(
                icon: const Icon(
                  Icons.arrow_back_ios_new_rounded,
                  color: AppColors.yemenBlack,
                  size: 20,
                ),
                tooltip: 'رجوع',
                onPressed: onBack,
              ),
            ),
            // Centered Title & Subtitle
            Column(
              mainAxisSize: MainAxisSize.min,
              children: const [
                Text(
                  'إنشاء حساب جديد',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                    color: AppColors.yemenBlack,
                    letterSpacing: -0.3,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  'انضم إلى منصة بادر وكن جزءاً من التغيير',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.normal,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ],
        ),
        const SizedBox(height: 10),

        // Responsive illustration below the subtitle
        Center(
          child: Image.asset(
            AppAssets.authIllustration,
            height: imageWidth,
            fit: BoxFit.contain,
            errorBuilder: (context, error, stackTrace) => Container(
              height: imageWidth,
              width: imageWidth,
              decoration: BoxDecoration(
                color: const Color(0xFFF3F4F6),
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(
                Icons.image_outlined,
                color: AppColors.textMuted,
                size: 40,
              ),
            ),
          ),
        ),
      ],
    );
  }
}
