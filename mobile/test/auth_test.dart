import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/auth/application/auth_state.dart';
import 'package:mobile/features/auth/data/models/auth_response_model.dart';
import 'package:mobile/features/auth/data/models/user_model.dart';
import 'package:mobile/features/auth/domain/entities/user_entity.dart';

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
  });
}
