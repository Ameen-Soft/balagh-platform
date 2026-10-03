import '../entities/field_assignment_entity.dart';
import '../entities/location_verification_result.dart';

/// Domain contract for field work operations.
abstract class FieldAssignmentRepository {
  /// List assignments for the current authenticated field worker.
  Future<List<FieldWorkAssignmentEntity>> getAssignments({String? status});

  /// Accept a pending assignment.
  Future<FieldWorkAssignmentEntity> acceptAssignment(int assignmentId);

  /// Verify field worker's geographic proximity to the complaint site.
  Future<LocationVerificationResult> verifyLocation({
    required int assignmentId,
    required double latitude,
    required double longitude,
  });

  /// Start a field assignment (requires geo-verification first).
  Future<FieldWorkAssignmentEntity> startAssignment(int assignmentId);

  /// Complete a field assignment with report, location, and evidence photos.
  Future<FieldWorkAssignmentEntity> completeAssignment({
    required int assignmentId,
    required String report,
    required double latitude,
    required double longitude,
    List<String>? attachmentPaths,
  });
}
