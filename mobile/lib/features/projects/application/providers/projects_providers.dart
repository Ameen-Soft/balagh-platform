import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../auth/application/providers.dart';
import '../../data/datasources/project_remote_data_source.dart';
import '../../data/repositories/project_repository_impl.dart';
import '../../domain/repositories/project_repository.dart';
import '../project_details_notifier.dart';
import '../project_details_state.dart';
import '../projects_notifier.dart';
import '../projects_state.dart';

/// Remote data source provider for projects.
final projectRemoteDataSourceProvider = Provider<ProjectRemoteDataSource>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return ProjectRemoteDataSourceImpl(apiClient: apiClient);
});

/// Repository provider for projects.
final projectRepositoryProvider = Provider<ProjectRepository>((ref) {
  final remoteDataSource = ref.watch(projectRemoteDataSourceProvider);
  return ProjectRepositoryImpl(remoteDataSource: remoteDataSource);
});

/// Main projects list notifier provider.
final projectsNotifierProvider =
    NotifierProvider<ProjectsNotifier, ProjectsState>(ProjectsNotifier.new);

/// Project details notifier provider.
final projectDetailsNotifierProvider =
    NotifierProvider<ProjectDetailsNotifier, ProjectDetailsState>(
  ProjectDetailsNotifier.new,
);
