import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../domain/entities/project_entity.dart';
import '../domain/repositories/project_repository.dart';
import 'project_details_state.dart';
import 'providers/projects_providers.dart';

class ProjectDetailsNotifier extends Notifier<ProjectDetailsState> {
  late final ProjectRepository _repository;
  int? _currentProjectId;

  @override
  ProjectDetailsState build() {
    _repository = ref.watch(projectRepositoryProvider);
    return const ProjectDetailsState();
  }

  Future<void> loadDetails(int projectId, {ProjectEntity? initialProject}) async {
    _currentProjectId = projectId;
    
    if (initialProject != null) {
      state = state.copyWith(
        status: ProjectDetailsStatus.loaded,
        project: initialProject,
        errorMessage: null,
      );
    } else {
      state = state.copyWith(
        status: ProjectDetailsStatus.loading,
        errorMessage: null,
      );
    }

    try {
      final details = await _repository.getProjectDetails(projectId);
      state = state.copyWith(
        status: ProjectDetailsStatus.loaded,
        project: details,
        errorMessage: null,
      );

      // Keep projects list updated as well
      ref.read(projectsNotifierProvider.notifier).updateProjectLocally(details);
    } catch (e) {
      if (state.project == null) {
        state = state.copyWith(
          status: ProjectDetailsStatus.error,
          errorMessage: e.toString().replaceFirst('Exception: ', ''),
        );
      }
    }
  }

  Future<bool> contribute({required double amount}) async {
    if (state.isSubmittingContribution || _currentProjectId == null) return false;

    state = state.copyWith(
      contributionStatus: ContributionStatus.submitting,
      contributionErrorMessage: null,
    );

    try {
      final contribution = await _repository.contributeToProject(
        projectId: _currentProjectId!,
        amount: amount,
      );

      // Reload fresh project details directly from backend to ensure accurate funding totals,
      // progress percentages, and status transitions
      final updatedProject = await _repository.getProjectDetails(_currentProjectId!);

      state = state.copyWith(
        contributionStatus: ContributionStatus.success,
        latestContribution: contribution,
        project: updatedProject,
        errorMessage: null,
      );

      // Reflect in list view
      ref.read(projectsNotifierProvider.notifier).updateProjectLocally(updatedProject);

      return true;
    } catch (e) {
      state = state.copyWith(
        contributionStatus: ContributionStatus.error,
        contributionErrorMessage: e.toString().replaceFirst('Exception: ', ''),
      );
      return false;
    }
  }

  void resetContributionStatus() {
    state = state.copyWith(
      contributionStatus: ContributionStatus.idle,
      contributionErrorMessage: null,
    );
  }
}


