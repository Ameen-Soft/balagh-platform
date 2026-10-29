import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import 'package:mobile/features/auth/application/auth_state.dart';
import 'package:mobile/features/auth/application/providers.dart';
import 'package:mobile/features/auth/data/models/auth_response_model.dart';
import 'package:mobile/features/auth/data/models/user_model.dart';
import 'package:mobile/features/auth/domain/entities/user_entity.dart';
import 'package:mobile/features/auth/domain/repositories/auth_repository.dart';

void main() {
  group('Auth Models & Entities Tests', () {
    test('UserModel.fromJson parses complete backend UserResource payload', () {
      final json = {
        'id': 1,
        'name': 'علي محمد',
        'email': 'ali@example.com',
        'phone': '771234567',
        'national_id': '100200300',
        'is_active': true,
        'department': {
          'id': 5,
          'name': 'إدارة الأشغال العامة',
          'ministry_id': 2,
          'ministry': {
            'id': 2,
            'name': 'وزارة الأشغال العامة والطرق',
          },
        },
        'roles': [
          {
            'id': 1,
            'name': 'Citizen',
            'permissions': ['create-complaints', 'view-projects'],
          }
        ],
        'created_at': '2026-09-14T15:00:00.000000Z',
      };

      final userModel = UserModel.fromJson(json);

      expect(userModel.id, 1);
      expect(userModel.name, 'علي محمد');
      expect(userModel.email, 'ali@example.com');
      expect(userModel.phone, '771234567');
      expect(userModel.nationalId, '100200300');
      expect(userModel.isActive, true);
      expect(userModel.department?.id, 5);
      expect(userModel.department?.name, 'إدارة الأشغال العامة');
      expect(userModel.department?.ministry?.name, 'وزارة الأشغال العامة والطرق');
      expect(userModel.roles.length, 1);
      expect(userModel.roles.first.name, 'Citizen');
      expect(userModel.roles.first.permissions, ['create-complaints', 'view-projects']);

      final entity = userModel.toEntity();
      expect(entity.id, 1);
      expect(entity.name, 'علي محمد');
      expect(entity.primaryRoleName, 'Citizen');
      expect(entity.isCitizen, true);
      expect(entity.isAdmin, false);
      expect(entity.department?.ministryName, 'وزارة الأشغال العامة والطرق');
      expect(entity.allPermissions, ['create-complaints', 'view-projects']);
    });

    test('AuthResponseModel.fromJson parses success response with data wrapper', () {
      final json = {
        'success': true,
        'message': 'تم تسجيل الدخول بنجاح.',
        'data': {
          'user': {
            'id': 2,
            'name': 'سارة أحمد',
            'email': 'sara@example.com',
            'phone': null,
            'national_id': null,
            'is_active': true,
            'department': null,
            'roles': [],
            'created_at': '2026-09-14T12:00:00.000000Z',
          },
          'token': '3|sanctum_plain_text_token_xyz',
        },
      };

      final response = AuthResponseModel.fromJson(json);

      expect(response.success, true);
      expect(response.message, 'تم تسجيل الدخول بنجاح.');
      expect(response.data?.token, '3|sanctum_plain_text_token_xyz');
      expect(response.data?.user.id, 2);
      expect(response.data?.user.name, 'سارة أحمد');
    });

    test('AuthState transitions work correctly and protect against flicker', () {
      final initial = AuthState.initial();
      expect(initial.isInitial, true);
      expect(initial.isAuthenticated, false);
      expect(initial.isLoading, false);
      expect(initial.isUnauthenticated, false);

      final restoring = AuthState.restoring();
      expect(restoring.isRestoring, true);
      expect(restoring.isAuthenticated, false);

      final loading = AuthState.loading();
      expect(loading.isLoading, true);
      expect(loading.isInitial, false);

      const user = UserEntity(
        id: 1,
        name: 'فاطمة',
        email: 'fatima@example.com',
      );
      final authenticated = AuthState.authenticated(user);
      expect(authenticated.isAuthenticated, true);
      expect(authenticated.user?.name, 'فاطمة');
      expect(authenticated.user?.primaryRoleName, 'مواطن');
      expect(authenticated.isOffline, false);

      final offlineAuth = AuthState.serverUnavailable(user: user);
      expect(offlineAuth.isAuthenticated, true);
      expect(offlineAuth.isServerUnavailable, true);
      expect(offlineAuth.isOffline, true);
      expect(offlineAuth.user?.name, 'فاطمة');

      final unauthenticated = AuthState.unauthenticated();
      expect(unauthenticated.isUnauthenticated, true);
      expect(unauthenticated.isAuthenticated, false);

      final error = AuthState.error(
        'بيانات الاعتماد غير صحيحة',
        validationErrors: {
          'email': ['البريد الإلكتروني غير مسجل']
        },
      );
      expect(error.isError, true);
      expect(error.errorMessage, 'بيانات الاعتماد غير صحيحة');
      expect(error.validationErrors?['email']?.first, 'البريد الإلكتروني غير مسجل');
    });

    test('UserModel.fromEntity creates round-trip JSON identical to UserEntity', () {
      const entity = UserEntity(
        id: 7,
        name: 'جمال الشامي',
        email: 'gamal@example.ye',
        phone: '777123456',
        nationalId: '1234567890',
        isActive: true,
        isEmailVerified: true,
        department: DepartmentEntity(
          id: 3,
          name: 'إدارة النظافة',
          ministryId: 1,
          ministryName: 'وزارة الإدارة المحلية',
        ),
        roles: [
          RoleEntity(
            id: 2,
            name: 'field_worker',
            permissions: ['execute-tasks'],
          ),
        ],
      );

      final model = UserModel.fromEntity(entity);
      final json = model.toJson();
      final restored = UserModel.fromJson(json).toEntity();

      expect(restored.id, entity.id);
      expect(restored.name, entity.name);
      expect(restored.email, entity.email);
      expect(restored.phone, entity.phone);
      expect(restored.nationalId, entity.nationalId);
      expect(restored.isActive, true);
      expect(restored.isEmailVerified, true);
      expect(restored.isFieldWorker, true);
      expect(restored.department?.name, 'إدارة النظافة');
      expect(restored.department?.ministryName, 'وزارة الإدارة المحلية');
    });
  });

  group('Session Restoration & Offline Resilience Tests', () {
    late TestAuthRepository repo;
    late ProviderContainer container;

    const savedUser = UserEntity(
      id: 10,
      name: 'هشام القدسي',
      email: 'hisham@example.ye',
      isEmailVerified: true,
    );

    setUp(() {
      repo = TestAuthRepository();
      container = ProviderContainer(
        overrides: [
          authRepositoryProvider.overrideWithValue(repo),
        ],
      );
    });

    tearDown(() {
      container.dispose();
    });

    test('جلسة محفوظة وإنترنت متاح: يستعيد التطبيق المستخدم بصورة طبيعية', () async {
      repo.hasToken = true;
      repo.cachedUser = savedUser;
      repo.currentUser = const UserEntity(
        id: 10,
        name: 'هشام القدسي المحدث',
        email: 'hisham@example.ye',
        isEmailVerified: true,
      );

      final notifier = container.read(authNotifierProvider.notifier);
      await notifier.checkAuth();

      final state = container.read(authNotifierProvider);
      expect(state.isAuthenticated, true);
      expect(state.status, AuthStatus.authenticated);
      expect(state.isOffline, false);
      expect(state.user?.name, 'هشام القدسي المحدث');
      expect(repo.logoutCalled, false);
      expect(repo.savedUserLocally?.name, 'هشام القدسي المحدث');
    });

    test('جلسة محفوظة والإنترنت مغلق: لا يُحوَّل المستخدم إلى تسجيل الدخول ويحتفظ بالجلسة المحلية', () async {
      repo.hasToken = true;
      repo.cachedUser = savedUser;
      repo.shouldThrowNetworkError = true;

      final notifier = container.read(authNotifierProvider.notifier);
      await notifier.checkAuth();

      final state = container.read(authNotifierProvider);
      // User is kept authenticated offline
      expect(state.isAuthenticated, true);
      expect(state.isUnauthenticated, false);
      expect(state.isServerUnavailable, true);
      expect(state.isOffline, true);
      expect(state.user?.id, 10);
      expect(state.user?.name, 'هشام القدسي');
      // Token is NEVER cleared
      expect(repo.logoutCalled, false);
      expect(repo.hasToken, true);
    });

    test('فشل الاتصال أثناء التحقق من الجلسة: لا تُمسح بيانات الجلسة إطلاقاً', () async {
      repo.hasToken = true;
      repo.cachedUser = savedUser;
      repo.shouldThrowNetworkError = true;

      final notifier = container.read(authNotifierProvider.notifier);
      await notifier.checkAuth();

      expect(repo.hasToken, true);
      expect(repo.cachedUser, isNotNull);
      expect(repo.cachedUser?.id, 10);
      expect(repo.logoutCalled, false);
    });

    test('إعادة الاتصال بالإنترنت: يستأنف التطبيق التحقق وتحديث البيانات بصورة صحيحة', () async {
      // 1. App starts offline
      repo.hasToken = true;
      repo.cachedUser = savedUser;
      repo.shouldThrowNetworkError = true;

      final notifier = container.read(authNotifierProvider.notifier);
      await notifier.checkAuth();

      expect(container.read(authNotifierProvider).isServerUnavailable, true);

      // 2. Internet is restored
      repo.shouldThrowNetworkError = false;
      repo.currentUser = const UserEntity(
        id: 10,
        name: 'هشام القدسي أونلاين',
        email: 'hisham@example.ye',
        isEmailVerified: true,
      );

      await notifier.retrySessionVerification();

      final state = container.read(authNotifierProvider);
      expect(state.status, AuthStatus.authenticated);
      expect(state.isAuthenticated, true);
      expect(state.isServerUnavailable, false);
      expect(state.isOffline, false);
      expect(state.user?.name, 'هشام القدسي أونلاين');
    });

    test('عدم وجود جلسة محفوظة: تظهر حالة غير مسجل الدخول ليتم توجيهه إلى تسجيل الدخول', () async {
      repo.hasToken = false;
      repo.cachedUser = null;

      final notifier = container.read(authNotifierProvider.notifier);
      await notifier.checkAuth();

      final state = container.read(authNotifierProvider);
      expect(state.isUnauthenticated, true);
      expect(state.isAuthenticated, false);
    });

    test('انتهاء صلاحية الرمز أو إبطاله من الخادم (401): يُطبّق السلوك الآمن الصحيح بمسح الجلسة', () async {
      repo.hasToken = true;
      repo.cachedUser = savedUser;
      repo.shouldThrowUnauthorized = true;

      final notifier = container.read(authNotifierProvider.notifier);
      await notifier.checkAuth();

      final state = container.read(authNotifierProvider);
      expect(state.isUnauthenticated, true);
      expect(state.isAuthenticated, false);
      expect(repo.logoutCalled, true);
      expect(repo.hasToken, false);
      expect(repo.cachedUser, isNull);
    });

    test('الحساب المرفوض أو غير النشط (403): لا يتم تجاوز التحقق الأمني وتُمسح الجلسة', () async {
      repo.hasToken = true;
      repo.cachedUser = savedUser;
      repo.shouldThrowForbidden = true;

      final notifier = container.read(authNotifierProvider.notifier);
      await notifier.checkAuth();

      final state = container.read(authNotifierProvider);
      expect(state.isError, true);
      expect(state.isAuthenticated, false);
      expect(repo.logoutCalled, true);
      expect(repo.hasToken, false);
      expect(repo.cachedUser, isNull);
    });

    test('تسجيل الخروج يدويًا: تُمسح الجلسة نهائياً ويُمنع استعادتها', () async {
      repo.hasToken = true;
      repo.cachedUser = savedUser;

      final notifier = container.read(authNotifierProvider.notifier);
      // User restores authenticated session
      await notifier.checkAuth();
      expect(container.read(authNotifierProvider).isAuthenticated, true);

      // User performs logout
      await notifier.logout();

      final state = container.read(authNotifierProvider);
      expect(state.isUnauthenticated, true);
      expect(repo.logoutCalled, true);
      expect(repo.hasToken, false);
      expect(repo.cachedUser, isNull);

      // Subsequent checkAuth remains unauthenticated
      await notifier.checkAuth();
      expect(container.read(authNotifierProvider).isUnauthenticated, true);
    });
  });
}

