import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:mobile/core/constants/app_assets.dart';
import 'package:mobile/core/constants/app_colors.dart';
import 'package:mobile/core/widgets/app_button.dart';
import 'package:mobile/core/widgets/app_error_banner.dart';
import 'package:mobile/core/widgets/app_text_field.dart';
import 'package:mobile/features/auth/application/providers.dart';

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
  bool _obscurePassword = true;

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
    final authNotifier = ref.read(authNotifierProvider.notifier);
    authNotifier.clearError();
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);

    // Also surface floating notification if needed
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
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            physics: const ClampingScrollPhysics(),
            keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
            padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 20.0),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 440),
              child: Form(
                key: _formKey,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const SizedBox(height: 8),

                    // --- Brand Identity Header ---
                    Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          // Emblem Badge
                          Container(
                            width: 64,
                            height: 64,
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(color: AppColors.borderSubtle, width: 1.2),
                              boxShadow: const [
                                BoxShadow(
                                  color: AppColors.shadowColor,
                                  blurRadius: 16,
                                  offset: Offset(0, 4),
                                ),
                              ],
                            ),
                            child: Image.asset(
                              AppAssets.logoOnly,
                              fit: BoxFit.contain,
                              errorBuilder: (context, error, stackTrace) => const Icon(
                                Icons.account_balance_rounded,
                                color: AppColors.yemenRed,
                                size: 30,
                              ),
                            ),
                          ),
                          const SizedBox(height: 12),
                          const Text(
                            'بــــادر',
                            style: TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.w900,
                              color: AppColors.yemenBlack,
                              letterSpacing: -0.5,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: AppColors.yemenRedLight,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: const Text(
                              'المنصة الوطنية للمشاريع والشكاوى',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: AppColors.yemenRed,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 28),

                    // --- Welcome Title & Subtitle ---
                    const Text(
                      'تسجيل الدخول',
                      textAlign: TextAlign.start,
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w800,
                        color: AppColors.yemenBlack,
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'أهلاً بك مجدداً، سجّل الدخول لمتابعة بلاغاتك والمشاريع المجتمعية.',
                      textAlign: TextAlign.start,
                      style: TextStyle(
                        fontSize: 13.5,
                        color: AppColors.textSecondary,
                        height: 1.4,
                      ),
                    ),

                    const SizedBox(height: 20),

                    // --- In-page Error Banner ---
                    if (authState.isError && authState.errorMessage != null) ...[
                      AppErrorBanner(
                        message: authState.errorMessage!,
                        onDismiss: () {
                          ref.read(authNotifierProvider.notifier).clearError();
                        },
                      ),
                      const SizedBox(height: 16),
                    ],

                    // --- Email Field ---
                    AppTextField(
                      controller: _emailController,
                      label: 'البريد الإلكتروني',
                      hint: 'name@example.com',
                      prefixIcon: Icons.alternate_email_rounded,
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

                    const SizedBox(height: 16),

                    // --- Password Field ---
                    AppTextField(
                      controller: _passwordController,
                      label: 'كلمة المرور',
                      hint: '••••••••',
                      prefixIcon: Icons.lock_outline_rounded,
                      obscureText: _obscurePassword,
                      textInputAction: TextInputAction.done,
                      enabled: !authState.isLoading,
                      autofillHints: const [AutofillHints.password],
                      onChanged: (_) => _onFieldChanged(),
                      onFieldSubmitted: (_) => _submitLogin(),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscurePassword
                              ? Icons.visibility_off_outlined
                              : Icons.visibility_outlined,
                          color: AppColors.textSecondary,
                          size: 20,
                        ),
                        tooltip: _obscurePassword ? 'إظهار كلمة المرور' : 'إخفاء كلمة المرور',
                        onPressed: () {
                          setState(() {
                            _obscurePassword = !_obscurePassword;
                          });
                        },
                      ),
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

                    const SizedBox(height: 24),

                    // --- Submit CTA Button ---
                    ListenableBuilder(
                      listenable: Listenable.merge([_emailController, _passwordController]),
                      builder: (context, _) {
                        final isFormFilled = _emailController.text.trim().isNotEmpty &&
                            _passwordController.text.isNotEmpty;

                        return AppButton(
                          text: 'تسجيل الدخول',
                          onPressed: _submitLogin,
                          isLoading: authState.isLoading,
                          isDisabled: !isFormFilled,
                          height: 52,
                        );
                      },
                    ),

                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(child: Divider(color: AppColors.borderSubtle)),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          child: Text('أو', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                        ),
                        Expanded(child: Divider(color: AppColors.borderSubtle)),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // --- Google Sign-In Button ---
                    OutlinedButton.icon(
                      onPressed: authState.isLoading ? null : _submitGoogleLogin,
                      icon: const Icon(
                        Icons.g_mobiledata_rounded,
                        size: 28,
                        color: AppColors.yemenRed,
                      ),
                      label: const Text(
                        'تسجيل الدخول باستخدام جوجل',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.yemenBlack,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        side: BorderSide(color: AppColors.borderSubtle),
                      ),
                    ),

                    const SizedBox(height: 20),

                    // --- Register Link Footer ---
                    Wrap(
                      alignment: WrapAlignment.center,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        const Text(
                          'ليس لديك حساب في بادر؟',
                          style: TextStyle(
                            fontSize: 13.5,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(width: 4),
                        TextButton(
                          onPressed: authState.isLoading
                              ? null
                              : () => context.push('/register'),
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                          child: const Text(
                            'إنشاء حساب جديد',
                            style: TextStyle(
                              fontSize: 13.5,
                              fontWeight: FontWeight.w800,
                              color: AppColors.yemenRed,
                            ),
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 12),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
