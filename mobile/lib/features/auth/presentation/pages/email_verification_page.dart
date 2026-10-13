import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/constants/app_colors.dart';
import '../../application/providers.dart';
import '../widgets/auth_corner_decoration.dart';
import '../widgets/auth_header.dart';

/// Email verification screen matching the visual mockup:
/// - Top bar with Back Button
/// - Illustration (assets/images/Email-verify_photo.jpg)
/// - Title: "التحقق من بريدك الإلكتروني"
/// - Instructional copy
/// - Email badge / container
/// - Resend trigger with countdown timer
/// - Check status action & logout / return
/// - Bottom ribbon decoration (AuthCornerDecoration)
class EmailVerificationPage extends ConsumerStatefulWidget {
  final String? email;
  final String? status;

  const EmailVerificationPage({
    super.key,
    this.email,
    this.status,
  });

  @override
  ConsumerState<EmailVerificationPage> createState() =>
      _EmailVerificationPageState();
}

class _EmailVerificationPageState
    extends ConsumerState<EmailVerificationPage> {
  Timer? _timer;
  int _secondsRemaining = 59; // Starts at 00:59 matching mockup
  bool _isResending = false;
  bool _isCheckingStatus = false;

  @override
  void initState() {
    super.initState();
    _startCountdown();
    if (widget.status == 'success' || widget.status == 'already_verified') {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _handleDeepLinkSuccess(widget.status!);
      });
    }
  }

  Future<void> _handleDeepLinkSuccess(String status) async {
    await ref.read(authNotifierProvider.notifier).refreshCurrentUser();
    if (!mounted) return;

    final message = status == 'already_verified'
        ? 'تم التحقق من البريد الإلكتروني مسبقاً.'
        : 'تم تأكيد بريدك الإلكتروني بنجاح! مرحباً بك في منصة بادر.';

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          message,
          textAlign: TextAlign.right,
          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
        ),
        backgroundColor: AppColors.yemenEmerald,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
        ),
        margin: const EdgeInsets.all(16),
      ),
    );

    final authState = ref.read(authNotifierProvider);
    if (authState.isAuthenticated) {
      context.go('/home');
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  void _startCountdown() {
    _timer?.cancel();
    setState(() {
      _secondsRemaining = 59;
    });
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      if (_secondsRemaining > 0) {
        setState(() {
          _secondsRemaining--;
        });
      } else {
        timer.cancel();
      }
    });
  }

  String _formatTimer(int seconds) {
    final minutesStr = (seconds ~/ 60).toString().padLeft(2, '0');
    final secondsStr = (seconds % 60).toString().padLeft(2, '0');
    return '$minutesStr:$secondsStr';
  }

  Future<void> _resendVerificationEmail(String email) async {
    if (_secondsRemaining > 0 || _isResending) return;

    setState(() {
      _isResending = true;
    });

    try {
      await ref
          .read(authNotifierProvider.notifier)
          .sendEmailVerification(email: email);

      if (!mounted) return;

      _startCountdown();

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text(
            'تم إرسال رابط تحقق جديد إلى بريدك الإلكتروني بنجاح',
            textAlign: TextAlign.right,
            style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
          ),
          backgroundColor: AppColors.yemenBlack,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
          margin: const EdgeInsets.all(16),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            e.toString().contains('429')
                ? 'يرجى الانتظار قليلاً قبل طلب رابط جديد منعاً للإساءة.'
                : 'تعذر إرسال رسالة التحقق، يرجى المحاولة بعد قليل.',
            textAlign: TextAlign.right,
            style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
          ),
          backgroundColor: AppColors.yemenRed,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
          margin: const EdgeInsets.all(16),
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isResending = false;
        });
      }
    }
  }

  Future<void> _checkVerificationStatus() async {
    if (_isCheckingStatus) return;

    setState(() {
      _isCheckingStatus = true;
    });

    try {
      final isVerified = await ref
          .read(authNotifierProvider.notifier)
          .checkEmailVerificationStatus();

      if (!mounted) return;

      if (isVerified) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text(
              'تم تأكيد بريدك الإلكتروني بنجاح! جاري تحويلك...',
              textAlign: TextAlign.right,
              style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
            ),
            backgroundColor: AppColors.yemenEmerald,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
            margin: const EdgeInsets.all(16),
          ),
        );

        final authState = ref.read(authNotifierProvider);
        if (authState.isAuthenticated) {
          context.go('/home');
        } else {
          context.go('/login');
        }
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text(
              'لم يتم تأكيد البريد بعد. يرجى فتح البريد الإلكتروني والضغط على الرابط المرسل.',
              textAlign: TextAlign.right,
              style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
            ),
            backgroundColor: AppColors.yemenBlack,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
            margin: const EdgeInsets.all(16),
          ),
        );
      }
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text(
            'تعذر التحقق من حالة الحساب حالياً، يرجى التحقق من الاتصال بالإنترنت.',
            textAlign: TextAlign.right,
            style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
          ),
          backgroundColor: AppColors.yemenRed,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
          margin: const EdgeInsets.all(16),
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isCheckingStatus = false;
        });
      }
    }
  }

  void _handleBack() {
    _handleLogoutOrBack();
  }

  Future<void> _handleLogoutOrBack() async {
    final authState = ref.read(authNotifierProvider);
    if (authState.isAuthenticated) {
      await ref.read(authNotifierProvider.notifier).logout();
    }
    if (mounted) {
      context.go('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);
    final displayEmail = widget.email ??
        authState.user?.email ??
        'example@domain.com';

    return Scaffold(
      backgroundColor: AppColors.background,
      body: Stack(
        children: [
          // Decorative Yemen Flag Corner Ribbon pinned to the true screen bottom
          Positioned(
            bottom: -MediaQuery.of(context).viewInsets.bottom,
            left: 0,
            child: const AuthCornerDecoration(),
          ),

          // Main Content
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                physics: const ClampingScrollPhysics(),
                padding: const EdgeInsets.symmetric(
                  horizontal: 24.0,
                  vertical: 16.0,
                ),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 420),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Header with Illustration & Instructions
                      EmailVerifyAuthHeader(onBack: _handleBack),

                      const SizedBox(height: 22),

                      // Email Display Container (matching mockup)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 20,
                          vertical: 13,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          displayEmail,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w600,
                            color: AppColors.yemenBlack,
                            letterSpacing: 0.1,
                          ),
                        ),
                      ),

                      const SizedBox(height: 24),

                      // Cooldown / Resend Section (matching mockup)
                      Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: const [
                              Icon(
                                Icons.sync_rounded,
                                size: 16,
                                color: AppColors.textSecondary,
                              ),
                              SizedBox(width: 6),
                              Text(
                                'لم يصلك الرابط؟',
                                style: TextStyle(
                                  fontSize: 12.5,
                                  color: AppColors.textSecondary,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          if (_secondsRemaining > 0)
                            Text(
                              'يمكنك طلب رابط جديد بعد ${_formatTimer(_secondsRemaining)}',
                              textAlign: TextAlign.center,
                              style: const TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w700,
                                color: AppColors.yemenBlack,
                              ),
                            )
                          else
                            TextButton(
                              onPressed: _isResending
                                  ? null
                                  : () => _resendVerificationEmail(displayEmail),
                              style: TextButton.styleFrom(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 16,
                                  vertical: 6,
                                ),
                                minimumSize: Size.zero,
                                tapTargetSize:
                                    MaterialTapTargetSize.shrinkWrap,
                              ),
                              child: _isResending
                                  ? const SizedBox(
                                      width: 18,
                                      height: 18,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2.2,
                                        color: AppColors.yemenRed,
                                      ),
                                    )
                                  : const Text(
                                      'إعادة إرسال الرابط الآن',
                                      style: TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.w700,
                                        color: AppColors.yemenRed,
                                      ),
                                    ),
                            ),
                        ],
                      ),

                      const SizedBox(height: 28),

                      // Refresh Status Action Button
                      OutlinedButton.icon(
                        onPressed: _isCheckingStatus
                            ? null
                            : _checkVerificationStatus,
                        icon: _isCheckingStatus
                            ? const SizedBox(
                                width: 16,
                                height: 16,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: AppColors.yemenBlack,
                                ),
                              )
                            : const Icon(
                                Icons.refresh_rounded,
                                size: 18,
                                color: AppColors.yemenBlack,
                              ),
                        label: const Text(
                          'تحديث حالة التحقق',
                          style: TextStyle(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w700,
                            color: AppColors.yemenBlack,
                          ),
                        ),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          side: const BorderSide(
                            color: AppColors.borderSubtle,
                            width: 1.2,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      ),

                      const SizedBox(height: 12),

                      // Logout / Return to Login
                      Center(
                        child: TextButton(
                          onPressed: _handleLogoutOrBack,
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 6,
                            ),
                          ),
                          child: const Text(
                            'تسجيل الخروج والعودة',
                            style: TextStyle(
                              fontSize: 12.5,
                              fontWeight: FontWeight.w600,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
