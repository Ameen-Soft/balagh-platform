class ProjectPhaseEntity {
  final int id;
  final int projectId;
  final String name;
  final String? description;
  final int completionPercentage;
  final DateTime? startDate;
  final DateTime? endDate;
  final String status;

  const ProjectPhaseEntity({
    required this.id,
    required this.projectId,
    required this.name,
    this.description,
    this.completionPercentage = 0,
    this.startDate,
    this.endDate,
    required this.status,
  });

  bool get isInProgress =>
      status == 'in_progress' || (completionPercentage > 0 && completionPercentage < 100);
  bool get isCompleted => completionPercentage >= 100 || status == 'completed';
}
