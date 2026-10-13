import 'package:mobile/core/network/api_client.dart';
import 'package:mobile/core/network/api_endpoints.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import '../models/auth_response_model.dart';
import '../models/user_model.dart';

abstract class AuthRemoteDataSource {
  Future<AuthDataModel> login({
    required String email,
    required String password,
    String? deviceName,
  });

  Future<AuthDataModel> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
    String? nationalId,
  });

  Future<void> logout();

  Future<UserModel> me();

  Future<AuthDataModel> loginWithGoogle(String idToken);

  Future<void> sendEmailVerification({String? email});

  Future<bool> checkEmailVerificationStatus();

  Future<void> forgotPassword(String email);

  Future<void> resetPassword({
    required String email,
    required String token,
    required String password,
    required String passwordConfirmation,
  });
}

class AuthRemoteDataSourceImpl implements AuthRemoteDataSource {
  final ApiClient apiClient;

  AuthRemoteDataSourceImpl({required this.apiClient});

  @override
  Future<AuthDataModel> login({
    required String email,
    required String password,
    String? deviceName,
  }) async {
    final Map<String, dynamic> body = {
      'email': email,
      'password': password,
    };
    if (deviceName != null && deviceName.isNotEmpty) {
      body['device_name'] = deviceName;
    }

    final response = await apiClient.post(
      ApiEndpoints.login,
      data: body,
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return AuthDataModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('استجابة غير صالحة من الخادم أثناء تسجيل الدخول.');
  }

  @override
  Future<AuthDataModel> loginWithGoogle(String idToken) async {
    final response = await apiClient.post(
      ApiEndpoints.googleLogin,
      data: {
        'id_token': idToken,
      },
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return AuthDataModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('استجابة غير صالحة من الخادم أثناء تسجيل الدخول باستخدام جوجل.');
  }

  @override
  Future<AuthDataModel> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
    String? nationalId,
  }) async {
    final Map<String, dynamic> body = {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
    };

    if (phone != null && phone.trim().isNotEmpty) {
      body['phone'] = phone.trim();
    }
    if (nationalId != null && nationalId.trim().isNotEmpty) {
      body['national_id'] = nationalId.trim();
    }

    final response = await apiClient.post(
      ApiEndpoints.register,
      data: body,
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return AuthDataModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('استجابة غير صالحة من الخادم أثناء التسجيل.');
  }

  @override
  Future<void> logout() async {
    await apiClient.post(ApiEndpoints.logout);
  }

  @override
  Future<UserModel> me() async {
    final response = await apiClient.get(ApiEndpoints.me);
    final data = response.data;

    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return UserModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('استجابة غير متوقعة لبيانات المستخدم.');
  }

  @override
  Future<void> sendEmailVerification({String? email}) async {
    if (email != null && email.trim().isNotEmpty) {
      await apiClient.post(
        ApiEndpoints.resendVerificationEmail,
        data: {'email': email.trim()},
      );
    } else {
      await apiClient.post(ApiEndpoints.resendVerificationAuth);
    }
  }

  @override
  Future<bool> checkEmailVerificationStatus() async {
    try {
      final response = await apiClient.get(ApiEndpoints.emailVerificationStatus);
      final data = response.data;
      if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
        final inner = data['data'] as Map<String, dynamic>;
        return inner['is_verified'] == true;
      }
    } catch (_) {
      // Fallback to me endpoint
      final user = await me();
      return user.isEmailVerified;
    }
    return false;
  }

  @override
  Future<void> forgotPassword(String email) async {
    await apiClient.post(
      ApiEndpoints.forgotPassword,
      data: {'email': email.trim()},
    );
  }

  @override
  Future<void> resetPassword({
    required String email,
    required String token,
    required String password,
    required String passwordConfirmation,
  }) async {
    await apiClient.post(
      ApiEndpoints.resetPassword,
      data: {
        'email': email.trim(),
        'token': token.trim(),
        'password': password,
        'password_confirmation': passwordConfirmation,
      },
    );
  }
}

