import '../../domain/entities/complaint_entity.dart';
import 'attachment_model.dart';
import 'category_model.dart';
import 'field_assignment_model.dart';
import 'timeline_model.dart';

class ComplaintModel {
  final int id;
  final String title;
  final String description;
  final String status;
  final String priority;
  final double latitude;
  final double longitude;
  final int? duplicateOfId;
  final String? citizenName;
  final String? citizenPhone;
  final CategoryModel? category;
  final String? departmentName;
  final String? ministryName;
  final List<AttachmentModel> attachments;
  final List<TimelineModel> timeline;
  final List<FieldAssignmentModel> fieldAssignments;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  const ComplaintModel({
    required this.id,
    required this.title,
    required this.description,
    required this.status,
    required this.priority,
    required this.latitude,
    required this.longitude,
    this.duplicateOfId,
    this.citizenName,
    this.citizenPhone,
    this.category,
    this.departmentName,
    this.ministryName,
    this.attachments = const [],
    this.timeline = const [],
    this.fieldAssignments = const [],
    this.createdAt,
    this.updatedAt,
  });

  factory ComplaintModel.fromJson(Map<String, dynamic> json) {
    double parseCoord(dynamic value) {
      if (value is num) return value.toDouble();
      return double.tryParse(value?.toString() ?? '0') ?? 0.0;
    }

    String? parsedCitizenName;
    String? parsedCitizenPhone;
    if (json['citizen'] is Map<String, dynamic>) {
      parsedCitizenName = json['citizen']['name']?.toString();
      parsedCitizenPhone = json['citizen']['phone']?.toString();
    }

    CategoryModel? parsedCategory;
    if (json['category'] is Map<String, dynamic>) {
      parsedCategory = CategoryModel.fromJson(json['category'] as Map<String, dynamic>);
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

    List<AttachmentModel> parsedAttachments = [];
    if (json['attachments'] is List) {
      parsedAttachments = (json['attachments'] as List)
          .whereType<Map<String, dynamic>>()
          .map((att) => AttachmentModel.fromJson(att))
          .toList();
    }

    List<TimelineModel> parsedTimeline = [];
    if (json['timeline'] is List) {
      parsedTimeline = (json['timeline'] as List)
          .whereType<Map<String, dynamic>>()
          .map((t) => TimelineModel.fromJson(t))
          .toList();
    }

    List<FieldAssignmentModel> parsedAssignments = [];
    if (json['field_assignments'] is List) {
      parsedAssignments = (json['field_assignments'] as List)
          .whereType<Map<String, dynamic>>()
          .map((a) => FieldAssignmentModel.fromJson(a))
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

    return ComplaintModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      status: json['status']?.toString() ?? 'new',
      priority: json['priority']?.toString() ?? 'medium',
      latitude: parseCoord(json['latitude']),
      longitude: parseCoord(json['longitude']),
      duplicateOfId: json['duplicate_of_id'] is int
          ? json['duplicate_of_id']
          : (json['duplicate_of_id'] != null ? int.tryParse(json['duplicate_of_id'].toString()) : null),
      citizenName: parsedCitizenName,
      citizenPhone: parsedCitizenPhone,
      category: parsedCategory,
      departmentName: parsedDeptName,
      ministryName: parsedMinistryName,
      attachments: parsedAttachments,
      timeline: parsedTimeline,
      fieldAssignments: parsedAssignments,
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
      'priority': priority,
      'latitude': latitude,
      'longitude': longitude,
      'duplicate_of_id': duplicateOfId,
      if (citizenName != null) 'citizen': {'name': citizenName, 'phone': citizenPhone},
      'category': category?.toJson(),
      'department': {
        'name': departmentName,
        'ministry': {'name': ministryName},
      },
      'attachments': attachments.map((a) => a.toJson()).toList(),
      'timeline': timeline.map((t) => t.toJson()).toList(),
      'field_assignments': fieldAssignments.map((f) => f.toJson()).toList(),
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }

  ComplaintEntity toEntity() {
    return ComplaintEntity(
      id: id,
      title: title,
      description: description,
      status: status,
      priority: priority,
      latitude: latitude,
      longitude: longitude,
      duplicateOfId: duplicateOfId,
      citizenName: citizenName,
      citizenPhone: citizenPhone,
      category: category?.toEntity(),
      departmentName: departmentName,
      ministryName: ministryName,
      attachments: attachments.map((a) => a.toEntity()).toList(),
      timeline: timeline.map((t) => t.toEntity()).toList(),
      fieldAssignments: fieldAssignments.map((f) => f.toEntity()).toList(),
      createdAt: createdAt,
      updatedAt: updatedAt,
    );
  }
}
