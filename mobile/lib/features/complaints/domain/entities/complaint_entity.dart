import 'attachment_entity.dart';
import 'category_entity.dart';
import 'field_assignment_entity.dart';
import 'timeline_entity.dart';

class ComplaintEntity {
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
  final CategoryEntity? category;
  final String? departmentName;
  final String? ministryName;
  final List<AttachmentEntity> attachments;
  final List<TimelineEntity> timeline;
  final List<FieldAssignmentEntity> fieldAssignments;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  final int likesCount;
  final bool isLikedByMe;
  final bool isPublic;

  const ComplaintEntity({
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
    this.likesCount = 0,
    this.isLikedByMe = false,
    this.isPublic = false,
  });

  ComplaintEntity copyWith({
    int? id,
    String? title,
    String? description,
    String? status,
    String? priority,
    double? latitude,
    double? longitude,
    int? duplicateOfId,
    String? citizenName,
    String? citizenPhone,
    CategoryEntity? category,
    String? departmentName,
    String? ministryName,
    List<AttachmentEntity>? attachments,
    List<TimelineEntity>? timeline,
    List<FieldAssignmentEntity>? fieldAssignments,
    DateTime? createdAt,
    DateTime? updatedAt,
    int? likesCount,
    bool? isLikedByMe,
    bool? isPublic,
  }) {
    return ComplaintEntity(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      status: status ?? this.status,
      priority: priority ?? this.priority,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      duplicateOfId: duplicateOfId ?? this.duplicateOfId,
      citizenName: citizenName ?? this.citizenName,
      citizenPhone: citizenPhone ?? this.citizenPhone,
      category: category ?? this.category,
      departmentName: departmentName ?? this.departmentName,
      ministryName: ministryName ?? this.ministryName,
      attachments: attachments ?? this.attachments,
      timeline: timeline ?? this.timeline,
      fieldAssignments: fieldAssignments ?? this.fieldAssignments,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      likesCount: likesCount ?? this.likesCount,
      isLikedByMe: isLikedByMe ?? this.isLikedByMe,
      isPublic: isPublic ?? this.isPublic,
    );
  }

  String get complaintNumber => 'CMP-${id.toString().padLeft(6, '0')}';

  String get statusArabic {
    switch (status) {
      case 'new':
      case 'submitted':
        return 'جديد وارد';
      case 'under_review':
        return 'قيد المراجعة';
      case 'assigned':
        return 'مسند للميدان';
      case 'in_progress':
        return 'قيد التنفيذ';
      case 'resolved':
        return 'تم الإنجاز';
      case 'rejected':
        return 'مرفوض';
      case 'closed':
        return 'مغلق';
      case 'reopened':
        return 'أُعيد فتحها';
      default:
        return status;
    }
  }

  String get priorityArabic {
    switch (priority) {
      case 'urgent':
        return 'طارئ';
      case 'high':
        return 'عالي';
      case 'medium':
        return 'متوسط';
      case 'low':
        return 'منخفض';
      default:
        return priority;
    }
  }
}
