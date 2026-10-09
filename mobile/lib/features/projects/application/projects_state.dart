import '../domain/entities/project_entity.dart';

enum ProjectsStatus { initial, loading, loaded, error }

class ProjectsState {
  final ProjectsStatus status;
  final List<ProjectEntity> projects;
  final String? errorMessage;
  final String? selectedStatus;
  final String searchQuery;
  final int currentPage;
  final bool hasReachedMax;
  final bool isLoadingMore;

  const ProjectsState({
    this.status = ProjectsStatus.initial,
    this.projects = const [],
    this.errorMessage,
    this.selectedStatus,
    this.searchQuery = '',
    this.currentPage = 1,
    this.hasReachedMax = false,
    this.isLoadingMore = false,
  });

  bool get isLoading => status == ProjectsStatus.loading;
  bool get isLoaded => status == ProjectsStatus.loaded;
  bool get isError => status == ProjectsStatus.error;
  bool get isEmpty => isLoaded && projects.isEmpty;

  ProjectsState copyWith({
    ProjectsStatus? status,
    List<ProjectEntity>? projects,
    String? errorMessage,
    String? selectedStatus,
    bool clearStatus = false,
    String? searchQuery,
    int? currentPage,
    bool? hasReachedMax,
    bool? isLoadingMore,
  }) {
    return ProjectsState(
      status: status ?? this.status,
      projects: projects ?? this.projects,
      errorMessage: errorMessage,
      selectedStatus: clearStatus ? null : (selectedStatus ?? this.selectedStatus),
      searchQuery: searchQuery ?? this.searchQuery,
      currentPage: currentPage ?? this.currentPage,
      hasReachedMax: hasReachedMax ?? this.hasReachedMax,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
    );
  }
}
