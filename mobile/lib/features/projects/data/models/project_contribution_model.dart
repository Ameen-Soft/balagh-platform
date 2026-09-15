import '../../domain/entities/project_contribution_entity.dart';

class ProjectContributionModel {
  final int id;
  final int projectId;
  final double amount;
  final String? contributorName;
  final DateTime? createdAt;

  const ProjectContributionModel({
    required this.id,
    required this.projectId,
    required this.amount,
    this.contributorName,
    this.createdAt,
  });

  factory ProjectContributionModel.fromJson(Map<String, dynamic> json) {
    double parseAmount(dynamic value) {
      if (value is num) return value.toDouble();
      return double.tryParse(value?.toString() ?? '0') ?? 0.0;
    }

    String? parsedContributorName;
    if (json['contributor'] is Map<String, dynamic>) {
      parsedContributorName = json['contributor']['name']?.toString();
    }

    DateTime? parsedCreatedAt;
    if (json['created_at'] != null) {
      parsedCreatedAt = DateTime.tryParse(json['created_at'].toString());
    }

    return ProjectContributionModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      projectId: json['project_id'] is int
          ? json['project_id']
          : int.parse(json['project_id'].toString()),
      amount: parseAmount(json['amount']),
      contributorName: parsedContributorName,
      createdAt: parsedCreatedAt,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'project_id': projectId,
      'amount': amount,
      if (contributorName != null) 'contributor': {'name': contributorName},
      'created_at': createdAt?.toIso8601String(),
    };
  }

  ProjectContributionEntity toEntity() {
    return ProjectContributionEntity(
      id: id,
      projectId: projectId,
      amount: amount,
      contributorName: contributorName,
      createdAt: createdAt,
    );
  }
}
