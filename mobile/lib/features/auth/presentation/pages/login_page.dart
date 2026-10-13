import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_sign_in/google_sign_in.dart';

import '../../../../core/constants/app_colors.dart';
import '../../../../core/widgets/app_error_banner.dart';
import '../../application/providers.dart';
import '../widgets/auth_corner_decoration.dart';
import '../widgets/auth_google_button.dart';
import '../widgets/auth_header.dart';
import '../widgets/auth_primary_button.dart';
import '../widgets/auth_text_field.dart';

class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({super.key});

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _emailController;
  late final TextEditingController _passwordController;
  final FocusNode _emailFocusNode = FocusNode();
  final FocusNode _passwordFocusNode = FocusNode();

  @override
  void initState() {
    super.initState();
    _emailController = TextEditingController();
    _passwordController = TextEditingController();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _emailFocusNode.dispose();
    _passwordFocusNode.dispose();
    super.dispose();
  }

  Future<void> _submitLogin() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    FocusScope.of(context).unfocus();

    await ref
        .read(authNotifierProvider.notifier)
        .login(
          email: _emailController.text.trim(),
          password: _passwordController.text,
        );
  }

  Future<void> _submitGoogleLogin() async {
    FocusScope.of(context).unfocus();
    try {
      await GoogleSignIn.instance.initialize(
        serverClientId: '897056716364-qciejua1mroqo991l1gpp30l1jabno8g.apps.googleusercontent.com',
      );

      final GoogleSignInAccount account = await GoogleSignIn.instance
          .authenticate(scopeHint: ['email', 'profile']);

      final GoogleSignInAuthentication googleAuth = account.authentication;
      final String? idToken = googleAuth.idToken;

      if (idToken != null) {
        if (mounted) {
          await ref
              .read(authNotifierProvider.notifier)
              .loginWithGoogle(idToken);
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('فشل الحصول على مصادقة جوجل.')),
          );
        }
      }
    } on GoogleSignInException catch (e) {
      // Gracefully ignore user cancellation or dismissed reauth
      if (e.code == GoogleSignInExceptionCode.canceled ||
          e.description?.contains('Cancelled') == true ||
          e.description?.contains('canceled') == true) {
        return;
      }

      if (mounted) {
        final message = e.description?.contains('Account reauth failed') == true
            ? 'تعذر التحقق من حساب Google، يرجى المحاولة مرة أخرى أو اختيار حساب آخر.'
            : 'تعذر إتمام تسجيل الدخول عبر Google، يرجى المحاولة لاحقاً.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              message,
              textAlign: TextAlign.right,
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
            ),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
              'حدث خطأ غير متوقع أثناء تسجيل الدخول، يرجى المحاولة لاحقاً.',
              textAlign: TextAlign.right,
              style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
            ),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _onFieldChanged() {
    ref.read(authNotifierProvider.notifier).clearError();
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);



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

          // Main Form Content
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
                        const LoginAuthHeader(),

                        const SizedBox(height: 22),

                        // Error Banner if in error state
                        if (authState.isError &&
                            authState.errorMessage != null) ...[
                          AppErrorBanner(
                            message: authState.errorMessage!,
                            onDismiss: () {
                              ref
                                  .read(authNotifierProvider.notifier)
                                  .clearError();
                            },
                          ),
                          const SizedBox(height: 14),
                        ],

                        // Email Field
                        AuthTextField(
                          controller: _emailController,
                          focusNode: _emailFocusNode,
                          hintText: 'البريد الإلكتروني',
                          leadingIcon: Icons.mail_outline_rounded,
                          keyboardType: TextInputType.emailAddress,
                          textInputAction: TextInputAction.next,
                          enabled: !authState.isLoading,
                          autofillHints: const [
                            AutofillHints.email,
                            AutofillHints.username,
                          ],
                          onChanged: (_) => _onFieldChanged(),
                          onFieldSubmitted: (_) {
                            FocusScope.of(context)
                                .requestFocus(_passwordFocusNode);
                          },
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

                        const SizedBox(height: 12),

                        // Password Field
                        AuthTextField(
                          controller: _passwordController,
                          focusNode: _passwordFocusNode,
                          hintText: 'كلمة المرور',
                          leadingIcon: Icons.lock_outline_rounded,
                          isPassword: true,
                          textInputAction: TextInputAction.done,
                          enabled: !authState.isLoading,
                          autofillHints: const [AutofillHints.password],
                          onChanged: (_) => _onFieldChanged(),
                          onFieldSubmitted: (_) => _submitLogin(),
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'يرجى إدخال كلمة المرور';
                            }
                            if (value.length < 6) {
                              return 'كلمة المرور يجب ألا تقل عن 6 أحرف';
                            }
                            return null;
                          },
                        ),

                        const SizedBox(height: 4),

                        // Forgot Password Link
                        Align(
                          alignment: Alignment.centerRight,
                          child: TextButton(
                            onPressed: authState.isLoading
                                ? null
                                : () => context.push('/forgot-password'),
                            style: TextButton.styleFrom(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 4,
                                vertical: 4,
                              ),
                              minimumSize: Size.zero,
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                            ),
                            child: const Text(
                              'نسيت كلمة المرور؟',
                              style: TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w700,
                                color: AppColors.yemenRed,
                              ),
                            ),
                          ),
                        ),

                        const SizedBox(height: 16),

                        // Primary Red CTA Button
                        AuthPrimaryButton(
                          text: 'تسجيل الدخول',
                          onPressed: _submitLogin,
                          isLoading: authState.isLoading,
                        ),

                        const SizedBox(height: 12),

                        // Google Sign-In Button
                        AuthGoogleButton(
                          text: 'تسجيل الدخول باستخدام Google',
                          onPressed: _submitGoogleLogin,
                          isLoading: authState.isLoading,
                        ),

                        const SizedBox(height: 20),

                        // Register Footer Link
                        Wrap(
                          alignment: WrapAlignment.center,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            const Text(
                              'ليس لديك حساب في بادر؟',
                              style: TextStyle(
                                fontSize: 13,
                                color: AppColors.textSecondary,
                              ),
                            ),
                            const SizedBox(width: 4),
                            GestureDetector(
                              onTap: authState.isLoading
                                  ? null
                                  : () => context.push('/register'),
                              child: const Text(
                                'إنشاء حساب جديد',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.yemenRed,
                                ),
                              ),
                            ),
                          ],
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
