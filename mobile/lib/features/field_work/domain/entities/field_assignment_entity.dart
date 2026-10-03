import '../../../complaints/domain/entities/complaint_entity.dart';

/// Extended entity for field worker views — includes complaint context.
class FieldWorkAssignmentEntity {
  final int id;
  final int complaintId;
  final int? workerId;
  final String? workerName;
  final String? assignedByName;
  final String status;
  final DateTime? startedAt;
  final DateTime? completedAt;
  final String? notes;
  final DateTime? createdAt;
  final ComplaintEntity? complaint;

  const FieldWorkAssignmentEntity({
    required this.id,
    required this.complaintId,
    this.workerId,
    this.workerName,
    this.assignedByName,
    required this.status,
    this.startedAt,
    this.completedAt,
    this.notes,
    this.createdAt,
    this.complaint,
  });

  bool get isPending => status == 'pending';
  bool get isAccepted => status == 'accepted';
  bool get isInProgress => status == 'in_progress';
  bool get isCompleted => status == 'completed';
  bool get isFailed => status == 'failed';

  String get statusArabic {
    switch (status) {
      case 'pending':
        return 'بانتظار القبول';
      case 'accepted':
        return 'تم القبول';
      case 'in_progress':
        return 'قيد التنفيذ';
      case 'completed':
        return 'مكتمل';
      case 'failed':
        return 'فشل';
      default:
        return status;
    }
  }
}
