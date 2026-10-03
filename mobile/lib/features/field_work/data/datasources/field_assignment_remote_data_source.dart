import 'package:dio/dio.dart';
import 'package:mobile/core/network/api_client.dart';
import 'package:mobile/core/network/api_endpoints.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import '../models/field_assignment_model.dart';

/// Abstract data source for field assignment API operations.
abstract class FieldAssignmentRemoteDataSource {
  Future<List<FieldWorkAssignmentModel>> getAssignments({String? status});
  Future<FieldWorkAssignmentModel> acceptAssignment(int assignmentId);
  Future<Map<String, dynamic>> verifyLocation({
    required int assignmentId,
    required double latitude,
    required double longitude,
  });
  Future<FieldWorkAssignmentModel> startAssignment(int assignmentId);
  Future<FieldWorkAssignmentModel> completeAssignment({
    required int assignmentId,
    required String report,
    required double latitude,
    required double longitude,
    List<String>? attachmentPaths,
  });
}

class FieldAssignmentRemoteDataSourceImpl
    implements FieldAssignmentRemoteDataSource {
  final ApiClient apiClient;

  FieldAssignmentRemoteDataSourceImpl({required this.apiClient});

  @override
  Future<List<FieldWorkAssignmentModel>> getAssignments({
    String? status,
  }) async {
    final Map<String, dynamic> queryParams = {
      if (status != null && status.isNotEmpty) 'status': status,
    };

    final response = await apiClient.get(
      ApiEndpoints.fieldAssignments,
      queryParameters: queryParams,
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is List) {
      return (data['data'] as List)
          .whereType<Map<String, dynamic>>()
          .map((item) => FieldWorkAssignmentModel.fromJson(item))
          .toList();
    }

    return [];
  }

  @override
  Future<FieldWorkAssignmentModel> acceptAssignment(int assignmentId) async {
    final response = await apiClient.patch(
      ApiEndpoints.acceptFieldAssignment(assignmentId),
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return FieldWorkAssignmentModel.fromJson(
          data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('استجابة غير صالحة عند قبول المهمة.');
  }

  @override
  Future<Map<String, dynamic>> verifyLocation({
    required int assignmentId,
    required double latitude,
    required double longitude,
  }) async {
    final response = await apiClient.post(
      ApiEndpoints.verifyFieldWorkerLocation(assignmentId),
      data: {
        'latitude': latitude,
        'longitude': longitude,
      },
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return data['data'] as Map<String, dynamic>;
    }

    throw const ServerException(
        'استجابة غير صالحة عند التحقق من الموقع الجغرافي.');
  }

  @override
  Future<FieldWorkAssignmentModel> startAssignment(int assignmentId) async {
    final response = await apiClient.post(
      ApiEndpoints.startFieldAssignment(assignmentId),
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return FieldWorkAssignmentModel.fromJson(
          data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('استجابة غير صالحة عند بدء المهمة.');
  }

  @override
  Future<FieldWorkAssignmentModel> completeAssignment({
    required int assignmentId,
    required String report,
    required double latitude,
    required double longitude,
    List<String>? attachmentPaths,
  }) async {
    dynamic payload;

    if (attachmentPaths != null && attachmentPaths.isNotEmpty) {
      final formData = FormData();
      formData.fields.addAll([
        MapEntry('report', report),
        MapEntry('latitude', latitude.toString()),
        MapEntry('longitude', longitude.toString()),
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
        'report': report,
        'latitude': latitude,
        'longitude': longitude,
      };
    }

    final response = await apiClient.post(
      ApiEndpoints.completeFieldAssignment(assignmentId),
      data: payload,
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return FieldWorkAssignmentModel.fromJson(
          data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('استجابة غير صالحة عند إتمام المهمة.');
  }
}
