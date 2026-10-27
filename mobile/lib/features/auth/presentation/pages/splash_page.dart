import 'dart:async';

import 'package:flutter/material.dart';
import 'package:mobile/core/constants/app_assets.dart';
import 'package:mobile/core/constants/app_colors.dart';

class SplashPage extends StatefulWidget {
  const SplashPage({super.key, this.onFinished});

  /// يُستدعى بعد عرض شاشة البداية لمدة 3 ثوانٍ.
  final VoidCallback? onFinished;

  @override
  State<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends State<SplashPage>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _fadeAnimation;
  late final Animation<double> _scaleAnimation;
  Timer? _timer;

  @override
  void initState() {
    super.initState();

    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    );

    _fadeAnimation = CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOut,
    );

    _scaleAnimation = Tween<double>(
      begin: 0.92,
      end: 1.0,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOutCubic));

    _controller.forward();

    _timer = Timer(const Duration(seconds: 3), () {
      if (mounted) {
        widget.onFinished?.call();
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F9FB),
      body: Stack(
        children: [
          // زخرفة هندسية خفيفة في أعلى الشاشة.
          Positioned(
            top: -100,
            right: -90,
            child: _decorationCircle(
              size: 270,
              color: AppColors.yemenRed.withValues(alpha: 0.045),
            ),
          ),
          Positioned(
            bottom: -130,
            left: -100,
            child: _decorationCircle(
              size: 300,
              color: AppColors.yemenBlack.withValues(alpha: 0.035),
            ),
          ),

          SafeArea(
            child: Center(
              child: FadeTransition(
                opacity: _fadeAnimation,
                child: ScaleTransition(
                  scale: _scaleAnimation,
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 32),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        // الشعار الرئيسي.
                        SizedBox(
                          width: double.infinity,
                          child: Center(
                            child: Image.asset(
                              AppAssets.logoHorizontal,
                              width: 320,
                              height: 200,
                              fit: BoxFit.contain,
                              errorBuilder: (context, error, stackTrace) {
                                return const Text(
                                  'بادر',
                                  style: TextStyle(
                                    fontSize: 52,
                                    fontWeight: FontWeight.w800,
                                    color: AppColors.yemenBlack,
                                  ),
                                );
                              },
                            ),
                          ),
                        ),

                        const SizedBox(height: 28),

                        const Text(
                          'بادر',
                          textDirection: TextDirection.rtl,
                          style: TextStyle(
                            fontSize: 32,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 0.2,
                            color: AppColors.yemenBlack,
                          ),
                        ),

                        const SizedBox(height: 12),

                        const Text(
                          'معا نحو خدمات أفضل',
                          textDirection: TextDirection.rtl,
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 15,
                            height: 1.7,
                            fontWeight: FontWeight.w500,
                            color: Color(0xFF737783),
                          ),
                        ),

                        const SizedBox(height: 48),

                        // مؤشر تحميل بسيط وأنيق.
                        SizedBox(
                          width: 108,
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(20),
                            child: const LinearProgressIndicator(
                              minHeight: 4,
                              backgroundColor: Color(0xFFE7E8EC),
                              valueColor: AlwaysStoppedAnimation<Color>(
                                AppColors.yemenRed,
                              ),
                            ),
                          ),
                        ),

                        const SizedBox(height: 18),

                        const Text(
                          'جار تجهيز البرنامج',
                          textDirection: TextDirection.rtl,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                            color: Color(0xFF989BA5),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),

          // ألوان العلم اليمني في أسفل الشاشة.
          const Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Row(
              children: [
                Expanded(
                  child: SizedBox(
                    height: 4,
                    child: ColoredBox(color: Color(0xFF08080A)),
                  ),
                ),
                Expanded(
                  child: SizedBox(
                    height: 4,
                    child: ColoredBox(color: Color(0xFFFFFFFF)),
                  ),
                ),
                Expanded(
                  child: SizedBox(
                    height: 4,
                    child: ColoredBox(color: Color(0xFFCE1126)),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _decorationCircle({required double size, required Color color}) {
    return IgnorePointer(
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(shape: BoxShape.circle, color: color),
      ),
    );
  }
}
