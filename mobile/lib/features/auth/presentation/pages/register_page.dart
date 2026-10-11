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

class RegisterPage extends ConsumerStatefulWidget {
  const RegisterPage({super.key});

  @override
  ConsumerState<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends ConsumerState<RegisterPage> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameController;
  late final TextEditingController _emailController;
  late final TextEditingController _passwordController;
  late final TextEditingController _passwordConfirmationController;

  final FocusNode _nameFocusNode = FocusNode();
  final FocusNode _emailFocusNode = FocusNode();
  final FocusNode _passwordFocusNode = FocusNode();
  final FocusNode _confirmPasswordFocusNode = FocusNode();

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController();
    _emailController = TextEditingController();
    _passwordController = TextEditingController();
    _passwordConfirmationController = TextEditingController();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _passwordConfirmationController.dispose();
    _nameFocusNode.dispose();
    _emailFocusNode.dispose();
    _passwordFocusNode.dispose();
    _confirmPasswordFocusNode.dispose();
    super.dispose();
  }

  Future<void> _submitRegister() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    FocusScope.of(context).unfocus();

    await ref.read(authNotifierProvider.notifier).register(
          name: _nameController.text.trim(),
          email: _emailController.text.trim(),
          password: _passwordController.text,
          passwordConfirmation: _passwordConfirmationController.text,
          phone: null,
          nationalId: null,
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

  void _handleBack() {
    if (context.canPop()) {
      context.pop();
    } else {
      context.go('/login');
    }
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

          // Main Scrollable Content
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                physics: const ClampingScrollPhysics(),
                keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 12.0),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 420),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Header with Back Arrow + Titles + Illustration
                        RegisterAuthHeader(onBack: _handleBack),

                        const SizedBox(height: 18),

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

                        // Field 1: Full Name
                        AuthTextField(
                          controller: _nameController,
                          focusNode: _nameFocusNode,
                          hintText: 'الاسم الكامل',
                          leadingIcon: Icons.person_outline_rounded,
                          keyboardType: TextInputType.name,
                          textInputAction: TextInputAction.next,
                          enabled: !authState.isLoading,
                          autofillHints: const [AutofillHints.name],
                          onChanged: (_) => _onFieldChanged(),
                          onFieldSubmitted: (_) {
                            FocusScope.of(context).requestFocus(_emailFocusNode);
                          },
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'يرجى إدخال الاسم الكامل';
                            }
                            if (value.trim().length < 3) {
                              return 'الاسم يجب ألا يقل عن 3 أحرف';
                            }
                            return null;
                          },
                        ),

                        const SizedBox(height: 12),

                        // Field 2: Email
                        AuthTextField(
                          controller: _emailController,
                          focusNode: _emailFocusNode,
                          hintText: 'البريد الإلكتروني',
                          leadingIcon: Icons.mail_outline_rounded,
                          keyboardType: TextInputType.emailAddress,
                          textInputAction: TextInputAction.next,
                          enabled: !authState.isLoading,
                          autofillHints: const [AutofillHints.email],
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

                        // Field 3: Password
                        AuthTextField(
                          controller: _passwordController,
                          focusNode: _passwordFocusNode,
                          hintText: 'كلمة المرور',
                          leadingIcon: Icons.lock_outline_rounded,
                          isPassword: true,
                          textInputAction: TextInputAction.next,
                          enabled: !authState.isLoading,
                          autofillHints: const [AutofillHints.newPassword],
                          onChanged: (_) => _onFieldChanged(),
                          onFieldSubmitted: (_) {
                            FocusScope.of(context).requestFocus(_confirmPasswordFocusNode);
                          },
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

                        const SizedBox(height: 12),

                        // Field 4: Confirm Password
                        AuthTextField(
                          controller: _passwordConfirmationController,
                          focusNode: _confirmPasswordFocusNode,
                          hintText: 'تأكيد كلمة المرور',
                          leadingIcon: Icons.lock_outline_rounded,
                          isPassword: true,
                          textInputAction: TextInputAction.done,
                          enabled: !authState.isLoading,
                          autofillHints: const [AutofillHints.newPassword],
                          onChanged: (_) => _onFieldChanged(),
                          onFieldSubmitted: (_) => _submitRegister(),
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'يرجى تأكيد كلمة المرور';
                            }
                            if (value != _passwordController.text) {
                              return 'كلمتا المرور غير متطابقتين';
                            }
                            return null;
                          },
                        ),

                        const SizedBox(height: 18),

                        // Primary Red CTA Button: "إنشاء حساب ->"
                        AuthPrimaryButton(
                          text: 'إنشاء حساب',
                          onPressed: _submitRegister,
                          isLoading: authState.isLoading,
                        ),

                        const SizedBox(height: 12),

                        // Google Sign-In Button
                        AuthGoogleButton(
                          text: 'إنشاء حساب باستخدام Google',
                          onPressed: _submitGoogleLogin,
                          isLoading: authState.isLoading,
                        ),

                        const SizedBox(height: 18),

                        // Terms & Privacy Notice
                        Center(
                          child: Wrap(
                            alignment: WrapAlignment.center,
                            crossAxisAlignment: WrapCrossAlignment.center,
                            children: const [
                              Text(
                                'بالتسجيل، فإنك توافق على ',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: AppColors.textSecondary,
                                ),
                              ),
                              Text(
                                'شروط الاستخدام وسياسة الخصوصية',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.yemenRed,
                                ),
                              ),
                            ],
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
