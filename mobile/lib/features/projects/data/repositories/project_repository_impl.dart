import '../../domain/entities/project_contribution_entity.dart';
import '../../domain/entities/project_entity.dart';
import '../../domain/repositories/project_repository.dart';
import '../datasources/project_remote_data_source.dart';

class ProjectRepositoryImpl implements ProjectRepository {
  final ProjectRemoteDataSource remoteDataSource;

  ProjectRepositoryImpl({required this.remoteDataSource});

  @override
  Future<List<ProjectEntity>> getProjects({
    int page = 1,
    String? status,
    String? search,
    int? departmentId,
    int? ministryId,
  }) async {
    final models = await remoteDataSource.getProjects(
      page: page,
      status: status,
      search: search,
      departmentId: departmentId,
      ministryId: ministryId,
    );
    return models.map((m) => m.toEntity()).toList();
  }

  @override
  Future<ProjectEntity> getProjectDetails(int id) async {
    final model = await remoteDataSource.getProjectDetails(id);
    return model.toEntity();
  }

  @override
  Future<ProjectContributionEntity> contributeToProject({
    required int projectId,
    required double amount,
  }) async {
    final model = await remoteDataSource.contributeToProject(
      projectId: projectId,
      amount: amount,
    );
    return model.toEntity();
  }
}
