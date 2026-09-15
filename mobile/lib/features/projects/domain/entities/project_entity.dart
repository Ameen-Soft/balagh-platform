import 'project_contribution_entity.dart';
import 'project_phase_entity.dart';

class ProjectEntity {
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
  final List<ProjectPhaseEntity> phases;
  final List<ProjectContributionEntity> contributions;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  const ProjectEntity({
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

  String get projectNumber => 'PRJ-${id.toString().padLeft(6, '0')}';

  String get statusArabic {
    switch (status) {
      case 'draft':
        return 'مسودة';
      case 'published':
        return 'منشور';
      case 'active':
        return 'نشط';
      case 'in_progress':
        return 'قيد التنفيذ';
      case 'completed':
        return 'مكتمل';
      case 'suspended':
        return 'معلق';
      default:
        return status;
    }
  }

  double get remainingAmount => (targetAmount - currentAmount).clamp(0.0, double.infinity);
  bool get isFullyFunded => currentAmount >= targetAmount;
}
