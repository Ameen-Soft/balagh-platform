import 'package:mobile/core/storage/secure_storage_service.dart';
import '../../domain/entities/user_entity.dart';
import '../../domain/repositories/auth_repository.dart';
import '../datasources/auth_remote_data_source.dart';

class AuthRepositoryImpl implements AuthRepository {
  final AuthRemoteDataSource remoteDataSource;
  final ISecureStorageService secureStorage;

  AuthRepositoryImpl({
    required this.remoteDataSource,
    required this.secureStorage,
  });

  @override
  Future<UserEntity> login({
    required String email,
    required String password,
    String? deviceName,
  }) async {
    final authData = await remoteDataSource.login(
      email: email,
      password: password,
      deviceName: deviceName,
    );

    await secureStorage.saveToken(authData.token);
    return authData.user.toEntity();
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
    final authData = await remoteDataSource.register(
      name: name,
      email: email,
      password: password,
      passwordConfirmation: passwordConfirmation,
      phone: phone,
      nationalId: nationalId,
    );

    await secureStorage.saveToken(authData.token);
    return authData.user.toEntity();
  }

  @override
  Future<void> logout() async {
    try {
      await remoteDataSource.logout();
    } catch (_) {
      // Even if remote logout fails (e.g. network offline), proceed with local token deletion
    } finally {
      await secureStorage.deleteToken();
    }
  }

  @override
  Future<UserEntity> getCurrentUser() async {
    final userModel = await remoteDataSource.me();
    return userModel.toEntity();
  }

  @override
  Future<bool> hasSavedToken() async {
    return await secureStorage.hasToken();
  }
}
