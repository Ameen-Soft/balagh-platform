import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import 'package:mobile/core/services/camera_service.dart';
import 'package:mobile/core/services/location_service.dart';
import 'package:mobile/features/auth/application/providers.dart';
import 'package:mobile/features/auth/domain/entities/user_entity.dart';
import 'package:mobile/features/complaints/application/complaints_providers.dart';
import 'package:mobile/features/complaints/domain/entities/attachment_entity.dart';
import 'package:mobile/features/complaints/domain/entities/category_entity.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_entity.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_like_result.dart';
import 'package:mobile/features/complaints/domain/entities/ministry_entity.dart';
import 'package:mobile/features/complaints/domain/entities/paginated_complaints_result.dart';
import 'package:mobile/features/complaints/domain/repositories/complaint_repository.dart';

class MockComplaintRepository implements ComplaintRepository {
  ApiException? exceptionToThrow;
  Exception? genericExceptionToThrow;

  List<ComplaintEntity> complaintsToReturn = [];
  ComplaintEntity? lastCreatedComplaint;

  @override
  Future<ComplaintEntity> createComplaint({
    required String title,
    required String description,
    required int categoryId,
    required double latitude,
    required double longitude,
    String? priority,
    bool? isPublic,
    List<String>? attachmentPaths,
  }) async {
    if (exceptionToThrow != null) {
      throw exceptionToThrow!;
    }
    if (genericExceptionToThrow != null) {
      throw genericExceptionToThrow!;
    }

    lastCreatedComplaint = ComplaintEntity(
      id: 501,
      title: title,
      description: description,
      status: 'new',
      priority: priority ?? 'medium',
      latitude: latitude,
      longitude: longitude,
      isPublic: isPublic ?? false,
      attachments: (attachmentPaths ?? []).map((path) => AttachmentEntity(
        id: 1,
        filePath: path,
        fileType: 'image/jpeg',
        type: 'before',
      )).toList(),
      createdAt: DateTime.now(),
    );

    return lastCreatedComplaint!;
  }

  @override
  Future<PaginatedComplaintsResult> getPaginatedComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  }) async {
    if (exceptionToThrow != null) {
      throw exceptionToThrow!;
    }
    if (genericExceptionToThrow != null) {
      throw genericExceptionToThrow!;
    }

    return PaginatedComplaintsResult(
      complaints: complaintsToReturn,
      currentPage: page,
      lastPage: 1,
      total: complaintsToReturn.length,
      hasMore: false,
    );
  }

  @override
  Future<List<ComplaintEntity>> getComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  }) async => complaintsToReturn;

  @override
  Future<ComplaintEntity> getComplaintDetails(int id) async =>
      complaintsToReturn.firstWhere((c) => c.id == id);

  @override
  Future<PaginatedComplaintsResult> getPublicComplaints({
    int page = 1,
    String? sortBy,
    int? categoryId,
    String? status,
    String? search,
  }) async => PaginatedComplaintsResult.empty();

  @override
  Future<List<ComplaintEntity>> getMapComplaints({
    double? minLat,
    double? minLng,
    double? maxLat,
    double? maxLng,
  }) async => [];

  @override
  Future<ComplaintLikeResult> likeComplaint(int complaintId) async =>
      const ComplaintLikeResult(isLiked: true, likesCount: 1);

  @override
  Future<ComplaintLikeResult> unlikeComplaint(int complaintId) async =>
      const ComplaintLikeResult(isLiked: false, likesCount: 0);

  @override
  Future<List<CategoryEntity>> getCategories({
    int? ministryId,
    int? departmentId,
    int? parentId,
    int? level,
  }) async => [];

  @override
  Future<List<MinistryEntity>> getMinistries() async => [];
}

class FakeCameraService extends CameraService {
  @override
  Future<String?> capturePhoto() async => '/data/user/0/mobile/cache/camera_123.jpg';
}

class FakeLocationService extends LocationService {
  @override
  Future<LocationResult> getCurrentLocation() async => const LocationResult(
        latitude: 15.369445,
        longitude: 44.191006,
        accuracy: 5.0,
      );
}

