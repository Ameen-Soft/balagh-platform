import 'package:dio/dio.dart';
import 'package:mobile/core/network/api_client.dart';
import 'package:mobile/core/network/api_endpoints.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import '../../domain/entities/complaint_like_result.dart';
import '../models/category_model.dart';
import '../models/complaint_model.dart';
import '../models/ministry_model.dart';
import '../models/paginated_complaints_model.dart';
import '../models/pagination_meta_model.dart';

abstract class ComplaintRemoteDataSource {
  Future<List<ComplaintModel>> getComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  });

  Future<PaginatedComplaintsModel> getPaginatedComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  });

  Future<PaginatedComplaintsModel> getPublicComplaints({
    int page = 1,
    String? sortBy,
    int? categoryId,
    String? status,
    String? search,
  });

  Future<List<ComplaintModel>> getMapComplaints({
    double? minLat,
    double? minLng,
    double? maxLat,
    double? maxLng,
  });

  Future<ComplaintLikeResult> likeComplaint(int complaintId);

  Future<ComplaintLikeResult> unlikeComplaint(int complaintId);

  Future<ComplaintModel> getComplaintDetails(int id);

  Future<ComplaintModel> createComplaint({
    required String title,
    required String description,
    required int categoryId,
    required double latitude,
    required double longitude,
    String? priority,
    bool? isPublic,
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
  Future<PaginatedComplaintsModel> getPaginatedComplaints({
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
    if (data is Map<String, dynamic>) {
      return PaginatedComplaintsModel.fromJson(data);
    }

    return PaginatedComplaintsModel(
      items: [],
      meta: PaginationMetaModel.empty(),
    );
  }

  @override
  Future<PaginatedComplaintsModel> getPublicComplaints({
    int page = 1,
    String? sortBy,
    int? categoryId,
    String? status,
    String? search,
  }) async {
    final Map<String, dynamic> queryParams = {
      'page': page,
      if (sortBy != null && sortBy.isNotEmpty) 'sort_by': sortBy,
      'category_id': ?categoryId,
      if (status != null && status.isNotEmpty) 'status': status,
      if (search != null && search.isNotEmpty) 'search': search,
    };

    final response = await apiClient.get(
      ApiEndpoints.publicComplaints,
      queryParameters: queryParams,
    );

    final data = response.data;
    if (data is Map<String, dynamic>) {
      return PaginatedComplaintsModel.fromJson(data);
    }

    return PaginatedComplaintsModel(
      items: [],
      meta: PaginationMetaModel.empty(),
    );
  }

  @override
  Future<List<ComplaintModel>> getMapComplaints({
    double? minLat,
    double? minLng,
    double? maxLat,
    double? maxLng,
  }) async {
    final Map<String, dynamic> queryParams = {};
    if (minLat != null && minLng != null && maxLat != null && maxLng != null) {
      queryParams['bbox'] = '$minLat,$minLng,$maxLat,$maxLng';
    }

    final response = await apiClient.get(
      ApiEndpoints.mapComplaints,
      queryParameters: queryParams.isNotEmpty ? queryParams : null,
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
  Future<ComplaintLikeResult> likeComplaint(int complaintId) async {
    final response = await apiClient.post(
      ApiEndpoints.likeComplaint(complaintId),
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return ComplaintLikeResult.fromJson(data['data'] as Map<String, dynamic>);
    }

    return const ComplaintLikeResult(isLiked: true, likesCount: 1);
  }

  @override
  Future<ComplaintLikeResult> unlikeComplaint(int complaintId) async {
    final response = await apiClient.delete(
      ApiEndpoints.unlikeComplaint(complaintId),
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return ComplaintLikeResult.fromJson(data['data'] as Map<String, dynamic>);
    }

    return const ComplaintLikeResult(isLiked: false, likesCount: 0);
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
    bool? isPublic,
    List<String>? attachmentPaths,
  }) async {
    dynamic payload;

    if (attachmentPaths != null && attachmentPaths.isNotEmpty) {
      final formData = FormData();
      formData.fields.addAll([
        MapEntry('title', title),
        MapEntry('description', description),
        MapEntry('category_id', categoryId.toString()),
        MapEntry('latitude', latitude.toString()),
        MapEntry('longitude', longitude.toString()),
        if (priority != null && priority.isNotEmpty) MapEntry('priority', priority),
        if (isPublic != null) MapEntry('is_public', isPublic ? '1' : '0'),
      ]);

      for (final path in attachmentPaths) {
        formData.files.add(MapEntry(
          'attachments[]',
          await MultipartFile.fromFile(path),
        ));
      }

      payload = formData;
    } else {
      payload = {
        'title': title,
        'description': description,
        'category_id': categoryId,
        'latitude': latitude,
        'longitude': longitude,
        'priority': ?priority,
        'is_public': ?isPublic,
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
