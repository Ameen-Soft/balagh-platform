import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/router/app_router.dart';
import '../../domain/entities/onboarding_item.dart';
import '../../domain/repositories/onboarding_repository.dart';
import '../widgets/onboarding_button.dart';
import '../widgets/onboarding_dots.dart';
import '../widgets/onboarding_header.dart';
import '../widgets/onboarding_item_view.dart';

/// The main Onboarding Screen managing PageView, Dots, and Persistent State
class OnboardingPage extends ConsumerStatefulWidget {
  final OnboardingRepository repository;

  const OnboardingPage({
    super.key,
    required this.repository,
  });

  @override
  ConsumerState<OnboardingPage> createState() => _OnboardingPageState();
}

class _OnboardingPageState extends ConsumerState<OnboardingPage> {
  final PageController _pageController = PageController();
  int _currentIndex = 0;

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _onNext() {
    if (_currentIndex < OnboardingItem.items.length - 1) {
      _pageController.nextPage(
        duration: const Duration(milliseconds: 350),
        curve: Curves.easeInOutCubic,
      );
    }
  }

  Future<void> _onStart() async {
    // Save completion flag in SharedPreferences via Clean Architecture repository
    await widget.repository.completeOnboarding();

    if (!mounted) return;
    ref.read(onboardingStatusProvider.notifier).setCompleted(true);
    // Navigate to Login/Home using GoRouter
    context.go('/login');
  }

  @override
  Widget build(BuildContext context) {
    final isLastPage = _currentIndex == OnboardingItem.items.length - 1;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: Stack(
        children: [
          // Background subtle contour / topography decoration
          Positioned.fill(
            child: CustomPaint(
              painter: _TopographyBackgroundPainter(),
            ),
          ),

          SafeArea(
            child: Column(
              children: [
                // Top Brand Logo
                const OnboardingHeader(),

                // PageView with the 3 onboarding screens
                Expanded(
                  child: PageView.builder(
                    controller: _pageController,
                    physics: const BouncingScrollPhysics(),
                    itemCount: OnboardingItem.items.length,
                    onPageChanged: (index) {
                      setState(() {
                        _currentIndex = index;
                      });
                    },
                    itemBuilder: (context, index) {
                      return OnboardingItemView(
                        item: OnboardingItem.items[index],
                      );
                    },
                  ),
                ),

                // Bottom Navigation Bar (Dots on one side, Next/Start Button on the other)
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24.0,
                    vertical: 24.0,
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Page Indicator Dots
                      OnboardingDots(
                        count: OnboardingItem.items.length,
                        currentIndex: _currentIndex,
                      ),

                      // Next / Start Button
                      OnboardingButton(
                        isLastPage: isLastPage,
                        onPressed: isLastPage ? _onStart : _onNext,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Subtle topographic curves painter matching the background aesthetic of the mockup
class _TopographyBackgroundPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFFF3F4F6).withValues(alpha: 0.45)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.0;

    // Bottom-right subtle contour curves
    final path1 = Path()
      ..moveTo(size.width, size.height * 0.72)
      ..quadraticBezierTo(
        size.width * 0.75,
        size.height * 0.85,
        size.width,
        size.height * 0.95,
      );

    final path2 = Path()
      ..moveTo(size.width, size.height * 0.76)
      ..quadraticBezierTo(
        size.width * 0.68,
        size.height * 0.88,
        size.width,
        size.height,
      );

    canvas.drawPath(path1, paint);
    canvas.drawPath(path2, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
