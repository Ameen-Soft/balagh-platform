import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../domain/entities/project_entity.dart';
import '../domain/repositories/project_repository.dart';
import 'projects_state.dart';
import 'providers/projects_providers.dart';

class ProjectsNotifier extends Notifier<ProjectsState> {
  late final ProjectRepository _repository;

  @override
  ProjectsState build() {
    _repository = ref.watch(projectRepositoryProvider);
    Future.microtask(() => loadProjects());
    return const ProjectsState();
  }

  Future<void> loadProjects({bool refresh = false}) async {
    if (state.isLoading && !refresh) return;

    state = state.copyWith(
      status: ProjectsStatus.loading,
      currentPage: 1,
      hasReachedMax: false,
      errorMessage: null,
    );

    try {
      final projects = await _repository.getProjects(
        page: 1,
        status: state.selectedStatus,
        search: state.searchQuery.isNotEmpty ? state.searchQuery : null,
      );

      state = state.copyWith(
        status: ProjectsStatus.loaded,
        projects: projects,
        currentPage: 1,
        hasReachedMax: projects.length < 15,
        errorMessage: null,
      );
    } catch (e) {
      state = state.copyWith(
        status: ProjectsStatus.error,
        errorMessage: e.toString().replaceFirst('Exception: ', ''),
      );
    }
  }

  Future<void> refresh() async {
    await loadProjects(refresh: true);
  }

  Future<void> loadMore() async {
    if (state.hasReachedMax || state.isLoadingMore || state.isLoading) return;

    state = state.copyWith(isLoadingMore: true);
    final nextPage = state.currentPage + 1;

    try {
      final newProjects = await _repository.getProjects(
        page: nextPage,
        status: state.selectedStatus,
        search: state.searchQuery.isNotEmpty ? state.searchQuery : null,
      );

      state = state.copyWith(
        isLoadingMore: false,
        currentPage: nextPage,
        projects: [...state.projects, ...newProjects],
        hasReachedMax: newProjects.isEmpty || newProjects.length < 15,
      );
    } catch (_) {
      state = state.copyWith(isLoadingMore: false);
    }
  }

  void filterByStatus(String? status) {
    if (state.selectedStatus == status) return;
    state = state.copyWith(selectedStatus: status, clearStatus: status == null);
    loadProjects(refresh: true);
  }

  void search(String query) {
    if (state.searchQuery == query.trim()) return;
    state = state.copyWith(searchQuery: query.trim());
    loadProjects(refresh: true);
  }

  void updateProjectLocally(ProjectEntity updatedProject) {
    final updatedList = state.projects.map((p) {
      return p.id == updatedProject.id ? updatedProject : p;
    }).toList();
    state = state.copyWith(projects: updatedList);
  }
}
