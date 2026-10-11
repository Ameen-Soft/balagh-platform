import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import 'package:mobile/features/auth/data/models/user_model.dart';
import 'package:mobile/features/auth/domain/entities/user_entity.dart';
import 'package:mobile/features/profile/application/profile_state.dart';
import 'package:mobile/features/profile/domain/repositories/profile_repository.dart';

class FakeProfileRepository implements ProfileRepository {
  UserEntity? mockUser;
  bool shouldThrow = false;
  ApiException? exceptionToThrow;

  @override
  Future<UserEntity> getProfile() async {
    if (shouldThrow) {
      throw exceptionToThrow ?? const ServerException('خطأ في استرجاع الملف الشخصي');
    }
    return mockUser ??
        const UserEntity(
          id: 1,
          name: 'أمين العليمي',
          email: 'ameen@example.com',
          phone: '777123456',
          nationalId: '10020030040',
          roles: [
            RoleEntity(id: 1, name: 'citizen', permissions: ['complaints.create']),
          ],
        );
  }

  @override
  Future<UserEntity> updateProfile({
    required String name,
    String? phone,
    String? nationalId,
  }) async {
    if (shouldThrow) {
      throw exceptionToThrow ?? const ServerException('فشل تحديث البيانات');
    }
    final current = mockUser ??
        const UserEntity(
          id: 1,
          name: 'أمين العليمي',
          email: 'ameen@example.com',
          phone: '777123456',
          nationalId: '10020030040',
          roles: [
            RoleEntity(id: 1, name: 'citizen', permissions: ['complaints.create']),
          ],
        );

    final updated = current.copyWith(
      name: name,
      phone: phone ?? current.phone,
      nationalId: nationalId ?? current.nationalId,
    );
    mockUser = updated;
    return updated;
  }
}

void main() {
  group('Phase 09: Profile Feature Tests', () {
    test('UserEntity copyWith updates allowed fields without mutating roles or ID', () {
      const originalUser = UserEntity(
        id: 42,
        name: 'أحمد علي',
        email: 'ahmed@balagh.ye',
        phone: '771234567',
        nationalId: '01010012345',
        roles: [
          RoleEntity(id: 2, name: 'field_worker', permissions: ['tasks.view', 'tasks.execute']),
        ],
      );

      final modifiedUser = originalUser.copyWith(
        name: 'أحمد علي حسن',
        phone: '779998877',
        nationalId: '01010099999',
      );

      expect(modifiedUser.id, 42);
      expect(modifiedUser.email, 'ahmed@balagh.ye');
      expect(modifiedUser.name, 'أحمد علي حسن');
      expect(modifiedUser.phone, '779998877');
      expect(modifiedUser.nationalId, '01010099999');
      expect(modifiedUser.isFieldWorker, isTrue);
      expect(modifiedUser.roles.first.name, 'field_worker');
      expect(modifiedUser.roles.first.permissions, ['tasks.view', 'tasks.execute']);
    });

    test('UserModel.fromJson parses backend UserResource with roles and citizen permissions', () {
      final json = {
        'id': 10,
        'name': 'فاطمة محمد',
        'email': 'fatima@balagh.ye',
        'phone': '733445566',
        'national_id': '02020054321',
        'is_active': true,
        'roles': [
          {
            'id': 3,
            'name': 'citizen',
            'permissions': ['complaints.create', 'complaints.view'],
          }
        ],
        'created_at': '2026-09-01T10:00:00.000000Z',
      };

      final model = UserModel.fromJson(json);
      final entity = model.toEntity();

      expect(entity.id, 10);
      expect(entity.name, 'فاطمة محمد');
      expect(entity.email, 'fatima@balagh.ye');
      expect(entity.phone, '733445566');
      expect(entity.nationalId, '02020054321');
      expect(entity.isCitizen, isTrue);
      expect(entity.isFieldWorker, isFalse);
      expect(entity.roles.first.permissions, ['complaints.create', 'complaints.view']);
    });

    test('ProfileState initial, loading, success, and error state transitions', () {
      const initial = ProfileState.initial();
      expect(initial.isInitial, isTrue);
      expect(initial.isLoading, isFalse);
      expect(initial.isSaving, isFalse);
      expect(initial.user, isNull);

      const loading = ProfileState.loading();
      expect(loading.isLoading, isTrue);

      const user = UserEntity(
        id: 1,
        name: 'مستخدم تجريبي',
        email: 'test@balagh.ye',
      );

      final loaded = ProfileState.loaded(user);
      expect(loaded.isLoaded, isTrue);
      expect(loaded.user?.name, 'مستخدم تجريبي');

      final saving = loaded.copyWith(isSaving: true);
      expect(saving.isSaving, isTrue);
      expect(saving.user?.name, 'مستخدم تجريبي');

      final success = loaded.copyWith(
        isSaving: false,
        successMessage: 'تم تحديث الملف الشخصي بنجاح',
      );
      expect(success.successMessage, 'تم تحديث الملف الشخصي بنجاح');
      expect(success.isSaving, isFalse);

      final error = ProfileState.error('فشل الاتصال بالخادم');
      expect(error.hasError, isTrue);
      expect(error.errorMessage, 'فشل الاتصال بالخادم');
    });

    test('ProfileRepository updates profile fields successfully', () async {
      final repo = FakeProfileRepository();
      final updated = await repo.updateProfile(
        name: 'أمين العلمي الجديد',
        phone: '770000000',
        nationalId: '12345678901',
      );

      expect(updated.name, 'أمين العلمي الجديد');
      expect(updated.phone, '770000000');
      expect(updated.nationalId, '12345678901');
    });

    test('ProfileRepository throws ValidationException on invalid payload format', () async {
      final repo = FakeProfileRepository();
      repo.shouldThrow = true;
      repo.exceptionToThrow = const ValidationException(
        'بيانات غير صالحة',
        errors: {
          'phone': ['رقم الهاتف غير صالح'],
        },
      );

      expect(
        () => repo.updateProfile(name: 'أمين', phone: 'invalid'),
        throwsA(isA<ValidationException>()),
      );
    });
  });
}
