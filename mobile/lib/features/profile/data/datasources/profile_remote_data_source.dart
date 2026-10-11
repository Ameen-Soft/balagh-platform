import 'package:mobile/core/network/api_client.dart';
import 'package:mobile/core/network/api_endpoints.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import 'package:mobile/features/auth/data/models/user_model.dart';

abstract class ProfileRemoteDataSource {
  Future<UserModel> getProfile();

  Future<UserModel> updateProfile({
    required String name,
    String? phone,
    String? nationalId,
  });
}

class ProfileRemoteDataSourceImpl implements ProfileRemoteDataSource {
  final ApiClient apiClient;

  ProfileRemoteDataSourceImpl({required this.apiClient});

  @override
  Future<UserModel> getProfile() async {
    final response = await apiClient.get(ApiEndpoints.me);
    final data = response.data;

    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return UserModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('استجابة غير متوقعة لبيانات الملف الشخصي.');
  }

  @override
  Future<UserModel> updateProfile({
    required String name,
    String? phone,
    String? nationalId,
  }) async {
    final Map<String, dynamic> body = {
      'name': name.trim(),
      'phone': phone?.trim(),
      'national_id': nationalId?.trim(),
    };

    final response = await apiClient.put(
      ApiEndpoints.updateProfile,
      data: body,
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return UserModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('فشل تحديث بيانات الملف الشخصي.');
  }
}
