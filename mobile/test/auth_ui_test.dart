import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import 'package:mobile/core/widgets/app_button.dart';
import 'package:mobile/core/widgets/app_error_banner.dart';
import 'package:mobile/features/auth/application/providers.dart';
import 'package:mobile/features/auth/domain/entities/user_entity.dart';
import 'package:mobile/features/auth/domain/repositories/auth_repository.dart';
import 'package:mobile/features/auth/presentation/pages/login_page.dart';
import 'package:mobile/features/auth/presentation/pages/register_page.dart';

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
    testWidgets('renders brand identity, title, fields, and submit CTA button', (tester) async {
      await tester.pumpWidget(createTestWidget(const LoginPage()));
      await tester.pumpAndSettle();

      // Brand Identity
      expect(find.text('بــــادر'), findsOneWidget);
      expect(find.text('المنصة الوطنية للمشاريع والشكاوى'), findsOneWidget);

      // Title & Subtitle
      expect(find.text('تسجيل الدخول'), findsWidgets);
      expect(find.textContaining('أهلاً بك مجدداً'), findsOneWidget);

      // Text fields
      expect(find.text('البريد الإلكتروني'), findsOneWidget);
      expect(find.text('كلمة المرور'), findsOneWidget);

      // Button
      expect(find.byType(AppButton), findsOneWidget);
      expect(find.text('إنشاء حساب جديد'), findsOneWidget);
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
    testWidgets('renders all 3 logical sections with required/optional fields', (tester) async {
      await tester.pumpWidget(createTestWidget(const RegisterPage()));
      await tester.pumpAndSettle();

      // Section titles
      expect(find.text('المعلومات الشخصية'), findsOneWidget);
      expect(find.text('بيانات التحقق والتواصل'), findsOneWidget);
      expect(find.text('أمان الحساب'), findsOneWidget);
      expect(find.text('اختياري'), findsOneWidget);

      // Required fields
      expect(find.text('الاسم الكامل'), findsOneWidget);
      expect(find.text('البريد الإلكتروني'), findsOneWidget);
      expect(find.text('رقم الهاتف'), findsOneWidget);
      expect(find.text('رقم الهوية الوطنية'), findsOneWidget);
      expect(find.text('كلمة المرور'), findsOneWidget);
      expect(find.text('تأكيد كلمة المرور'), findsOneWidget);

      // CTA Button
      expect(find.text('إنشاء الحساب'), findsOneWidget);
    });

    testWidgets('shows password matching feedback when passwords match', (tester) async {
      await tester.pumpWidget(createTestWidget(const RegisterPage()));
      await tester.pumpAndSettle();

      // Enter matching passwords
      final passwordFields = find.byType(TextFormField);
      // Index 0: Name, 1: Email, 2: Phone, 3: NationalID, 4: Password, 5: ConfirmPassword
      await tester.enterText(passwordFields.at(4), 'Secret1234');
      await tester.enterText(passwordFields.at(5), 'Secret1234');
      await tester.pumpAndSettle();

      expect(find.text('كلمتا المرور متطابقتان وجاهزتان'), findsOneWidget);
    });

    testWidgets('shows warning feedback when passwords do not match', (tester) async {
      await tester.pumpWidget(createTestWidget(const RegisterPage()));
      await tester.pumpAndSettle();

      final passwordFields = find.byType(TextFormField);
      await tester.enterText(passwordFields.at(4), 'Secret1234');
      await tester.enterText(passwordFields.at(5), 'MismatchPassword');
      await tester.pumpAndSettle();

      expect(find.text('كلمتا المرور غير متطابقتين بعد'), findsOneWidget);
    });
  });
}