void main() {
  late MockComplaintRepository mockRepo;
  late FakeCameraService fakeCamera;
  late FakeLocationService fakeLocation;
  late ProviderContainer container;

  setUp(() {
    mockRepo = MockComplaintRepository();
    fakeCamera = FakeCameraService();
    fakeLocation = FakeLocationService();

    container = ProviderContainer(
      overrides: [
        complaintRepositoryProvider.overrideWithValue(mockRepo),
        cameraServiceProvider.overrideWithValue(fakeCamera),
        locationServiceProvider.overrideWithValue(fakeLocation),
      ],
    );
  });

  tearDown(() {
    container.dispose();
  });

  group('Worker Role Complaint Lifecycle', () {
    test('Worker account can successfully create complaint with full details', () async {
      // 1. Setup user as Field Worker
      final workerUser = const UserEntity(
        id: 42,
        name: 'علي الميداني',
        email: 'worker@bader.test',
        department: DepartmentEntity(id: 3, name: 'إدارة الطرق والجسور', ministryId: 1),
        roles: [RoleEntity(id: 2, name: 'Field Worker', permissions: ['complaints.create', 'complaints.view'])],
      );
      expect(workerUser.isFieldWorker, true);

      final notifier = container.read(createComplaintNotifierProvider.notifier);

      // 2. Select category
      notifier.selectCategory(
        const CategoryEntity(id: 12, name: 'صيانة الحفر الإسفلتية', level: 2),
      );

      // 3. Fill text info
      notifier.setTitle('هبوط في شارع الزبيري');
      notifier.setDescription('هبوط إسفلتي عميق يحتاج تدخل عاجل لإعادة التسوية.');

      // 4. Capture photo and location
      await notifier.capturePhoto();
      await notifier.captureLocation();

      expect(container.read(createComplaintNotifierProvider).isReadyToSubmit, true);

      // 5. Submit complaint as worker
      final success = await notifier.submitComplaint();

      expect(success, true);
      final state = container.read(createComplaintNotifierProvider);
      expect(state.isSuccess, true);
      expect(state.errorMessage, isNull);
      expect(state.createdComplaint?.title, 'هبوط في شارع الزبيري');
      expect(state.createdComplaint?.complaintNumber, 'CMP-000501');
    });

    test('Worker can view complaints history in MyComplaintsNotifier', () async {
      final complaint1 = ComplaintEntity(
        id: 101,
        title: 'بلاغي القديم قبل الترقية',
        description: 'تفاصيل البلاغ المسجل عندما كان الحساب مواطناً.',
        status: 'new',
        priority: 'high',
        latitude: 15.35,
        longitude: 44.20,
        createdAt: DateTime.now().subtract(const Duration(days: 10)),
      );

      final complaint2 = ComplaintEntity(
        id: 102,
        title: 'بلاغي الجديد بعد الترقية',
        description: 'تفاصيل البلاغ المسجل بعد الترقية إلى عامل ميداني.',
        status: 'under_review',
        priority: 'urgent',
        latitude: 15.36,
        longitude: 44.21,
        createdAt: DateTime.now(),
      );

      mockRepo.complaintsToReturn = [complaint1, complaint2];

      final myComplaintsNotifier = container.read(myComplaintsNotifierProvider.notifier);
      await myComplaintsNotifier.loadInitial();

      final state = container.read(myComplaintsNotifierProvider);
      expect(state.isLoadingInitial, false);
      expect(state.errorMessage, isNull);
      expect(state.complaints.length, 2);
      expect(state.complaints[0].title, 'بلاغي القديم قبل الترقية');
      expect(state.complaints[1].title, 'بلاغي الجديد بعد الترقية');
    });
  });

  group('HTTP Error Handling Accuracy (Not Masking Errors)', () {
    setUp(() async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);
      notifier.selectCategory(const CategoryEntity(id: 10, name: 'إنارة الطرق', level: 2));
      notifier.setTitle('انقطاع الإنارة');
      notifier.setDescription('انقطاع تام لإنارة الشارع العام.');
      await notifier.capturePhoto();
      await notifier.captureLocation();
    });

    test('403 Forbidden does NOT display "يرجى التحقق من اتصالك والمحاولة مجدداً"', () async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);

      // Simulate backend 403 Forbidden
      mockRepo.exceptionToThrow = const ForbiddenException('ليس لديك الصلاحية لتقديم بلاغ جديد.', 403);

      final success = await notifier.submitComplaint();

      expect(success, false);
      final state = container.read(createComplaintNotifierProvider);
      expect(state.errorMessage, isNotNull);
      // MUST NOT say check your connection
      expect(state.errorMessage!.contains('اتصالك والمحاولة مجدد'), false);
      // MUST clearly indicate authorization issue
      expect(state.errorMessage!.contains('الصلاحية'), true);
    });

    test('403 Forbidden with default English "This action is unauthorized." is mapped to Arabic permission message', () async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);

      mockRepo.exceptionToThrow = const ForbiddenException('This action is unauthorized.', 403);

      final success = await notifier.submitComplaint();

      expect(success, false);
      final state = container.read(createComplaintNotifierProvider);
      expect(state.errorMessage, isNotNull);
      expect(state.errorMessage!.contains('اتصالك والمحاولة مجدد'), false);
      expect(state.errorMessage!.contains('الصلاحية'), true);
    });

    test('401 Unauthorized shows session expiry message', () async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);

      mockRepo.exceptionToThrow = const UnauthorizedException('انتهت صلاحية الجلسة، يرجى تسجيل الدخول مجدداً.', 401);

      final success = await notifier.submitComplaint();

      expect(success, false);
      final state = container.read(createComplaintNotifierProvider);
      expect(state.errorMessage, isNotNull);
      expect(state.errorMessage!.contains('الجلسة'), true);
      expect(state.errorMessage!.contains('اتصالك والمحاولة مجدد'), false);
    });

    test('422 Validation Error shows specific validation firstErrorMessage', () async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);

      mockRepo.exceptionToThrow = const ValidationException(
        'بيانات الإدخال غير صالحة',
        errors: {
          'category_id': ['هذا التصنيف معطل حالياً ولا يمكن استقبال بلاغات جديدة عليه.'],
        },
        statusCode: 422,
      );

      final success = await notifier.submitComplaint();

      expect(success, false);
      final state = container.read(createComplaintNotifierProvider);
      expect(state.errorMessage, isNotNull);
      expect(state.errorMessage, 'هذا التصنيف معطل حالياً ولا يمكن استقبال بلاغات جديدة عليه.');
      expect(state.errorMessage!.contains('اتصالك والمحاولة مجدد'), false);
    });

    test('500 Server Error shows server error message', () async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);

      mockRepo.exceptionToThrow = const ServerException('حدث خطأ في الخادم، يرجى المحاولة لاحقاً.', 500);

      final success = await notifier.submitComplaint();

      expect(success, false);
      final state = container.read(createComplaintNotifierProvider);
      expect(state.errorMessage, isNotNull);
      expect(state.errorMessage!.contains('الخادم'), true);
      expect(state.errorMessage!.contains('اتصالك والمحاولة مجدد'), false);
    });

    test('Actual Network / Connection failure shows connection error message', () async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);

      mockRepo.exceptionToThrow = const NetworkException('تعذر الاتصال بالخادم، يرجى التحقق من اتصال الإنترنت والمحاولة مجدداً.');

      final success = await notifier.submitComplaint();

      expect(success, false);
      final state = container.read(createComplaintNotifierProvider);
      expect(state.errorMessage, isNotNull);
      expect(state.errorMessage!.contains('اتصال الإنترنت') || state.errorMessage!.contains('الاتصال بالخادم'), true);
    });
  });

  group('MyComplaintsNotifier Error Differentiation & State Isolation', () {
    test('403 Forbidden in MyComplaints displays permission message', () async {
      mockRepo.exceptionToThrow = const ForbiddenException('This action is unauthorized.', 403);

      final notifier = container.read(myComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      final state = container.read(myComplaintsNotifierProvider);
      expect(state.errorMessage, isNotNull);
      expect(state.errorMessage!.contains('الصلاحية'), true);
      expect(state.errorMessage!.contains('This action is unauthorized.'), false);
    });

    test('Network error in MyComplaints retains connection message', () async {
      mockRepo.exceptionToThrow = const NetworkException('تعذر الاتصال بالخادم، يرجى التحقق من اتصال الإنترنت.');

      final notifier = container.read(myComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      final state = container.read(myComplaintsNotifierProvider);
      expect(state.errorMessage, isNotNull);
      expect(state.errorMessage!.contains('اتصال'), true);
    });

    test('Reset wipes complaints from state to prevent data leakage across accounts', () async {
      mockRepo.complaintsToReturn = [
        ComplaintEntity(
          id: 101,
          title: 'بلاغ المستخدم السابق',
          description: 'تفاصيل خاصة',
          status: 'new',
          priority: 'medium',
          latitude: 15.35,
          longitude: 44.20,
          createdAt: DateTime.now(),
        ),
      ];

      final notifier = container.read(myComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      expect(container.read(myComplaintsNotifierProvider).complaints.length, 1);
      expect(container.read(myComplaintsNotifierProvider).complaints.first.title, 'بلاغ المستخدم السابق');

      // Logout or account switch triggers reset
      notifier.reset();

      final resetState = container.read(myComplaintsNotifierProvider);
      expect(resetState.complaints.isEmpty, true);
      expect(resetState.total, 0);
      expect(resetState.errorMessage, isNull);
    });
  });
}
