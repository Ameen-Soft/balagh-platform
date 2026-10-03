import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import 'package:mobile/features/auth/domain/entities/user_entity.dart';
import 'package:mobile/features/field_work/application/field_work_state.dart';
import 'package:mobile/features/field_work/application/providers/field_work_providers.dart';
import 'package:mobile/features/field_work/data/models/field_assignment_model.dart';
import 'package:mobile/features/field_work/domain/entities/field_assignment_entity.dart';
import 'package:mobile/features/field_work/domain/entities/location_verification_result.dart';
import 'package:mobile/features/field_work/domain/repositories/field_assignment_repository.dart';

class MockFieldAssignmentRepository implements FieldAssignmentRepository {
  List<FieldWorkAssignmentEntity> mockAssignments = [];
  bool shouldThrow = false;
  String errorMessage = 'خطأ في الاتصال بالخادم';

  @override
  Future<List<FieldWorkAssignmentEntity>> getAssignments({String? status}) async {
    if (shouldThrow) throw NetworkException(errorMessage);
    if (status != null && status != 'all') {
      return mockAssignments.where((a) => a.status == status).toList();
    }
    return mockAssignments;
  }

  @override
  Future<FieldWorkAssignmentEntity> acceptAssignment(int assignmentId) async {
    if (shouldThrow) throw ServerException(errorMessage);
    return FieldWorkAssignmentEntity(
      id: assignmentId,
      complaintId: 101,
      status: 'accepted',
      workerName: 'أحمد الميداني',
      createdAt: DateTime(2026, 9, 20),
    );
  }

  @override
  Future<LocationVerificationResult> verifyLocation({
    required int assignmentId,
    required double latitude,
    required double longitude,
  }) async {
    if (shouldThrow) throw ServerException(errorMessage);
    final isWithin = latitude > 15.0 && latitude < 16.0;
    return LocationVerificationResult(
      assignmentId: assignmentId,
      complaintId: 101,
      workerLatitude: latitude,
      workerLongitude: longitude,
      complaintLatitude: 15.369445,
      complaintLongitude: 44.191006,
      distanceMeters: isWithin ? 120.5 : 850.0,
      allowedRadiusMeters: 500.0,
      isWithinRange: isWithin,
      message: isWithin
          ? 'الموظف الميداني متواجد داخل النطاق الجغرافي المحدد للبلاغ.'
          : 'الموظف الميداني يبعد 850 متراً عن موقع البلاغ (الحد الأقصى: 500م).',
    );
  }

  @override
  Future<FieldWorkAssignmentEntity> startAssignment(int assignmentId) async {
    if (shouldThrow) throw ServerException(errorMessage);
    return FieldWorkAssignmentEntity(
      id: assignmentId,
      complaintId: 101,
      status: 'in_progress',
      startedAt: DateTime.now(),
      workerName: 'أحمد الميداني',
      createdAt: DateTime(2026, 9, 20),
    );
  }

  @override
  Future<FieldWorkAssignmentEntity> completeAssignment({
    required int assignmentId,
    required String report,
    required double latitude,
    required double longitude,
    List<String>? attachmentPaths,
  }) async {
    if (shouldThrow) throw ServerException(errorMessage);
    return FieldWorkAssignmentEntity(
      id: assignmentId,
      complaintId: 101,
      status: 'completed',
      completedAt: DateTime.now(),
      notes: report,
      workerName: 'أحمد الميداني',
      createdAt: DateTime(2026, 9, 20),
    );
  }
}

