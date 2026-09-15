import '../../domain/entities/project_phase_entity.dart';

class ProjectPhaseModel {
  final int id;
  final int projectId;
  final String name;
  final String? description;
  final int completionPercentage;
  final DateTime? startDate;
  final DateTime? endDate;
  final String status;

  const ProjectPhaseModel({
    required this.id,
    required this.projectId,
    required this.name,
    this.description,
    this.completionPercentage = 0,
    this.startDate,
    this.endDate,
    required this.status,
  });

  factory ProjectPhaseModel.fromJson(Map<String, dynamic> json) {
    DateTime? parseDate(dynamic value) {
      if (value == null) return null;
      return DateTime.tryParse(value.toString());
    }

    return ProjectPhaseModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      projectId: json['project_id'] is int
          ? json['project_id']
          : int.parse(json['project_id'].toString()),
      name: json['name'] ?? '',
      description: json['description']?.toString(),
      completionPercentage: json['completion_percentage'] is int
          ? json['completion_percentage']
          : (json['completion_percentage'] != null
              ? int.tryParse(json['completion_percentage'].toString()) ?? 0
              : 0),
      startDate: parseDate(json['start_date']),
      endDate: parseDate(json['end_date']),
      status: json['status']?.toString() ?? 'pending',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'project_id': projectId,
      'name': name,
      'description': description,
      'completion_percentage': completionPercentage,
      'start_date': startDate?.toIso8601String().split('T').first,
      'end_date': endDate?.toIso8601String().split('T').first,
      'status': status,
    };
  }

  ProjectPhaseEntity toEntity() {
    return ProjectPhaseEntity(
      id: id,
      projectId: projectId,
      name: name,
      description: description,
      completionPercentage: completionPercentage,
      startDate: startDate,
      endDate: endDate,
      status: status,
    );
  }
}
