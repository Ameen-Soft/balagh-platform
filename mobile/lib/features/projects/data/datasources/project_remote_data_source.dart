import 'package:mobile/core/network/api_client.dart';
import 'package:mobile/core/network/api_endpoints.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import '../models/project_contribution_model.dart';
import '../models/project_model.dart';

abstract class ProjectRemoteDataSource {
  Future<List<ProjectModel>> getProjects({
    int page = 1,
    String? status,
    String? search,
    int? departmentId,
    int? ministryId,
  });

  Future<ProjectModel> getProjectDetails(int id);

  Future<ProjectContributionModel> contributeToProject({
    required int projectId,
    required double amount,
  });
}

class ProjectRemoteDataSourceImpl implements ProjectRemoteDataSource {
  final ApiClient apiClient;

  ProjectRemoteDataSourceImpl({required this.apiClient});

  @override
  Future<List<ProjectModel>> getProjects({
    int page = 1,
    String? status,
    String? search,
    int? departmentId,
    int? ministryId,
  }) async {
    final Map<String, dynamic> queryParams = {
      'page': page,
      if (status != null && status.isNotEmpty) 'status': status,
      if (search != null && search.isNotEmpty) 'search': search,
      'department_id': ?departmentId,
      'ministry_id': ?ministryId,
    };

    final response = await apiClient.get(
      ApiEndpoints.projects,
      queryParameters: queryParams,
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is List) {
      return (data['data'] as List)
          .whereType<Map<String, dynamic>>()
          .map((item) => ProjectModel.fromJson(item))
          .toList();
    }

    return [];
  }

  @override
  Future<ProjectModel> getProjectDetails(int id) async {
    final response = await apiClient.get(ApiEndpoints.projectDetails(id));
    final data = response.data;

    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return ProjectModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('استجابة غير صالحة لتفاصيل المشروع التنموي.');
  }

  @override
  Future<ProjectContributionModel> contributeToProject({
    required int projectId,
    required double amount,
  }) async {
    final response = await apiClient.post(
      ApiEndpoints.contributeProject(projectId),
      data: {'amount': amount},
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return ProjectContributionModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('فشل تسجيل المساهمة في المشروع.');
  }
}
