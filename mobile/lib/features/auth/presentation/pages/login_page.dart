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

    await ref.read(authNotifierProvider.notifier).login(
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
      
      final GoogleSignInAccount account = await GoogleSignIn.instance.authenticate(
        scopeHint: ['email', 'profile'],
      );

      final GoogleSignInAuthentication googleAuth = account.authentication;
      final String? idToken = googleAuth.idToken;

      if (idToken != null) {
        if (mounted) {
          await ref.read(authNotifierProvider.notifier).loginWithGoogle(idToken);
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('فشل الحصول على مصادقة جوجل.')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('حدث خطأ أثناء تسجيل الدخول: $e')),
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

    ref.listen(authNotifierProvider, (previous, next) {
      if (next.isError && next.errorMessage != null && previous?.errorMessage != next.errorMessage) {
        ScaffoldMessenger.of(context).hideCurrentSnackBar();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              next.errorMessage!,
              textAlign: TextAlign.right,
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
            ),
            backgroundColor: AppColors.yemenRed,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            margin: const EdgeInsets.all(16),
          ),
        );
      }
    });

    return Scaffold(
      backgroundColor: AppColors.background,
      body: Stack(
        children: [
          // Decorative Yemen Flag Corner Ribbon at bottom-left
          const Positioned(
            bottom: 0,
            left: 0,
            child: AuthCornerDecoration(),
          ),

          // Main Form Content
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                physics: const ClampingScrollPhysics(),
                keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 16.0),
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
                        if (authState.isError && authState.errorMessage != null) ...[
                          AppErrorBanner(
                            message: authState.errorMessage!,
                            onDismiss: () {
                              ref.read(authNotifierProvider.notifier).clearError();
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
                          autofillHints: const [AutofillHints.email, AutofillHints.username],
                          onChanged: (_) => _onFieldChanged(),
                          onFieldSubmitted: (_) {
                            FocusScope.of(context).requestFocus(_passwordFocusNode);
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
                            onPressed: () {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text(
                                    'خدمة استعادة كلمة المرور ستتوفر قريباً عبر البريد الإلكتروني',
                                    textAlign: TextAlign.right,
                                  ),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            },
                            style: TextButton.styleFrom(
                              padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
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
