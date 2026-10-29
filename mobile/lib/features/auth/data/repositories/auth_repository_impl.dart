import 'dart:convert';
import 'package:mobile/core/storage/secure_storage_service.dart';
import '../../domain/entities/user_entity.dart';
import '../../domain/repositories/auth_repository.dart';
import '../datasources/auth_remote_data_source.dart';
import '../models/user_model.dart';

class AuthRepositoryImpl implements AuthRepository {
  final AuthRemoteDataSource remoteDataSource;
  final ISecureStorageService secureStorage;

  AuthRepositoryImpl({
    required this.remoteDataSource,
    required this.secureStorage,
  });

  Future<void> _cacheUser(UserEntity user) async {
    try {
      final jsonStr = jsonEncode(UserModel.fromEntity(user).toJson());
      await secureStorage.saveUserData(jsonStr);
    } catch (_) {}
  }

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
    final entity = authData.user.toEntity();
    await _cacheUser(entity);
    return entity;
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
    final entity = authData.user.toEntity();
    await _cacheUser(entity);
    return entity;
  }

  @override
  Future<UserEntity> loginWithGoogle(String idToken) async {
    final authData = await remoteDataSource.loginWithGoogle(idToken);
    await secureStorage.saveToken(authData.token);
    final entity = authData.user.toEntity();
    await _cacheUser(entity);
    return entity;
  }

  @override
  Future<void> logout() async {
    try {
      await remoteDataSource.logout();
    } catch (_) {
      // Even if remote logout fails (e.g. network offline), proceed with local token deletion
    } finally {
      await secureStorage.deleteToken();
      await secureStorage.deleteUserData();
    }
  }

  @override
  Future<UserEntity> getCurrentUser() async {
    final userModel = await remoteDataSource.me();
    final entity = userModel.toEntity();
    await _cacheUser(entity);
    return entity;
  }

  @override
  Future<bool> hasSavedToken() async {
    return await secureStorage.hasToken();
  }

  @override
  Future<UserEntity?> getCachedUser() async {
    try {
      final jsonStr = await secureStorage.getUserData();
      if (jsonStr == null || jsonStr.isEmpty) return null;
      final map = jsonDecode(jsonStr) as Map<String, dynamic>;
      return UserModel.fromJson(map).toEntity();
    } catch (_) {
      return null;
    }
  }

  @override
  Future<void> saveCachedUser(UserEntity user) async {
    await _cacheUser(user);
  }

  @override
  Future<void> sendEmailVerification({String? email}) async {
    await remoteDataSource.sendEmailVerification(email: email);
  }

  @override
  Future<bool> checkEmailVerificationStatus() async {
    return await remoteDataSource.checkEmailVerificationStatus();
  }

  @override
  Future<void> forgotPassword(String email) async {
    await remoteDataSource.forgotPassword(email);
  }

  @override
  Future<void> resetPassword({
    required String email,
    required String token,
    required String password,
    required String passwordConfirmation,
  }) async {
    await remoteDataSource.resetPassword(
      email: email,
      token: token,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );
  }
}