class TestAuthRepository implements AuthRepository {
  bool hasToken = false;
  UserEntity? cachedUser;
  UserEntity? currentUser;
  UserEntity? savedUserLocally;
  bool shouldThrowNetworkError = false;
  bool shouldThrowUnauthorized = false;
  bool shouldThrowForbidden = false;
  bool logoutCalled = false;

  @override
  Future<bool> hasSavedToken() async => hasToken;

  @override
  Future<UserEntity?> getCachedUser() async => cachedUser;

  @override
  Future<void> saveCachedUser(UserEntity user) async {
    savedUserLocally = user;
    cachedUser = user;
  }

  @override
  Future<UserEntity> getCurrentUser() async {
    if (shouldThrowNetworkError) {
      throw const NetworkException('تعذر الاتصال بالخادم، يرجى التحقق من اتصال الإنترنت.');
    }
    if (shouldThrowUnauthorized) {
      throw const UnauthorizedException('انتهت صلاحية الجلسة، يرجى تسجيل الدخول مجدداً.');
    }
    if (shouldThrowForbidden) {
      throw const ForbiddenException('حسابك غير نشط، يرجى التواصل مع الإدارة.');
    }
    final user = currentUser ??
        const UserEntity(
          id: 1,
          name: 'مواطن مسجل',
          email: 'citizen@example.ye',
          isEmailVerified: true,
        );
    savedUserLocally = user;
    return user;
  }

  @override
  Future<void> logout() async {
    logoutCalled = true;
    hasToken = false;
    cachedUser = null;
    savedUserLocally = null;
  }

  @override
  Future<UserEntity> login({
    required String email,
    required String password,
    String? deviceName,
  }) async {
    return const UserEntity(id: 1, name: 'تجريبي', email: 'test@example.ye');
  }

  @override
  Future<UserEntity> loginWithGoogle(String idToken) async {
    return const UserEntity(id: 1, name: 'تجريبي', email: 'test@example.ye');
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
    return const UserEntity(id: 1, name: 'تجريبي', email: 'test@example.ye');
  }

  @override
  Future<void> sendEmailVerification({String? email}) async {}

  @override
  Future<bool> checkEmailVerificationStatus() async => true;

  @override
  Future<void> forgotPassword(String email) async {}

  @override
  Future<void> resetPassword({
    required String email,
    required String token,
    required String password,
    required String passwordConfirmation,
  }) async {}
}
