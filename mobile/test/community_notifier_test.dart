import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import 'package:mobile/features/complaints/application/complaints_providers.dart';
import 'package:mobile/features/complaints/domain/entities/category_entity.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_entity.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_like_result.dart';
import 'package:mobile/features/complaints/domain/entities/ministry_entity.dart';
import 'package:mobile/features/complaints/domain/entities/paginated_complaints_result.dart';
import 'package:mobile/features/complaints/domain/repositories/complaint_repository.dart';

class FakeCommunityComplaintRepository implements ComplaintRepository {
  List<ComplaintEntity> publicComplaintsToReturn = [];
  bool shouldThrowOnPublic = false;
  bool shouldThrowOnLike = false;

  int? lastRequestedPage;
  String? lastRequestedSortBy;
  int? lastRequestedCategoryId;
  String? lastRequestedStatus;
  String? lastRequestedSearch;

  int likeCallsCount = 0;
  int unlikeCallsCount = 0;

  @override
  Future<PaginatedComplaintsResult> getPublicComplaints({
    int page = 1,
    String? sortBy,
    int? categoryId,
    String? status,
    String? search,
  }) async {
    lastRequestedPage = page;
    lastRequestedSortBy = sortBy;
    lastRequestedCategoryId = categoryId;
    lastRequestedStatus = status;
    lastRequestedSearch = search;

    if (shouldThrowOnPublic) {
      throw const NetworkException('تعذر الاتصال بخادم بادر');
    }

    final hasMore = page < 3;
    return PaginatedComplaintsResult(
      complaints: publicComplaintsToReturn,
      currentPage: page,
      lastPage: 3,
      total: 30,
      hasMore: hasMore,
    );
  }

  @override
  Future<ComplaintLikeResult> likeComplaint(int complaintId) async {
    likeCallsCount++;
    if (shouldThrowOnLike) {
      throw const NetworkException('انقطع الاتصال أثناء تسجيل الإعجاب');
    }
    return const ComplaintLikeResult(isLiked: true, likesCount: 15);
  }

  @override
  Future<ComplaintLikeResult> unlikeComplaint(int complaintId) async {
    unlikeCallsCount++;
    if (shouldThrowOnLike) {
      throw const NetworkException('انقطع الاتصال أثناء إلغاء الإعجاب');
    }
    return const ComplaintLikeResult(isLiked: false, likesCount: 14);
  }

  @override
  Future<List<ComplaintEntity>> getMapComplaints({
    double? minLat,
    double? minLng,
    double? maxLat,
    double? maxLng,
  }) async {
    return publicComplaintsToReturn;
  }

  @override
  Future<List<ComplaintEntity>> getComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  }) async {
    return publicComplaintsToReturn;
  }

  @override
  Future<PaginatedComplaintsResult> getPaginatedComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  }) async {
    return PaginatedComplaintsResult(
      complaints: publicComplaintsToReturn,
      currentPage: page,
      lastPage: 1,
      total: publicComplaintsToReturn.length,
      hasMore: false,
    );
  }

  @override
  Future<ComplaintEntity> getComplaintDetails(int id) async {
    return publicComplaintsToReturn.firstWhere((c) => c.id == id);
  }

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
    throw UnimplementedError();
  }

  @override
  Future<List<MinistryEntity>> getMinistries() async => [];

  @override
  Future<List<CategoryEntity>> getCategories({
    int? ministryId,
    int? departmentId,
    int? parentId,
    int? level,
  }) async => [];
}

