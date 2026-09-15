import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/services/camera_service.dart';
import 'package:mobile/core/services/location_service.dart';
import 'package:mobile/features/complaints/application/complaints_providers.dart';
import 'package:mobile/features/complaints/domain/entities/category_entity.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_entity.dart';
import 'package:mobile/features/complaints/domain/entities/ministry_entity.dart';
import 'package:mobile/features/complaints/domain/repositories/complaint_repository.dart';

class FakeComplaintRepository implements ComplaintRepository {
  bool shouldThrow = false;
  String? createdTitle;

  @override
  Future<ComplaintEntity> createComplaint({
    required String title,
    required String description,
    required int categoryId,
    required double latitude,
    required double longitude,
    String? priority,
    List<String>? attachmentPaths,
  }) async {
    if (shouldThrow) {
      throw Exception('فشل في خادم البلاغات (Fake Error)');
    }
    createdTitle = title;
    return ComplaintEntity(
      id: 999,
      title: title,
      description: description,
      status: 'new',
      priority: priority ?? 'medium',
      latitude: latitude,
      longitude: longitude,
      createdAt: DateTime.now(),
    );
  }

  @override
  Future<List<CategoryEntity>> getCategories({
    int? ministryId,
    int? departmentId,
    int? parentId,
    int? level,
  }) async =>
      [];

  @override
  Future<ComplaintEntity> getComplaintDetails(int id) async =>
      throw UnimplementedError();

  @override
  Future<List<ComplaintEntity>> getComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  }) async =>
      [];

  @override
  Future<List<MinistryEntity>> getMinistries() async => [];
}

class FakeCameraService extends CameraService {
  String? returnPath = '/tmp/fake_photo.jpg';
  bool shouldThrow = false;

  @override
  Future<String?> capturePhoto() async {
    if (shouldThrow) {
      throw const CameraException('إذن استخدام الكاميرا مطلوب.');
    }
    return returnPath;
  }
}

class FakeLocationService extends LocationService {
  LocationResult returnResult = const LocationResult(
    latitude: 15.369445,
    longitude: 44.191006,
    accuracy: 8.5,
  );
  bool shouldThrow = false;

  @override
  Future<LocationResult> getCurrentLocation() async {
    if (shouldThrow) {
      throw const LocationException('خدمات الموقع غير مفعلة.', isGpsDisabled: true);
    }
    return returnResult;
  }
}

