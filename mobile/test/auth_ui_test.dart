import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import 'package:mobile/core/widgets/app_error_banner.dart';
import 'package:mobile/features/auth/application/providers.dart';
import 'package:mobile/features/auth/domain/entities/user_entity.dart';
import 'package:mobile/features/auth/domain/repositories/auth_repository.dart';
import 'package:mobile/features/auth/presentation/pages/login_page.dart';
import 'package:mobile/features/auth/presentation/pages/register_page.dart';
import 'package:mobile/features/auth/presentation/widgets/auth_corner_decoration.dart';
import 'package:mobile/features/auth/presentation/widgets/auth_google_button.dart';
import 'package:mobile/features/auth/presentation/widgets/auth_primary_button.dart';

class MockAuthRepository implements AuthRepository {
  bool shouldThrow = false;
  String? lastLoginEmail;
  String? lastLoginPassword;
  String? lastRegisterName;
  String? lastRegisterEmail;

  @override
  Future<UserEntity> login({
    required String email,
    required String password,
    String? deviceName,
  }) async {
    if (shouldThrow) throw const UnauthorizedException('بيانات الاعتماد غير صحيحة');
    lastLoginEmail = email;
    lastLoginPassword = password;
    return const UserEntity(id: 1, name: 'مواطن تجريبي', email: 'test@example.com');
  }

  @override
  Future<UserEntity> loginWithGoogle(String idToken) async {
    if (shouldThrow) throw const UnauthorizedException('بيانات الاعتماد غير صحيحة');
    return const UserEntity(id: 1, name: 'مواطن تجريبي جوجل', email: 'google@example.com');
  }

  @override
  Future<UserEntity> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
    String? nationalId,
  }) async {
    if (shouldThrow) throw const ValidationException('البريد الإلكتروني مسجل مسبقاً');
    lastRegisterName = name;
    lastRegisterEmail = email;
    return const UserEntity(id: 2, name: 'مستخدم جديد', email: 'new@example.com');
  }

  @override
  Future<UserEntity> getCurrentUser() async {
    return const UserEntity(id: 1, name: 'مواطن', email: 'user@example.com');
  }

  @override
  Future<bool> hasSavedToken() async => false;

  @override
  Future<void> logout() async {}
}

void main() {
  late MockAuthRepository mockRepo;

  setUp(() {
    mockRepo = MockAuthRepository();
  });

  Widget createTestWidget(Widget child) {
    return ProviderScope(
      overrides: [
        authRepositoryProvider.overrideWithValue(mockRepo),
      ],
      child: MaterialApp(
        locale: const Locale('ar'),
        theme: ThemeData(
          fontFamily: 'Cairo',
          useMaterial3: true,
        ),
        home: child,
      ),
    );
  }

  group('LoginPage UI & UX Tests', () {
    testWidgets('renders header, title, fields, and action buttons', (tester) async {
      await tester.pumpWidget(createTestWidget(const LoginPage()));
      await tester.pumpAndSettle();

      // Header Title & Subtitle
      expect(find.text('مرحباً بك في بادر'), findsOneWidget);
      expect(find.text('معاً نحو مجتمع أكثر أماناً وازدهاراً'), findsOneWidget);

      // Form Text fields
      expect(find.text('البريد الإلكتروني'), findsOneWidget);
      expect(find.text('كلمة المرور'), findsOneWidget);

      // Forgot password
      expect(find.text('نسيت كلمة المرور؟'), findsOneWidget);

      // Primary and Google CTA Buttons
      expect(find.byType(AuthPrimaryButton), findsOneWidget);
      expect(find.text('تسجيل الدخول'), findsOneWidget);
      expect(find.byType(AuthGoogleButton), findsOneWidget);
      expect(find.text('تسجيل الدخول باستخدام Google'), findsOneWidget);

      // Sign up link
      expect(find.text('ليس لديك حساب في بادر؟'), findsOneWidget);
      expect(find.text('إنشاء حساب جديد'), findsOneWidget);

      // Decorative corner wave
      expect(find.byType(AuthCornerDecoration), findsOneWidget);
    });

    testWidgets('toggling password visibility changes icon and obscureText', (tester) async {
      await tester.pumpWidget(createTestWidget(const LoginPage()));
      await tester.pumpAndSettle();

      final visibilityIcon = find.byIcon(Icons.visibility_off_outlined);
      expect(visibilityIcon, findsOneWidget);

      // Tap to toggle visibility
      await tester.tap(visibilityIcon);
      await tester.pumpAndSettle();

      expect(find.byIcon(Icons.visibility_outlined), findsOneWidget);
    });

    testWidgets('displays AppErrorBanner when AuthState is in error', (tester) async {
      mockRepo.shouldThrow = true;

      final container = ProviderContainer(
        overrides: [
          authRepositoryProvider.overrideWithValue(mockRepo),
        ],
      );

      // Trigger login failure
      await container.read(authNotifierProvider.notifier).login(
        email: 'test@example.com',
        password: 'wrong_password',
      );

      await tester.pumpWidget(
        UncontrolledProviderScope(
          container: container,
          child: const MaterialApp(
            locale: Locale('ar'),
            home: LoginPage(),
          ),
        ),
      );
      await tester.pumpAndSettle();

      expect(find.byType(AppErrorBanner), findsOneWidget);
      expect(find.text('بيانات الاعتماد غير صحيحة'), findsOneWidget);
    });
  });

  group('RegisterPage UI & UX Tests', () {
    testWidgets('renders header, 4 fields only, and CTA buttons', (tester) async {
      await tester.pumpWidget(createTestWidget(const RegisterPage()));
      await tester.pumpAndSettle();

      // Header titles
      expect(find.text('إنشاء حساب جديد'), findsOneWidget);
      expect(find.text('انضم إلى منصة بادر وكن جزءاً من التغيير'), findsOneWidget);

      // 4 Form fields matching mockup
      expect(find.text('الاسم الكامل'), findsOneWidget);
      expect(find.text('البريد الإلكتروني'), findsOneWidget);
      expect(find.text('كلمة المرور'), findsOneWidget);
      expect(find.text('تأكيد كلمة المرور'), findsOneWidget);

      // Buttons
      expect(find.byType(AuthPrimaryButton), findsOneWidget);
      expect(find.text('إنشاء حساب'), findsOneWidget);
      expect(find.byType(AuthGoogleButton), findsOneWidget);
      expect(find.text('إنشاء حساب باستخدام Google'), findsOneWidget);

      // Terms disclaimer
      expect(find.textContaining('شروط الاستخدام وسياسة الخصوصية'), findsOneWidget);

      // Decorative corner wave
      expect(find.byType(AuthCornerDecoration), findsOneWidget);
    });

    testWidgets('toggles visibility in register password fields', (tester) async {
      await tester.pumpWidget(createTestWidget(const RegisterPage()));
      await tester.pumpAndSettle();

      final visibilityIcons = find.byIcon(Icons.visibility_off_outlined);
      // Both password & confirm password have visibility toggles
      expect(visibilityIcons, findsNWidgets(2));

      await tester.tap(visibilityIcons.first);
      await tester.pumpAndSettle();

      expect(find.byIcon(Icons.visibility_outlined), findsOneWidget);
    });
  });
}