void main() {
  group('CommunityComplaintsNotifier Tests', () {
    late FakeCommunityComplaintRepository repository;
    late ProviderContainer container;

    final sampleComplaint1 = const ComplaintEntity(
      id: 1,
      title: 'ماسورة مياه رئيسية مكسورة',
      description: 'تسرب مياه كبير في الشارع الدائري',
      status: 'in_progress',
      priority: 'high',
      latitude: 15.369,
      longitude: 44.191,
      likesCount: 14,
      isLikedByMe: false,
      isPublic: true,
    );

    final sampleComplaint2 = const ComplaintEntity(
      id: 2,
      title: 'تكدس قمامة بالقرب من مدرسة',
      description: 'تجمع مخلفات يعيق مرور الطلاب',
      status: 'new',
      priority: 'medium',
      latitude: 15.355,
      longitude: 44.205,
      likesCount: 8,
      isLikedByMe: true,
      isPublic: true,
    );

    setUp(() {
      repository = FakeCommunityComplaintRepository();
      repository.publicComplaintsToReturn = [sampleComplaint1, sampleComplaint2];

      container = ProviderContainer(
        overrides: [
          complaintRepositoryProvider.overrideWithValue(repository),
        ],
      );
    });

    tearDown(() {
      container.dispose();
    });

    test('Initial build triggers loadInitial and populates complaints list', () async {
      final notifier = container.read(communityComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      final state = container.read(communityComplaintsNotifierProvider);
      expect(state.isLoadingInitial, false);
      expect(state.complaints.length, 2);
      expect(state.complaints.first.id, 1);
      expect(state.complaints.first.likesCount, 14);
      expect(state.total, 30);
      expect(state.hasMore, true);
      expect(repository.lastRequestedPage, 1);
    });

    test('loadMore appends new complaints and advances page number', () async {
      final notifier = container.read(communityComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      final sampleComplaint3 = const ComplaintEntity(
        id: 3,
        title: 'أعمدة إنارة ساقطة',
        description: 'عمود إنارة سقط جراء الرياح',
        status: 'new',
        priority: 'urgent',
        latitude: 15.340,
        longitude: 44.220,
        likesCount: 22,
        isLikedByMe: false,
        isPublic: true,
      );

      repository.publicComplaintsToReturn = [sampleComplaint3];

      await notifier.loadMore();

      final state = container.read(communityComplaintsNotifierProvider);
      expect(state.isLoadingMore, false);
      expect(state.complaints.length, 3);
      expect(state.complaints.last.id, 3);
      expect(state.currentPage, 2);
      expect(repository.lastRequestedPage, 2);
    });

    test('setSortBy changes sorting strategy and reloads from page 1', () async {
      final notifier = container.read(communityComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      await notifier.setSortBy('latest');

      final state = container.read(communityComplaintsNotifierProvider);
      expect(state.sortBy, 'latest');
      expect(repository.lastRequestedSortBy, 'latest');
      expect(repository.lastRequestedPage, 1);
    });

    test('filterByCategory and filterByStatus update state and trigger fetch', () async {
      final notifier = container.read(communityComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      await notifier.filterByCategory(5);
      expect(container.read(communityComplaintsNotifierProvider).selectedCategoryId, 5);
      expect(repository.lastRequestedCategoryId, 5);

      await notifier.filterByStatus('in_progress');
      expect(container.read(communityComplaintsNotifierProvider).selectedStatus, 'in_progress');
      expect(repository.lastRequestedStatus, 'in_progress');
    });

    test('toggleLike performs optimistic like update and confirms with server result', () async {
      final notifier = container.read(communityComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      // Complaint 1 starts with isLikedByMe = false, likesCount = 14
      final future = notifier.toggleLike(1);

      // Check immediate optimistic update
      final optimisticState = container.read(communityComplaintsNotifierProvider);
      final optimisticComplaint = optimisticState.complaints.firstWhere((c) => c.id == 1);
      expect(optimisticComplaint.isLikedByMe, true);
      expect(optimisticComplaint.likesCount, 15);

      await future;

      // Confirmed with server (server returns isLiked: true, likesCount: 15)
      final confirmedState = container.read(communityComplaintsNotifierProvider);
      final confirmedComplaint = confirmedState.complaints.firstWhere((c) => c.id == 1);
      expect(confirmedComplaint.isLikedByMe, true);
      expect(confirmedComplaint.likesCount, 15);
      expect(repository.likeCallsCount, 1);
    });

    test('toggleLike performs automatic rollback when network request fails', () async {
      final notifier = container.read(communityComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      // Make like call throw an exception
      repository.shouldThrowOnLike = true;

      // Complaint 1 starts with isLikedByMe = false, likesCount = 14
      try {
        await notifier.toggleLike(1);
      } catch (_) {
        // Expected rethrow
      }

      // Rollback should revert isLikedByMe to false and likesCount to 14
      final rolledBackState = container.read(communityComplaintsNotifierProvider);
      final rolledBackComplaint = rolledBackState.complaints.firstWhere((c) => c.id == 1);
      expect(rolledBackComplaint.isLikedByMe, false);
      expect(rolledBackComplaint.likesCount, 14);
      expect(rolledBackState.errorMessage, contains('تعذر تسجيل التفاعل'));
    });

    test('loadInitial handles network failure gracefully and populates errorMessage', () async {
      repository.shouldThrowOnPublic = true;

      final notifier = container.read(communityComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      final state = container.read(communityComplaintsNotifierProvider);
      expect(state.isLoadingInitial, false);
      expect(state.complaints.isEmpty, true);
      expect(state.errorMessage, contains('تعذر الاتصال بخادم بادر'));
    });
  });
}
