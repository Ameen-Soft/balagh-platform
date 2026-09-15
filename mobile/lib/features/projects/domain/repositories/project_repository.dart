import '../entities/project_contribution_entity.dart';
import '../entities/project_entity.dart';

abstract class ProjectRepository {
  Future<List<ProjectEntity>> getProjects({
    int page = 1,
    String? status,
    String? search,
    int? departmentId,
    int? ministryId,
  });

  Future<ProjectEntity> getProjectDetails(int id);

  Future<ProjectContributionEntity> contributeToProject({
    required int projectId,
    required double amount,
  });
}
