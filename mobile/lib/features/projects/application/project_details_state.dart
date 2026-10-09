import '../domain/entities/project_contribution_entity.dart';
import '../domain/entities/project_entity.dart';

enum ProjectDetailsStatus { initial, loading, loaded, error }
enum ContributionStatus { idle, submitting, success, error }

class ProjectDetailsState {
  final ProjectDetailsStatus status;
  final ProjectEntity? project;
  final String? errorMessage;

  // Simulated contribution state
  final ContributionStatus contributionStatus;
  final ProjectContributionEntity? latestContribution;
  final String? contributionErrorMessage;

  const ProjectDetailsState({
    this.status = ProjectDetailsStatus.initial,
    this.project,
    this.errorMessage,
    this.contributionStatus = ContributionStatus.idle,
    this.latestContribution,
    this.contributionErrorMessage,
  });

  bool get isLoading => status == ProjectDetailsStatus.loading;
  bool get isLoaded => status == ProjectDetailsStatus.loaded;
  bool get isError => status == ProjectDetailsStatus.error;

  bool get isSubmittingContribution => contributionStatus == ContributionStatus.submitting;
  bool get isContributionSuccess => contributionStatus == ContributionStatus.success;
  bool get isContributionError => contributionStatus == ContributionStatus.error;

  ProjectDetailsState copyWith({
    ProjectDetailsStatus? status,
    ProjectEntity? project,
    String? errorMessage,
    ContributionStatus? contributionStatus,
    ProjectContributionEntity? latestContribution,
    String? contributionErrorMessage,
  }) {
    return ProjectDetailsState(
      status: status ?? this.status,
      project: project ?? this.project,
      errorMessage: errorMessage,
      contributionStatus: contributionStatus ?? this.contributionStatus,
      latestContribution: latestContribution ?? this.latestContribution,
      contributionErrorMessage: contributionErrorMessage,
    );
  }
}