void main() {
  group('CreateComplaintNotifier & State Tests', () {
    late FakeComplaintRepository fakeRepo;
    late FakeCameraService fakeCamera;
    late FakeLocationService fakeLocation;
    late ProviderContainer container;

    setUp(() {
      fakeRepo = FakeComplaintRepository();
      fakeCamera = FakeCameraService();
      fakeLocation = FakeLocationService();
      container = ProviderContainer(
        overrides: [
          complaintRepositoryProvider.overrideWithValue(fakeRepo),
          cameraServiceProvider.overrideWithValue(fakeCamera),
          locationServiceProvider.overrideWithValue(fakeLocation),
        ],
      );
    });

    tearDown(() {
      container.dispose();
    });

    test('Initial state has step 0 and empty fields', () {
      final state = container.read(createComplaintNotifierProvider);
      expect(state.currentStep, 0);
      expect(state.title, '');
      expect(state.description, '');
      expect(state.selectedMinistry, isNull);
      expect(state.selectedCategory, isNull);
      expect(state.imagePath, isNull);
      expect(state.latitude, isNull);
      expect(state.canProceedFromCategoryStep, false);
      expect(state.isReadyToSubmit, false);
    });

    test('Selecting ministry resets parent and child categories', () {
      final notifier = container.read(createComplaintNotifierProvider.notifier);

      const ministry = MinistryEntity(
        id: 1,
        name: 'وزارة الأشغال العامة',
        code: 'MPW',
      );
      notifier.selectMinistry(ministry);
      expect(container.read(createComplaintNotifierProvider).selectedMinistry?.id, 1);
      expect(container.read(createComplaintNotifierProvider).selectedParentCategory, isNull);
      expect(container.read(createComplaintNotifierProvider).selectedCategory, isNull);

      const parentCategory = CategoryEntity(
        id: 10,
        name: 'صيانة الطرق',
        level: 1,
        children: [
          CategoryEntity(id: 20, name: 'حفر عميقة', level: 2),
        ],
      );
      notifier.selectParentCategory(parentCategory);
      expect(container.read(createComplaintNotifierProvider).selectedParentCategory?.id, 10);
      expect(container.read(createComplaintNotifierProvider).selectedCategory, isNull); // has children, so leaf is null

      const subCategory = CategoryEntity(id: 20, name: 'حفر عميقة', level: 2);
      notifier.selectCategory(subCategory);
      expect(container.read(createComplaintNotifierProvider).selectedCategory?.id, 20);
      expect(container.read(createComplaintNotifierProvider).canProceedFromCategoryStep, true);
    });

    test('Details validation checks title length >= 3 and description >= 10', () {
      final notifier = container.read(createComplaintNotifierProvider.notifier);
      expect(container.read(createComplaintNotifierProvider).canProceedFromDetailsStep, false);

      notifier.setTitle('هو');
      notifier.setDescription('وصف قصير');
      expect(container.read(createComplaintNotifierProvider).canProceedFromDetailsStep, false);

      notifier.setTitle('هبوط في شارع حدة');
      notifier.setDescription('هبوط إسفلتي مفاجئ يقطع حركة السير أمام المارة');
      expect(container.read(createComplaintNotifierProvider).canProceedFromDetailsStep, true);
    });

    test('Camera capture sets imagePath and handles errors', () async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);
      await notifier.capturePhoto();
      expect(container.read(createComplaintNotifierProvider).imagePath, '/tmp/fake_photo.jpg');
      expect(container.read(createComplaintNotifierProvider).cameraError, isNull);

      notifier.removePhoto();
      expect(container.read(createComplaintNotifierProvider).imagePath, isNull);

      fakeCamera.shouldThrow = true;
      await notifier.capturePhoto();
      expect(container.read(createComplaintNotifierProvider).cameraError, isNotNull);
      expect(container.read(createComplaintNotifierProvider).imagePath, isNull);
    });

    test('Location capture sets coordinates and accuracy', () async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);
      await notifier.captureLocation();
      expect(container.read(createComplaintNotifierProvider).latitude, 15.369445);
      expect(container.read(createComplaintNotifierProvider).longitude, 44.191006);
      expect(container.read(createComplaintNotifierProvider).locationAccuracy, 8.5);
      expect(container.read(createComplaintNotifierProvider).locationError, isNull);

      fakeLocation.shouldThrow = true;
      await notifier.captureLocation();
      expect(container.read(createComplaintNotifierProvider).locationError, isNotNull);
      expect(container.read(createComplaintNotifierProvider).isGpsDisabled, true);
    });

    test('Submission completes successfully with all required data', () async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);

      // Step 0: Category
      notifier.selectCategory(const CategoryEntity(id: 5, name: 'إنارة الطرق', level: 1));

      // Step 1: Details
      notifier.setTitle('أعمدة الإنارة مطفأة بالكامل');
      notifier.setDescription('انقطاع تام للإنارة في شارع الستين الغربي منذ 3 أيام');

      // Step 2: Evidence
      await notifier.capturePhoto();
      await notifier.captureLocation();

      expect(container.read(createComplaintNotifierProvider).isReadyToSubmit, true);

      // Submit
      final success = await notifier.submitComplaint();
      expect(success, true);
      expect(container.read(createComplaintNotifierProvider).isSuccess, true);
      expect(container.read(createComplaintNotifierProvider).createdComplaint?.complaintNumber, 'CMP-000999');
      expect(fakeRepo.createdTitle, 'أعمدة الإنارة مطفأة بالكامل');
    });

    test('Submission failure sets errorMessage gracefully', () async {
      final notifier = container.read(createComplaintNotifierProvider.notifier);

      notifier.selectCategory(const CategoryEntity(id: 5, name: 'إنارة الطرق', level: 1));
      notifier.setTitle('أعمدة الإنارة مطفأة');
      notifier.setDescription('انقطاع تام للإنارة في شارع الستين الغربي');
      await notifier.capturePhoto();
      await notifier.captureLocation();

      fakeRepo.shouldThrow = true;
      final success = await notifier.submitComplaint();
      expect(success, false);
      expect(container.read(createComplaintNotifierProvider).isSuccess, false);
      expect(container.read(createComplaintNotifierProvider).errorMessage, isNotNull);
    });
  });
}
