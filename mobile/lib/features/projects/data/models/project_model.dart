import '../../domain/entities/project_entity.dart';
import 'project_contribution_model.dart';
import 'project_phase_model.dart';

class ProjectModel {
  final int id;
  final String title;
  final String description;
  final String status;
  final double targetAmount;
  final double currentAmount;
  final double progressPercentage;
  final double? latitude;
  final double? longitude;
  final String? departmentName;
  final String? ministryName;
  final String? createdByName;
  final List<ProjectPhaseModel> phases;
  final List<ProjectContributionModel> contributions;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  const ProjectModel({
    required this.id,
    required this.title,
    required this.description,
    required this.status,
    required this.targetAmount,
    required this.currentAmount,
    required this.progressPercentage,
    this.latitude,
    this.longitude,
    this.departmentName,
    this.ministryName,
    this.createdByName,
    this.phases = const [],
    this.contributions = const [],
    this.createdAt,
    this.updatedAt,
  });

  factory ProjectModel.fromJson(Map<String, dynamic> json) {
    double parseDouble(dynamic value, [double defaultValue = 0.0]) {
      if (value is num) return value.toDouble();
      return double.tryParse(value?.toString() ?? '') ?? defaultValue;
    }

    double? parseNullableDouble(dynamic value) {
      if (value == null) return null;
      if (value is num) return value.toDouble();
      return double.tryParse(value.toString());
    }

    String? parsedDeptName;
    String? parsedMinistryName;
    if (json['department'] is Map<String, dynamic>) {
      final dept = json['department'] as Map<String, dynamic>;
      parsedDeptName = dept['name']?.toString();
      if (dept['ministry'] is Map<String, dynamic>) {
        parsedMinistryName = dept['ministry']['name']?.toString();
      }
    }

    String? parsedCreatedByName;
    if (json['created_by'] is Map<String, dynamic>) {
      parsedCreatedByName = json['created_by']['name']?.toString();
    }

    List<ProjectPhaseModel> parsedPhases = [];
    if (json['phases'] is List) {
      parsedPhases = (json['phases'] as List)
          .whereType<Map<String, dynamic>>()
          .map((p) => ProjectPhaseModel.fromJson(p))
          .toList();
    }

    List<ProjectContributionModel> parsedContributions = [];
    if (json['contributions'] is List) {
      parsedContributions = (json['contributions'] as List)
          .whereType<Map<String, dynamic>>()
          .map((c) => ProjectContributionModel.fromJson(c))
          .toList();
    }

    DateTime? parsedCreatedAt;
    if (json['created_at'] != null) {
      parsedCreatedAt = DateTime.tryParse(json['created_at'].toString());
    }

    DateTime? parsedUpdatedAt;
    if (json['updated_at'] != null) {
      parsedUpdatedAt = DateTime.tryParse(json['updated_at'].toString());
    }

    return ProjectModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      status: json['status']?.toString() ?? 'active',
      targetAmount: parseDouble(json['target_amount']),
      currentAmount: parseDouble(json['current_amount']),
      progressPercentage: parseDouble(json['progress_percentage']),
      latitude: parseNullableDouble(json['latitude']),
      longitude: parseNullableDouble(json['longitude']),
      departmentName: parsedDeptName,
      ministryName: parsedMinistryName,
      createdByName: parsedCreatedByName,
      phases: parsedPhases,
      contributions: parsedContributions,
      createdAt: parsedCreatedAt,
      updatedAt: parsedUpdatedAt,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'status': status,
      'target_amount': targetAmount,
      'current_amount': currentAmount,
      'progress_percentage': progressPercentage,
      'latitude': latitude,
      'longitude': longitude,
      if (departmentName != null)
        'department': {
          'name': departmentName,
          if (ministryName != null) 'ministry': {'name': ministryName},
        },
      if (createdByName != null) 'created_by': {'name': createdByName},
      'phases': phases.map((p) => p.toJson()).toList(),
      'contributions': contributions.map((c) => c.toJson()).toList(),
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }

  ProjectEntity toEntity() {
    return ProjectEntity(
      id: id,
      title: title,
      description: description,
      status: status,
      targetAmount: targetAmount,
      currentAmount: currentAmount,
      progressPercentage: progressPercentage,
      latitude: latitude,
      longitude: longitude,
      departmentName: departmentName,
      ministryName: ministryName,
      createdByName: createdByName,
      phases: phases.map((p) => p.toEntity()).toList(),
      contributions: contributions.map((c) => c.toEntity()).toList(),
      createdAt: createdAt,
      updatedAt: updatedAt,
    );
  }
}
