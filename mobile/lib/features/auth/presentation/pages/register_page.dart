import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:mobile/core/constants/app_colors.dart';
import 'package:mobile/core/widgets/app_button.dart';
import 'package:mobile/core/widgets/app_error_banner.dart';
import 'package:mobile/core/widgets/app_text_field.dart';
import 'package:mobile/features/auth/application/providers.dart';

class RegisterPage extends ConsumerStatefulWidget {
  const RegisterPage({super.key});

  @override
  ConsumerState<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends ConsumerState<RegisterPage> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameController;
  late final TextEditingController _emailController;
  late final TextEditingController _phoneController;
  late final TextEditingController _nationalIdController;
  late final TextEditingController _passwordController;
  late final TextEditingController _passwordConfirmationController;

  final FocusNode _nameFocusNode = FocusNode();
  final FocusNode _emailFocusNode = FocusNode();
  final FocusNode _phoneFocusNode = FocusNode();
  final FocusNode _nationalIdFocusNode = FocusNode();
  final FocusNode _passwordFocusNode = FocusNode();
  final FocusNode _confirmPasswordFocusNode = FocusNode();

  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController();
    _emailController = TextEditingController();
    _phoneController = TextEditingController();
    _nationalIdController = TextEditingController();
    _passwordController = TextEditingController();
    _passwordConfirmationController = TextEditingController();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _nationalIdController.dispose();
    _passwordController.dispose();
    _passwordConfirmationController.dispose();
    _nameFocusNode.dispose();
    _emailFocusNode.dispose();
    _phoneFocusNode.dispose();
    _nationalIdFocusNode.dispose();
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
          phone: _phoneController.text.trim().isEmpty
              ? null
              : _phoneController.text.trim(),
          nationalId: _nationalIdController.text.trim().isEmpty
              ? null
              : _nationalIdController.text.trim(),
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
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back_ios_new_rounded,
            color: AppColors.yemenBlack,
            size: 20,
          ),
          tooltip: 'رجوع',
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/login');
            }
          },
        ),
        centerTitle: true,
        title: const Text(
          'إنشاء حساب مواطن',
          style: TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w800,
            color: AppColors.yemenBlack,
          ),
        ),
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            physics: const ClampingScrollPhysics(),
            keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
            padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 16.0),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 460),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Subtitle Intro
                    const Text(
                      'انضم إلى منصة بادر',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w800,
                        color: AppColors.yemenBlack,
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'سجّل بياناتك لتتمكن من تقديم البلاغات ومتابعة تنفيذها مباشرة.',
                      style: TextStyle(
                        fontSize: 13,
                        color: AppColors.textSecondary,
                        height: 1.4,
                      ),
                    ),

                    const SizedBox(height: 18),

                    // Inline Error Banner
                    if (authState.isError && authState.errorMessage != null) ...[
                      AppErrorBanner(
                        message: authState.errorMessage!,
                        onDismiss: () {
                          ref.read(authNotifierProvider.notifier).clearError();
                        },
                      ),
                      const SizedBox(height: 16),
                    ],

                    // ==========================================
                    // Group 1: المعلومات الأساسية (Required)
                    // ==========================================
                    _buildSectionContainer(
                      title: 'المعلومات الشخصية',
                      subtitle: 'الاسم والبريد لتسجيل الدخول واستلام الإشعارات',
                      icon: Icons.person_outline_rounded,
                      children: [
                        AppTextField(
                          controller: _nameController,
                          label: 'الاسم الكامل',
                          hint: 'مثال: محمد أحمد علي',
                          prefixIcon: Icons.person_outline_rounded,
                          isRequired: true,
                          enabled: !authState.isLoading,
                          textInputAction: TextInputAction.next,
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
                        const SizedBox(height: 14),
                        AppTextField(
                          controller: _emailController,
                          label: 'البريد الإلكتروني',
                          hint: 'name@example.com',
                          prefixIcon: Icons.alternate_email_rounded,
                          isRequired: true,
                          keyboardType: TextInputType.emailAddress,
                          textInputAction: TextInputAction.next,
                          enabled: !authState.isLoading,
                          autofillHints: const [AutofillHints.email],
                          onChanged: (_) => _onFieldChanged(),
                          onFieldSubmitted: (_) {
                            FocusScope.of(context).requestFocus(_phoneFocusNode);
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
                      ],
                    ),

                    const SizedBox(height: 16),

                    // ==========================================
                    // Group 2: بيانات التواصل والتحقق (Optional)
                    // ==========================================
                    _buildSectionContainer(
                      title: 'بيانات التحقق والتواصل',
                      subtitle: 'تساعد في التحقق من صحة البلاغ وسرعة الوصول إليك',
                      icon: Icons.contact_phone_outlined,
                      badgeText: 'اختياري',
                      badgeColor: const Color(0xFFF3F4F6),
                      badgeTextColor: AppColors.textSecondary,
                      children: [
                        AppTextField(
                          controller: _phoneController,
                          label: 'رقم الهاتف',
                          hint: 'مثال: 770000000',
                          prefixIcon: Icons.phone_outlined,
                          keyboardType: TextInputType.phone,
                          textInputAction: TextInputAction.next,
                          enabled: !authState.isLoading,
                          autofillHints: const [AutofillHints.telephoneNumber],
                          onChanged: (_) => _onFieldChanged(),
                          onFieldSubmitted: (_) {
                            FocusScope.of(context).requestFocus(_nationalIdFocusNode);
                          },
                          validator: (value) {
                            if (value != null && value.trim().isNotEmpty) {
                              if (value.trim().length < 8) {
                                return 'رقم الهاتف غير مكتمل';
                              }
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),
                        AppTextField(
                          controller: _nationalIdController,
                          label: 'رقم الهوية الوطنية',
                          hint: 'مثال: 01010000000',
                          prefixIcon: Icons.badge_outlined,
                          keyboardType: TextInputType.number,
                          textInputAction: TextInputAction.next,
                          enabled: !authState.isLoading,
                          onChanged: (_) => _onFieldChanged(),
                          onFieldSubmitted: (_) {
                            FocusScope.of(context).requestFocus(_passwordFocusNode);
                          },
                          validator: (value) {
                            if (value != null && value.trim().isNotEmpty) {
                              if (value.trim().length < 6) {
                                return 'رقم الهوية غير صالح';
                              }
                            }
                            return null;
                          },
                        ),
                      ],
                    ),

                    const SizedBox(height: 16),

                    // ==========================================
                    // Group 3: أمان الحساب وكلمة المرور (Required)
                    // ==========================================
                    _buildSectionContainer(
                      title: 'أمان الحساب',
                      subtitle: 'اختر كلمة مرور قوية لحماية حسابك',
                      icon: Icons.shield_outlined,
                      children: [
                        AppTextField(
                          controller: _passwordController,
                          label: 'كلمة المرور',
                          hint: '8 أحرف أو أكثر',
                          prefixIcon: Icons.lock_outline_rounded,
                          isRequired: true,
                          obscureText: _obscurePassword,
                          textInputAction: TextInputAction.next,
                          enabled: !authState.isLoading,
                          autofillHints: const [AutofillHints.newPassword],
                          onChanged: (_) {
                            _onFieldChanged();
                            setState(() {});
                          },
                          onFieldSubmitted: (_) {
                            FocusScope.of(context).requestFocus(_confirmPasswordFocusNode);
                          },
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
                            if (value.length < 8) {
                              return 'كلمة المرور يجب ألا تقل عن 8 أحرف';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),
                        AppTextField(
                          controller: _passwordConfirmationController,
                          label: 'تأكيد كلمة المرور',
                          hint: 'أعد كتابة كلمة المرور',
                          prefixIcon: Icons.lock_reset_rounded,
                          isRequired: true,
                          obscureText: _obscureConfirmPassword,
                          textInputAction: TextInputAction.done,
                          enabled: !authState.isLoading,
                          autofillHints: const [AutofillHints.newPassword],
                          onChanged: (_) {
                            _onFieldChanged();
                            setState(() {});
                          },
                          onFieldSubmitted: (_) => _submitRegister(),
                          suffixIcon: IconButton(
                            icon: Icon(
                              _obscureConfirmPassword
                                  ? Icons.visibility_off_outlined
                                  : Icons.visibility_outlined,
                              color: AppColors.textSecondary,
                              size: 20,
                            ),
                            tooltip: _obscureConfirmPassword ? 'إظهار كلمة المرور' : 'إخفاء كلمة المرور',
                            onPressed: () {
                              setState(() {
                                _obscureConfirmPassword = !_obscureConfirmPassword;
                              });
                            },
                          ),
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

                        // Real-time password confirmation feedback indicator
                        if (_passwordController.text.isNotEmpty &&
                            _passwordConfirmationController.text.isNotEmpty) ...[
                          const SizedBox(height: 8),
                          Builder(
                            builder: (context) {
                              final isMatch = _passwordController.text ==
                                  _passwordConfirmationController.text;
                              final isLengthValid = _passwordController.text.length >= 8;

                              if (isMatch && isLengthValid) {
                                return Row(
                                  children: const [
                                    Icon(
                                      Icons.check_circle_rounded,
                                      size: 15,
                                      color: AppColors.yemenEmerald,
                                    ),
                                    SizedBox(width: 6),
                                    Text(
                                      'كلمتا المرور متطابقتان وجاهزتان',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                        color: AppColors.yemenEmerald,
                                      ),
                                    ),
                                  ],
                                );
                              } else if (!isMatch) {
                                return Row(
                                  children: const [
                                    Icon(
                                      Icons.info_outline_rounded,
                                      size: 15,
                                      color: AppColors.yemenGold,
                                    ),
                                    SizedBox(width: 6),
                                    Text(
                                      'كلمتا المرور غير متطابقتين بعد',
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: AppColors.yemenGold,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                );
                              }
                              return const SizedBox.shrink();
                            },
                          ),
                        ],
                      ],
                    ),

                    const SizedBox(height: 24),

                    // Submit CTA Button with Disabled State validation
                    ListenableBuilder(
                      listenable: Listenable.merge([
                        _nameController,
                        _emailController,
                        _passwordController,
                        _passwordConfirmationController,
                      ]),
                      builder: (context, _) {
                        final isRequiredFilled = _nameController.text.trim().isNotEmpty &&
                            _emailController.text.trim().isNotEmpty &&
                            _passwordController.text.isNotEmpty &&
                            _passwordConfirmationController.text.isNotEmpty;

                        final isPasswordMatch = _passwordController.text ==
                            _passwordConfirmationController.text;

                        final isFormReady = isRequiredFilled && isPasswordMatch;

                        return AppButton(
                          text: 'إنشاء الحساب',
                          onPressed: _submitRegister,
                          isLoading: authState.isLoading,
                          isDisabled: !isFormReady,
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
                        'إنشاء حساب باستخدام جوجل',
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

                    const SizedBox(height: 18),

                    // Login Link Footer
                    Wrap(
                      alignment: WrapAlignment.center,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        const Text(
                          'لديك حساب بالفعل؟',
                          style: TextStyle(
                            fontSize: 13.5,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(width: 4),
                        TextButton(
                          onPressed: authState.isLoading
                              ? null
                              : () {
                                  if (context.canPop()) {
                                    context.pop();
                                  } else {
                                    context.go('/login');
                                  }
                                },
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                          child: const Text(
                            'تسجيل الدخول',
                            style: TextStyle(
                              fontSize: 13.5,
                              fontWeight: FontWeight.w800,
                              color: AppColors.yemenRed,
                            ),
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 16),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSectionContainer({
    required String title,
    required String subtitle,
    required IconData icon,
    String? badgeText,
    Color? badgeColor,
    Color? badgeTextColor,
    required List<Widget> children,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle, width: 1.0),
        boxShadow: const [
          BoxShadow(
            color: AppColors.shadowColor,
            blurRadius: 8,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: AppColors.yemenRedLight,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(
                  icon,
                  size: 16,
                  color: AppColors.yemenRed,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.w700,
                    color: AppColors.yemenBlack,
                  ),
                ),
              ),
              if (badgeText != null)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                  decoration: BoxDecoration(
                    color: badgeColor ?? const Color(0xFFF3F4F6),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    badgeText,
                    style: TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w600,
                      color: badgeTextColor ?? AppColors.textSecondary,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: const TextStyle(
              fontSize: 11.5,
              color: AppColors.textSecondary,
            ),
          ),
          const Divider(height: 20, color: AppColors.borderSubtle),
          ...children,
        ],
      ),
    );
  }
}
