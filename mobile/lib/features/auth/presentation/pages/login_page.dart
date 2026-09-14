import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/core/constants/app_assets.dart';
import 'package:mobile/core/constants/app_colors.dart';
import 'package:mobile/features/onboarding/domain/repositories/onboarding_repository.dart';

/// Landing page displayed after onboarding is completed
class LoginPage extends StatelessWidget {
  final OnboardingRepository repository;

  const LoginPage({
    super.key,
    required this.repository,
  });

  Future<void> _resetOnboarding(BuildContext context) async {
    await repository.resetOnboarding();
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'تم إعادة تعيين حالة شاشات التهيئة بنجاح! سيتم نقلك الآن...',
            textAlign: TextAlign.right,
          ),
          backgroundColor: AppColors.yemenEmerald,
          duration: Duration(seconds: 2),
        ),
      );
      Future.delayed(const Duration(milliseconds: 600), () {
        if (context.mounted) {
          context.go('/onboarding');
        }
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(28.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Spacer(),

              // Brand Logo Badge
              Center(
                child: Container(
                  height: 62,
                  width: 200,
                  clipBehavior: Clip.antiAlias,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        Color(0xFFF9FAFB),
                        Color(0xFFE5E9EE),
                      ],
                    ),
                    borderRadius: BorderRadius.circular(31),
                    border: Border.all(
                      color: const Color(0xFFD1D5DB),
                      width: 1.2,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.06),
                        blurRadius: 10,
                        offset: const Offset(0, 3),
                      ),
                      BoxShadow(
                        color: AppColors.yemenRed.withOpacity(0.08),
                        blurRadius: 6,
                        offset: const Offset(0, 1),
                      ),
                    ],
                  ),
                  child: Center(
                    child: Transform.scale(
                      scale: 2.7,
                      child: Image.asset(
                        AppAssets.logoHorizontal,
                        fit: BoxFit.contain,
                        errorBuilder: (context, error, stackTrace) => const Text(
                          'بادر',
                          style: TextStyle(
                            fontSize: 26,
                            fontWeight: FontWeight.bold,
                            color: AppColors.yemenBlack,
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),

              const SizedBox(height: 28),

              const Text(
                'مرحباً بك في منصة بادر',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w800,
                  color: AppColors.yemenBlack,
                ),
              ),

              const SizedBox(height: 12),

              const Text(
                'تم تجاوز مرحلة التهيئة (Onboarding) بنجاح وحفظ الحالة محلياً.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 15,
                  color: AppColors.textSecondary,
                  height: 1.5,
                ),
              ),

              const Spacer(),

              // Development / Testing Button: Reset Onboarding
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.yemenRedLight,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: AppColors.yemenRed.withOpacity(0.2),
                  ),
                ),
                child: Column(
                  children: [
                    const Text(
                      'خيار للمطور للاختبار:',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: AppColors.yemenRedHover,
                      ),
                    ),
                    const SizedBox(height: 8),
                    ElevatedButton.icon(
                      onPressed: () => _resetOnboarding(context),
                      icon: const Icon(Icons.refresh_rounded, size: 18),
                      label: const Text(
                        'إعادة تجربة شاشات التهيئة (Reset Onboarding)',
                        style: TextStyle(fontWeight: FontWeight.w700),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.yemenRed,
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 12,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }
}
