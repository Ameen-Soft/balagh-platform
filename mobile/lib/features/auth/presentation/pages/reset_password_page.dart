import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/api_exceptions.dart';
import '../../../../core/widgets/app_error_banner.dart';
import '../../application/providers.dart';
import '../widgets/auth_corner_decoration.dart';
import '../widgets/auth_header.dart';
import '../widgets/auth_primary_button.dart';
import '../widgets/auth_text_field.dart';

/// Reset Password Screen matching the visual design and Yemen national branding:
/// - Top bar with Back Button
/// - Header with Illustration (assets/images/Reset-password_photo.jpg)
/// - Title: "إعادة تعيين كلمة المرور"
/// - Subtitle: "أدخل رمز التحقق وكلمة المرور الجديدة"
/// - Email, Verification Token, New Password, and Confirm Password fields
/// - Primary Red CTA: "حفظ كلمة المرور الجديدة"
/// - Return to Login link
/// - Bottom ribbon decoration (AuthCornerDecoration)
class ResetPasswordPage extends ConsumerStatefulWidget {
  final String? initialEmail;
  final String? initialToken;

  const ResetPasswordPage({
    super.key,
    this.initialEmail,
    this.initialToken,
  });

  @override
  ConsumerState<ResetPasswordPage> createState() => _ResetPasswordPageState();
}

class _ResetPasswordPageState extends ConsumerState<ResetPasswordPage> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _emailController;
  late final TextEditingController _tokenController;
  late final TextEditingController _passwordController;
  late final TextEditingController _confirmPasswordController;

  final FocusNode _emailFocusNode = FocusNode();
  final FocusNode _tokenFocusNode = FocusNode();
  final FocusNode _passwordFocusNode = FocusNode();
  final FocusNode _confirmPasswordFocusNode = FocusNode();

  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _emailController = TextEditingController(text: widget.initialEmail ?? '');
    _tokenController = TextEditingController(text: widget.initialToken ?? '');
    _passwordController = TextEditingController();
    _confirmPasswordController = TextEditingController();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _tokenController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();

    _emailFocusNode.dispose();
    _tokenFocusNode.dispose();
    _passwordFocusNode.dispose();
    _confirmPasswordFocusNode.dispose();
    super.dispose();
  }

  Future<void> _submitResetPassword() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    FocusScope.of(context).unfocus();

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final email = _emailController.text.trim();
      final token = _tokenController.text.trim();
      final password = _passwordController.text;
      final confirmPassword = _confirmPasswordController.text;

      await ref.read(authNotifierProvider.notifier).resetPassword(
            email: email,
            token: token,
            password: password,
            passwordConfirmation: confirmPassword,
          );

      if (!mounted) return;

      setState(() {
        _isLoading = false;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text(
            'تم إعادة تعيين كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول.',
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

      // Navigate to login
      context.go('/login');
    } on ValidationException catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = e.firstErrorMessage;
      });
      _showErrorSnackBar(_errorMessage!);
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = e.message;
      });
      _showErrorSnackBar(_errorMessage!);
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = 'حدث خطأ أثناء إعادة تعيين كلمة المرور. يرجى التحقق من صحة الرمز والمحاولة مجدداً.';
      });
      _showErrorSnackBar(_errorMessage!);
    }
  }

  void _showErrorSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          message,
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
                        ResetPasswordAuthHeader(onBack: _handleBack),

                        const SizedBox(height: 20),

                        // Error Banner if submission failed
                        if (_errorMessage != null) ...[
                          AppErrorBanner(
                            message: _errorMessage!,
                            onDismiss: () {
                              setState(() {
                                _errorMessage = null;
                              });
                            },
                          ),
                          const SizedBox(height: 16),
                        ],

                        // Email Field
                        AuthTextField(
                          controller: _emailController,
                          focusNode: _emailFocusNode,
                          hintText: 'البريد الإلكتروني',
                          leadingIcon: Icons.mail_outline_rounded,
                          keyboardType: TextInputType.emailAddress,
                          textInputAction: TextInputAction.next,
                          enabled: !_isLoading,
                          autofillHints: const [AutofillHints.email],
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

                        const SizedBox(height: 14),

                        // Token Field
                        AuthTextField(
                          controller: _tokenController,
                          focusNode: _tokenFocusNode,
                          hintText: 'رمز التحقق (Token)',
                          leadingIcon: Icons.vpn_key_outlined,
                          keyboardType: TextInputType.text,
                          textInputAction: TextInputAction.next,
                          enabled: !_isLoading,
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'يرجى إدخال رمز التحقق المستلم بالبريد الإلكتروني';
                            }
                            return null;
                          },
                        ),

                        const SizedBox(height: 14),

                        // New Password Field
                        AuthTextField(
                          controller: _passwordController,
                          focusNode: _passwordFocusNode,
                          hintText: 'كلمة المرور الجديدة',
                          leadingIcon: Icons.lock_outline_rounded,
                          isPassword: true,
                          textInputAction: TextInputAction.next,
                          enabled: !_isLoading,
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'يرجى إدخال كلمة المرور الجديدة';
                            }
                            if (value.length < 8) {
                              return 'كلمة المرور يجب أن لا تقل عن 8 أحرف';
                            }
                            return null;
                          },
                        ),

                        const SizedBox(height: 14),

                        // Confirm New Password Field
                        AuthTextField(
                          controller: _confirmPasswordController,
                          focusNode: _confirmPasswordFocusNode,
                          hintText: 'تأكيد كلمة المرور الجديدة',
                          leadingIcon: Icons.lock_outline_rounded,
                          isPassword: true,
                          textInputAction: TextInputAction.done,
                          enabled: !_isLoading,
                          onFieldSubmitted: (_) => _submitResetPassword(),
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'يرجى تأكيد كلمة المرور';
                            }
                            if (value != _passwordController.text) {
                              return 'كلمة المرور غير متطابقة';
                            }
                            return null;
                          },
                        ),

                        const SizedBox(height: 20),

                        // Primary Red CTA Button
                        AuthPrimaryButton(
                          text: 'حفظ كلمة المرور الجديدة',
                          onPressed: _isLoading ? null : _submitResetPassword,
                          isLoading: _isLoading,
                        ),

                        const SizedBox(height: 20),

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
                              'العودة لتسجيل الدخول',
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
