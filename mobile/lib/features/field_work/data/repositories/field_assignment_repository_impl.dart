import '../../domain/entities/field_assignment_entity.dart';
import '../../domain/entities/location_verification_result.dart';
import '../../domain/repositories/field_assignment_repository.dart';
import '../datasources/field_assignment_remote_data_source.dart';

class FieldAssignmentRepositoryImpl implements FieldAssignmentRepository {
  final FieldAssignmentRemoteDataSource remoteDataSource;

  FieldAssignmentRepositoryImpl({required this.remoteDataSource});

  @override
  Future<List<FieldWorkAssignmentEntity>> getAssignments({
    String? status,
  }) async {
    final models = await remoteDataSource.getAssignments(status: status);
    return models.map((m) => m.toEntity()).toList();
  }

  @override
  Future<FieldWorkAssignmentEntity> acceptAssignment(int assignmentId) async {
    final model = await remoteDataSource.acceptAssignment(assignmentId);
    return model.toEntity();
  }

  @override
  Future<LocationVerificationResult> verifyLocation({
    required int assignmentId,
    required double latitude,
    required double longitude,
  }) async {
    final json = await remoteDataSource.verifyLocation(
      assignmentId: assignmentId,
      latitude: latitude,
      longitude: longitude,
    );
    return LocationVerificationResult.fromJson(json);
  }

  @override
  Future<FieldWorkAssignmentEntity> startAssignment(int assignmentId) async {
    final model = await remoteDataSource.startAssignment(assignmentId);
    return model.toEntity();
  }

  @override
  Future<FieldWorkAssignmentEntity> completeAssignment({
    required int assignmentId,
    required String report,
    required double latitude,
    required double longitude,
    List<String>? attachmentPaths,
  }) async {
    final model = await remoteDataSource.completeAssignment(
      assignmentId: assignmentId,
      report: report,
      latitude: latitude,
      longitude: longitude,
      attachmentPaths: attachmentPaths,
    );
    return model.toEntity();
  }
}
