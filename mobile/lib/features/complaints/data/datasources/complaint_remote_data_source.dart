import 'package:dio/dio.dart';
import 'package:mobile/core/network/api_client.dart';
import 'package:mobile/core/network/api_endpoints.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import '../models/category_model.dart';
import '../models/complaint_model.dart';
import '../models/ministry_model.dart';

abstract class ComplaintRemoteDataSource {
  Future<List<ComplaintModel>> getComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  });

  Future<ComplaintModel> getComplaintDetails(int id);

  Future<ComplaintModel> createComplaint({
    required String title,
    required String description,
    required int categoryId,
    required double latitude,
    required double longitude,
    String? priority,
    List<String>? attachmentPaths,
  });

  Future<List<MinistryModel>> getMinistries();

  Future<List<CategoryModel>> getCategories({
    int? ministryId,
    int? departmentId,
    int? parentId,
    int? level,
  });
}

class ComplaintRemoteDataSourceImpl implements ComplaintRemoteDataSource {
  final ApiClient apiClient;

  ComplaintRemoteDataSourceImpl({required this.apiClient});

  @override
  Future<List<ComplaintModel>> getComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  }) async {
    final Map<String, dynamic> queryParams = {
      'page': page,
      if (status != null && status.isNotEmpty) 'status': status,
      'category_id': ?categoryId,
      'department_id': ?departmentId,
      if (search != null && search.isNotEmpty) 'search': search,
    };

    final response = await apiClient.get(
      ApiEndpoints.complaints,
      queryParameters: queryParams,
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is List) {
      return (data['data'] as List)
          .whereType<Map<String, dynamic>>()
          .map((item) => ComplaintModel.fromJson(item))
          .toList();
    }

    return [];
  }

  @override
  Future<ComplaintModel> getComplaintDetails(int id) async {
    final response = await apiClient.get(ApiEndpoints.complaintDetails(id));
    final data = response.data;

    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return ComplaintModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('استجابة غير صالحة لتفاصيل البلاغ.');
  }

  @override
  Future<ComplaintModel> createComplaint({
    required String title,
    required String description,
    required int categoryId,
    required double latitude,
    required double longitude,
    String? priority,
    List<String>? attachmentPaths,
  }) async {
    dynamic payload;

    if (attachmentPaths != null && attachmentPaths.isNotEmpty) {
      final List<MultipartFile> files = [];
      for (final path in attachmentPaths) {
        files.add(await MultipartFile.fromFile(path));
      }

      payload = FormData.fromMap({
        'title': title,
        'description': description,
        'category_id': categoryId,
        'latitude': latitude,
        'longitude': longitude,
        'priority': ?priority,
        'attachments[]': files,
      });
    } else {
      payload = {
        'title': title,
        'description': description,
        'category_id': categoryId,
        'latitude': latitude,
        'longitude': longitude,
        'priority': ?priority,
      };
    }

    final response = await apiClient.post(
      ApiEndpoints.complaints,
      data: payload,
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return ComplaintModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('فشل تسجيل البلاغ الجديد.');
  }

  @override
  Future<List<MinistryModel>> getMinistries() async {
    final response = await apiClient.get(ApiEndpoints.ministries);
    final data = response.data;

    if (data is Map<String, dynamic> && data['data'] is List) {
      return (data['data'] as List)
          .whereType<Map<String, dynamic>>()
          .map((item) => MinistryModel.fromJson(item))
          .toList();
    }

    return [];
  }

  @override
  Future<List<CategoryModel>> getCategories({
    int? ministryId,
    int? departmentId,
    int? parentId,
    int? level,
  }) async {
    final Map<String, dynamic> queryParams = {
      'ministry_id': ?ministryId,
      'department_id': ?departmentId,
      'parent_id': ?parentId,
      'level': ?level,
    };

    final response = await apiClient.get(
      ApiEndpoints.categories,
      queryParameters: queryParams,
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is List) {
      return (data['data'] as List)
          .whereType<Map<String, dynamic>>()
          .map((item) => CategoryModel.fromJson(item))
          .toList();
    }

    return [];
  }
}
