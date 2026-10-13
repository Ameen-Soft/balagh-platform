import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/constants/app_colors.dart';
import '../../application/providers.dart';
import '../widgets/auth_corner_decoration.dart';
import '../widgets/auth_header.dart';
import '../widgets/auth_primary_button.dart';
import '../widgets/auth_text_field.dart';

/// Forgot Password screen matching the visual mockup:
/// - Top bar with Back Button
/// - Illustration (assets/images/Reset-password_photo.jpg)
/// - Title: "نسيت كلمة المرور؟"
/// - Instructional copy
/// - Email input field with validation
/// - Red primary button: "إرسال رابط إعادة تعيين كلمة المرور ->"
/// - Navigation link to return to Login
/// - Bottom ribbon decoration (AuthCornerDecoration)
class ForgotPasswordPage extends ConsumerStatefulWidget {
  const ForgotPasswordPage({super.key});

  @override
  ConsumerState<ForgotPasswordPage> createState() => _ForgotPasswordPageState();
}

class _ForgotPasswordPageState extends ConsumerState<ForgotPasswordPage> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _emailController;
  final FocusNode _emailFocusNode = FocusNode();

  bool _isLoading = false;
  bool _isSuccess = false;
  String? _successMessage;

  @override
  void initState() {
    super.initState();
    _emailController = TextEditingController();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _emailFocusNode.dispose();
    super.dispose();
  }

  Future<void> _submitForgotPassword() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    FocusScope.of(context).unfocus();

    setState(() {
      _isLoading = true;
      _isSuccess = false;
    });

    try {
      final email = _emailController.text.trim();
      await ref.read(authNotifierProvider.notifier).forgotPassword(email);

      if (!mounted) return;

      setState(() {
        _isLoading = false;
        _isSuccess = true;
        _successMessage =
            'إذا كان هذا البريد مسجلاً لدينا، فستصلك رسالة تحتوي على تعليمات إعادة تعيين كلمة المرور.';
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            _successMessage!,
            textAlign: TextAlign.right,
            style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
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
      setState(() {
        _isLoading = false;
      });

      final isRateLimit = e.toString().contains('429');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            isRateLimit
                ? 'يرجى الانتظار دقيقة قبل محاولة إرسال رابط جديد.'
                : 'حدث خطأ أثناء إرسال الرابط، يرجى المحاولة لاحقاً.',
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
    }
  }

  void _handleBack() {
    if (context.canPop()) {
      context.pop();
    } else {
      context.go('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
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

          // Main Scrollable Content
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                physics: const ClampingScrollPhysics(),
                keyboardDismissBehavior:
                    ScrollViewKeyboardDismissBehavior.onDrag,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24.0,
                  vertical: 16.0,
                ),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 420),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Header with Illustration + Titles
                        ForgotPasswordAuthHeader(onBack: _handleBack),

                        const SizedBox(height: 22),

                        // Success Feedback Banner if link was sent
                        if (_isSuccess && _successMessage != null) ...[
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: AppColors.yemenEmeraldLight,
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                color: AppColors.yemenEmerald.withValues(alpha: 0.3),
                              ),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Icon(
                                      Icons.check_circle_rounded,
                                      color: AppColors.yemenEmerald,
                                      size: 20,
                                    ),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Text(
                                        _successMessage!,
                                        style: const TextStyle(
                                          fontSize: 12.5,
                                          fontWeight: FontWeight.w600,
                                          color: AppColors.yemenBlack,
                                          height: 1.4,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 10),
                                const Text(
                                  'تم إرسال رمز التحقق ورابط الاستعادة إلى بريدك الإلكتروني. اضغط على الزر أدناه لإدخال الرمز وكلمة المرور الجديدة:',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: AppColors.textSecondary,
                                    height: 1.4,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),
                        ],

                        // Email Field (matching mockup)
                        AuthTextField(
                          controller: _emailController,
                          focusNode: _emailFocusNode,
                          hintText: 'البريد الإلكتروني',
                          leadingIcon: Icons.mail_outline_rounded,
                          keyboardType: TextInputType.emailAddress,
                          textInputAction: TextInputAction.done,
                          enabled: !_isLoading,
                          autofillHints: const [AutofillHints.email],
                          onFieldSubmitted: (_) => _submitForgotPassword(),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'يرجى إدخال البريد الإلكتروني';
                            }
                            final emailRegex = RegExp(r'^[^@]+@[^@]+\.[^@]+');
                            if (!emailRegex.hasMatch(value.trim())) {
                              return 'صيغة البريد الإلكتروني غير صحيحة';
                            }
                            return null;
                          },
                        ),

                        const SizedBox(height: 18),

                        if (_isSuccess) ...[
                          // Primary CTA to go directly to reset password page
                          AuthPrimaryButton(
                            text: 'إدخال رمز التحقق وتعيين كلمة المرور',
                            onPressed: () {
                              final email = _emailController.text.trim();
                              context.push(
                                '/reset-password?email=${Uri.encodeComponent(email)}',
                              );
                            },
                          ),
                          const SizedBox(height: 12),
                          // Secondary option to resend if needed
                          Center(
                            child: TextButton.icon(
                              onPressed: _isLoading ? null : _submitForgotPassword,
                              icon: const Icon(
                                Icons.refresh_rounded,
                                size: 18,
                                color: AppColors.textSecondary,
                              ),
                              label: const Text(
                                'إعادة إرسال الرابط',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.textSecondary,
                                ),
                              ),
                            ),
                          ),
                        ] else ...[
                          // Primary Red CTA Button to request reset email
                          AuthPrimaryButton(
                            text: 'إرسال رابط إعادة تعيين كلمة المرور',
                            onPressed: _isLoading ? null : _submitForgotPassword,
                            isLoading: _isLoading,
                          ),
                          const SizedBox(height: 12),
                          // Shortcut if the user already has a token
                          Center(
                            child: TextButton(
                              onPressed: () {
                                final email = _emailController.text.trim();
                                context.push(
                                  '/reset-password?email=${Uri.encodeComponent(email)}',
                                );
                              },
                              child: const Text(
                                'لديك رمز تحقق بالفعل؟ إعادة تعيين كلمة المرور',
                                style: TextStyle(
                                  fontSize: 12.5,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.yemenRed,
                                ),
                              ),
                            ),
                          ),
                        ],

                        const SizedBox(height: 16),

                        // Return to Login Link
                        Center(
                          child: TextButton(
                            onPressed: _isLoading
                                ? null
                                : () {
                                    if (context.canPop()) {
                                      context.pop();
                                    } else {
                                      context.go('/login');
                                    }
                                  },
                            style: TextButton.styleFrom(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 6,
                              ),
                            ),
                            child: const Text(
                              'تذكرت كلمة المرور؟ العودة لتسجيل الدخول',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w700,
                                color: AppColors.yemenBlack,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 10),
                      ],
                    ),
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
