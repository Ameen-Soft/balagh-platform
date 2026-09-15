class ProjectContributionEntity {
  final int id;
  final int projectId;
  final double amount;
  final String? contributorName;
  final DateTime? createdAt;

  const ProjectContributionEntity({
    required this.id,
    required this.projectId,
    required this.amount,
    this.contributorName,
    this.createdAt,
  });
}
