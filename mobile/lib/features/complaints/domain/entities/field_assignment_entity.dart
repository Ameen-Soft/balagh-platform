class FieldAssignmentEntity {
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

  const FieldAssignmentEntity({
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
  });

  bool get isPending => status == 'pending';
  bool get isAccepted => status == 'accepted';
  bool get isInProgress => status == 'in_progress';
  bool get isCompleted => status == 'completed';
}
