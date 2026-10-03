import '../../../complaints/data/models/complaint_model.dart';
import '../../domain/entities/field_assignment_entity.dart';

/// Data model for field work assignments including nested complaint data.
/// Parses the backend's FieldAssignmentResource JSON which eager-loads
/// complaint.category, complaint.currentDepartment, worker, and assignedBy.
class FieldWorkAssignmentModel {
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
  final ComplaintModel? complaint;

  const FieldWorkAssignmentModel({
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

  factory FieldWorkAssignmentModel.fromJson(Map<String, dynamic> json) {
    int? parseNullableInt(dynamic value) {
      if (value == null) return null;
      if (value is int) return value;
      return int.tryParse(value.toString());
    }

    DateTime? parseDateTime(dynamic value) {
      if (value == null) return null;
      return DateTime.tryParse(value.toString());
    }

    // Parse nested worker
    String? parsedWorkerName;
    int? parsedWorkerId;
    if (json['worker'] is Map<String, dynamic>) {
      final worker = json['worker'] as Map<String, dynamic>;
      parsedWorkerName = worker['name']?.toString();
      parsedWorkerId = parseNullableInt(worker['id']);
    }

    // Parse nested assigned_by
    String? parsedAssignedByName;
    if (json['assigned_by'] is Map<String, dynamic>) {
      parsedAssignedByName = json['assigned_by']['name']?.toString();
    }

    // Parse nested complaint
    ComplaintModel? parsedComplaint;
    if (json['complaint'] is Map<String, dynamic>) {
      parsedComplaint =
          ComplaintModel.fromJson(json['complaint'] as Map<String, dynamic>);
    }

    return FieldWorkAssignmentModel(
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
      complaint: parsedComplaint,
    );
  }

  FieldWorkAssignmentEntity toEntity() {
    return FieldWorkAssignmentEntity(
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
      complaint: complaint?.toEntity(),
    );
  }
}
