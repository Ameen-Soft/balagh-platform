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
  });

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