void main() {
  group('Phase 05 — Field Worker Models & Parsing Tests', () {
    test('FieldWorkAssignmentModel parses Laravel FieldAssignmentResource response correctly', () {
      final json = {
        'id': 1,
        'complaint_id': 101,
        'worker': {
          'id': 5,
          'name': 'المهندس طارق',
          'email': 'fieldworker@balagh.gov.ye',
        },
        'assigned_by': {
          'id': 2,
          'name': 'إدارة الأشغال العامة',
        },
        'status': 'pending',
        'started_at': null,
        'completed_at': null,
        'notes': 'يرجى التوجه للموقع في شارع الستين',
        'created_at': '2026-09-20T10:00:00.000000Z',
      };

      final model = FieldWorkAssignmentModel.fromJson(json);

      expect(model.id, 1);
      expect(model.complaintId, 101);
      expect(model.workerId, 5);
      expect(model.workerName, 'المهندس طارق');
      expect(model.assignedByName, 'إدارة الأشغال العامة');
      expect(model.status, 'pending');
      expect(model.notes, 'يرجى التوجه للموقع في شارع الستين');
      expect(model.startedAt, isNull);
      expect(model.completedAt, isNull);

      final entity = model.toEntity();
      expect(entity.id, 1);
      expect(entity.isPending, true);
      expect(entity.isAccepted, false);
      expect(entity.isInProgress, false);
      expect(entity.isCompleted, false);
      expect(entity.statusArabic, 'بانتظار القبول');
    });

    test('LocationVerificationResult parses proximity payload correctly (Within range)', () {
      final json = {
        'assignment_id': 1,
        'complaint_id': 101,
        'worker_location': {
          'latitude': 15.369400,
          'longitude': 44.191000,
        },
        'complaint_location': {
          'latitude': 15.369445,
          'longitude': 44.191006,
        },
        'distance_meters': 5.2,
        'allowed_radius_meters': 500.0,
        'is_within_range': true,
        'message': 'الموظف الميداني متواجد داخل النطاق الجغرافي المحدد للبلاغ.',
      };

      final result = LocationVerificationResult.fromJson(json);

      expect(result.assignmentId, 1);
      expect(result.complaintId, 101);
      expect(result.workerLatitude, 15.369400);
      expect(result.workerLongitude, 44.191000);
      expect(result.complaintLatitude, 15.369445);
      expect(result.complaintLongitude, 44.191006);
      expect(result.distanceMeters, 5.2);
      expect(result.allowedRadiusMeters, 500.0);
      expect(result.isWithinRange, true);
    });

    test('LocationVerificationResult parses proximity payload correctly (Outside range)', () {
      final json = {
        'assignment_id': 1,
        'complaint_id': 101,
        'worker_location': {
          'latitude': 15.450000,
          'longitude': 44.250000,
        },
        'complaint_location': {
          'latitude': 15.369445,
          'longitude': 44.191006,
        },
        'distance_meters': 12500.0,
        'allowed_radius_meters': 500.0,
        'is_within_range': false,
        'message': 'الموظف الميداني يبعد 12500 متراً عن موقع البلاغ (الحد الأقصى: 500م).',
      };

      final result = LocationVerificationResult.fromJson(json);

      expect(result.isWithinRange, false);
      expect(result.distanceMeters, 12500.0);
      expect(result.message, contains('12500'));
    });
  });

  group('Phase 05 — FieldWorkNotifier & State Transitions', () {
    late MockFieldAssignmentRepository mockRepo;
    late ProviderContainer container;

    setUp(() {
      mockRepo = MockFieldAssignmentRepository();
      mockRepo.mockAssignments = [
        const FieldWorkAssignmentEntity(
          id: 1,
          complaintId: 101,
          status: 'pending',
          workerName: 'أحمد الميداني',
        ),
        const FieldWorkAssignmentEntity(
          id: 2,
          complaintId: 102,
          status: 'in_progress',
          workerName: 'أحمد الميداني',
        ),
      ];

      container = ProviderContainer(
        overrides: [
          fieldAssignmentRepositoryProvider.overrideWithValue(mockRepo),
        ],
      );
    });

    tearDown(() {
      container.dispose();
    });

    test('Initial state is initial and empty', () {
      final state = container.read(fieldWorkNotifierProvider);
      expect(state.status, FieldWorkStatus.initial);
      expect(state.assignments, isEmpty);
      expect(state.selectedFilter, 'all');
      expect(state.isLocationVerified, false);
    });

    test('loadAssignments loads assignments and sets status to loaded', () async {
      final notifier = container.read(fieldWorkNotifierProvider.notifier);

      await notifier.loadAssignments();

      final state = container.read(fieldWorkNotifierProvider);
      expect(state.status, FieldWorkStatus.loaded);
      expect(state.assignments.length, 2);
    });

    test('setFilter filters assignments correctly', () async {
      final notifier = container.read(fieldWorkNotifierProvider.notifier);
      await notifier.loadAssignments();

      notifier.setFilter('pending');
      var state = container.read(fieldWorkNotifierProvider);
      expect(state.filteredAssignments.length, 1);
      expect(state.filteredAssignments.first.id, 1);

      notifier.setFilter('in_progress');
      state = container.read(fieldWorkNotifierProvider);
      expect(state.filteredAssignments.length, 1);
      expect(state.filteredAssignments.first.id, 2);

      notifier.setFilter('completed');
      state = container.read(fieldWorkNotifierProvider);
      expect(state.filteredAssignments.length, 0);

      notifier.setFilter('all');
      state = container.read(fieldWorkNotifierProvider);
      expect(state.filteredAssignments.length, 2);
    });

    test('selectAssignment selects the right task and resets verification result', () async {
      final notifier = container.read(fieldWorkNotifierProvider.notifier);
      await notifier.loadAssignments();

      notifier.selectAssignment(2);
      final state = container.read(fieldWorkNotifierProvider);
      expect(state.selectedAssignment?.id, 2);
      expect(state.verificationResult, isNull);
    });

    test('acceptAssignment updates assignment status from pending to accepted', () async {
      final notifier = container.read(fieldWorkNotifierProvider.notifier);
      await notifier.loadAssignments();

      final success = await notifier.acceptAssignment(1);

      expect(success, true);
      final state = container.read(fieldWorkNotifierProvider);
      expect(state.status, FieldWorkStatus.success);
      expect(state.assignments.firstWhere((a) => a.id == 1).status, 'accepted');
    });

    test('verifyLocation within geofence sets isLocationVerified to true', () async {
      final notifier = container.read(fieldWorkNotifierProvider.notifier);
      await notifier.loadAssignments();

      // Latitude inside the range
      final isWithin = await notifier.verifyLocation(
        assignmentId: 1,
        latitude: 15.369445,
        longitude: 44.191006,
      );

      expect(isWithin, true);
      final state = container.read(fieldWorkNotifierProvider);
      expect(state.isLocationVerified, true);
      expect(state.verificationResult?.isWithinRange, true);
      expect(state.errorMessage, isNull);
    });

    test('verifyLocation outside geofence sets isLocationVerified to false with error message', () async {
      final notifier = container.read(fieldWorkNotifierProvider.notifier);
      await notifier.loadAssignments();

      // Latitude outside the range
      final isWithin = await notifier.verifyLocation(
        assignmentId: 1,
        latitude: 12.0,
        longitude: 45.0,
      );

      expect(isWithin, false);
      final state = container.read(fieldWorkNotifierProvider);
      expect(state.isLocationVerified, false);
      expect(state.errorMessage, contains('يبعد 850 متراً'));
    });

    test('startAssignment updates task to in_progress', () async {
      final notifier = container.read(fieldWorkNotifierProvider.notifier);
      await notifier.loadAssignments();

      final success = await notifier.startAssignment(1);

      expect(success, true);
      final state = container.read(fieldWorkNotifierProvider);
      expect(state.status, FieldWorkStatus.success);
      expect(state.assignments.firstWhere((a) => a.id == 1).status, 'in_progress');
    });

    test('completeAssignment updates task to completed with report', () async {
      final notifier = container.read(fieldWorkNotifierProvider.notifier);
      await notifier.loadAssignments();

      final success = await notifier.completeAssignment(
        assignmentId: 1,
        report: 'تم إصلاح أنبوب المياه وإعادة ردم الحفرة بنجاح.',
        latitude: 15.369445,
        longitude: 44.191006,
      );

      expect(success, true);
      final state = container.read(fieldWorkNotifierProvider);
      expect(state.status, FieldWorkStatus.success);
      final completed = state.assignments.firstWhere((a) => a.id == 1);
      expect(completed.status, 'completed');
      expect(completed.notes, contains('أنبوب المياه'));
    });

    test('Error handling in repository propagates cleanly to state', () async {
      mockRepo.shouldThrow = true;
      mockRepo.errorMessage = 'خادم غير متاح';

      final notifier = container.read(fieldWorkNotifierProvider.notifier);
      await notifier.loadAssignments();

      final state = container.read(fieldWorkNotifierProvider);
      expect(state.status, FieldWorkStatus.error);
      expect(state.errorMessage, 'خادم غير متاح');
    });
  });

  group('Phase 05 — Role-Based Entity & Routing Checks', () {
    test('UserEntity correctly identifies Field Worker role', () {
      const fieldWorkerUser = UserEntity(
        id: 5,
        name: 'ميداني 1',
        email: 'field@balagh.gov.ye',
        roles: [
          RoleEntity(id: 4, name: 'Field Worker'),
        ],
      );

      expect(fieldWorkerUser.isFieldWorker, true);
      expect(fieldWorkerUser.isCitizen, false);
      expect(fieldWorkerUser.isAdmin, false);

      const citizenUser = UserEntity(
        id: 10,
        name: 'مواطن',
        email: 'citizen@example.com',
        roles: [
          RoleEntity(id: 1, name: 'Citizen'),
        ],
      );

      expect(citizenUser.isFieldWorker, false);
      expect(citizenUser.isCitizen, true);
    });
  });
}
