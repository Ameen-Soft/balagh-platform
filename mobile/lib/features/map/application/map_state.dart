
import '../../complaints/domain/entities/complaint_entity.dart';
import '../../projects/domain/entities/project_entity.dart';

class MapState {
  final bool isLoading;
  final String? error;
  final List<ComplaintEntity> complaints;
  final List<ProjectEntity> projects;
  final bool showComplaints;
  final bool showProjects;
  final String? selectedStatus;

  MapState({
    this.isLoading = false,
    this.error,
    this.complaints = const [],
    this.projects = const [],
    this.showComplaints = true,
    this.showProjects = true,
    this.selectedStatus,
  });

  MapState copyWith({
    bool? isLoading,
    String? error,
    List<ComplaintEntity>? complaints,
    List<ProjectEntity>? projects,
    bool? showComplaints,
    bool? showProjects,
    String? selectedStatus,
    bool clearStatus = false,
  }) {
    return MapState(
      isLoading: isLoading ?? this.isLoading,
      error: error ?? this.error,
      complaints: complaints ?? this.complaints,
      projects: projects ?? this.projects,
      showComplaints: showComplaints ?? this.showComplaints,
      showProjects: showProjects ?? this.showProjects,
      selectedStatus: clearStatus ? null : (selectedStatus ?? this.selectedStatus),
    );
  }
}
