import '../../domain/entities/field_assignment_entity.dart';

class FieldAssignmentModel {
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

  const FieldAssignmentModel({
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

  factory FieldAssignmentModel.fromJson(Map<String, dynamic> json) {
    int? parseNullableInt(dynamic value) {
      if (value == null) return null;
      if (value is int) return value;
      return int.tryParse(value.toString());
    }

    DateTime? parseDateTime(dynamic value) {
      if (value == null) return null;
      return DateTime.tryParse(value.toString());
    }

    String? parsedWorkerName;
    int? parsedWorkerId;
    if (json['worker'] is Map<String, dynamic>) {
      final worker = json['worker'] as Map<String, dynamic>;
      parsedWorkerName = worker['name']?.toString();
      parsedWorkerId = parseNullableInt(worker['id']);
    }

    String? parsedAssignedByName;
    if (json['assigned_by'] is Map<String, dynamic>) {
      parsedAssignedByName = json['assigned_by']['name']?.toString();
    }

    return FieldAssignmentModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      complaintId: json['complaint_id'] is int
          ? json['complaint_id']
          : int.parse(json['complaint_id'].toString()),
      workerId: parsedWorkerId,
      workerName: parsedWorkerName,
      assignedByName: parsedAssignedByName,
      status: json['status']?.toString() ?? 'pending',
      startedAt: parseDateTime(json['started_at']),
      completedAt: parseDateTime(json['completed_at']),
      notes: json['notes']?.toString(),
      createdAt: parseDateTime(json['created_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'complaint_id': complaintId,
      if (workerName != null) 'worker': {'id': workerId, 'name': workerName},
      if (assignedByName != null) 'assigned_by': {'name': assignedByName},
      'status': status,
      'started_at': startedAt?.toIso8601String(),
      'completed_at': completedAt?.toIso8601String(),
      'notes': notes,
      'created_at': createdAt?.toIso8601String(),
    };
  }

  FieldAssignmentEntity toEntity() {
    return FieldAssignmentEntity(
      id: id,
      complaintId: complaintId,
      workerId: workerId,
      workerName: workerName,
      assignedByName: assignedByName,
      status: status,
      startedAt: startedAt,
      completedAt: completedAt,
      notes: notes,
      createdAt: createdAt,
    );
  }
}
