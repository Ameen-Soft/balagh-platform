import '../entities/user_entity.dart';

abstract class AuthRepository {
  Future<UserEntity> login({
    required String email,
    required String password,
    String? deviceName,
  });

  Future<UserEntity> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
    String? nationalId,
  });

  Future<void> logout();

  Future<UserEntity> getCurrentUser();

  Future<bool> hasSavedToken();
}
