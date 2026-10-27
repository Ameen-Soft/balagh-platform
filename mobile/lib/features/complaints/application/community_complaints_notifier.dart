import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/api_exceptions.dart';
import '../../auth/application/providers.dart';
import '../domain/entities/complaint_entity.dart';
import '../domain/entities/complaint_like_result.dart';
import '../domain/repositories/complaint_repository.dart';
import 'community_complaints_state.dart';
import 'complaints_providers.dart';

class CommunityComplaintsNotifier extends Notifier<CommunityComplaintsState> {
  ComplaintRepository get _repository => ref.read(complaintRepositoryProvider);
  final Set<int> _likeInProgress = {};

  @override
  CommunityComplaintsState build() {
    ref.listen(authNotifierProvider, (previous, next) {
      if (previous?.user?.id != next.user?.id) {
        loadInitial();
      }
    });

    Future.microtask(() => loadInitial());
    return CommunityComplaintsState.initial();
  }

  /// Initial load or filter change (page 1)
  Future<void> loadInitial() async {
    state = state.copyWith(
      isLoadingInitial: true,
      clearError: true,
    );

    try {
      final result = await _repository.getPublicComplaints(
        page: 1,
        sortBy: state.sortBy,
        categoryId: state.selectedCategoryId,
        status: state.selectedStatus,
        search: state.searchQuery,
      );

      state = state.copyWith(
        complaints: result.complaints,
        currentPage: result.currentPage,
        lastPage: result.lastPage,
        total: result.total,
        hasMore: result.hasMore,
        isLoadingInitial: false,
        clearError: true,
      );
    } catch (e) {
      final message = e is ApiException
          ? e.message
          : 'تعذر تحميل بلاغات المجتمع. يرجى التحقق من الاتصال.';
      state = state.copyWith(
        isLoadingInitial: false,
        errorMessage: message,
      );
    }
  }

  /// Pagination: load next page
  Future<void> loadMore() async {
    if (state.isLoadingInitial ||
        state.isLoadingMore ||
        state.isRefreshing ||
        !state.hasMore) {
      return;
    }

    state = state.copyWith(isLoadingMore: true);

    try {
      final nextPage = state.currentPage + 1;
      final result = await _repository.getPublicComplaints(
        page: nextPage,
        sortBy: state.sortBy,
        categoryId: state.selectedCategoryId,
        status: state.selectedStatus,
        search: state.searchQuery,
      );

      state = state.copyWith(
        complaints: [...state.complaints, ...result.complaints],
        currentPage: result.currentPage,
        lastPage: result.lastPage,
        total: result.total,
        hasMore: result.hasMore,
        isLoadingMore: false,
      );
    } catch (e) {
      state = state.copyWith(isLoadingMore: false);
    }
  }

  /// Pull-to-refresh
  Future<void> refresh() async {
    if (state.isLoadingInitial || state.isRefreshing) return;

    state = state.copyWith(isRefreshing: true);

    try {
      final result = await _repository.getPublicComplaints(
        page: 1,
        sortBy: state.sortBy,
        categoryId: state.selectedCategoryId,
        status: state.selectedStatus,
        search: state.searchQuery,
      );

      state = state.copyWith(
        complaints: result.complaints,
        currentPage: result.currentPage,
        lastPage: result.lastPage,
        total: result.total,
        hasMore: result.hasMore,
        isRefreshing: false,
        clearError: true,
      );
    } catch (e) {
      final message = e is ApiException
          ? e.message
          : 'تعذر تحديث البيانات. يرجى التحقق من الاتصال.';
      state = state.copyWith(
        isRefreshing: false,
        errorMessage: message,
      );
    }
  }

  /// Change sorting: 'popular' (الأكثر تفاعلاً) or 'latest' (الأحدث)
  Future<void> setSortBy(String sortBy) async {
    if (state.sortBy == sortBy) return;
    state = state.copyWith(sortBy: sortBy);
    await loadInitial();
  }

  /// Filter by category
  Future<void> filterByCategory(int? categoryId) async {
    if (state.selectedCategoryId == categoryId) return;
    state = state.copyWith(
      selectedCategoryId: categoryId,
      clearCategory: categoryId == null,
    );
    await loadInitial();
  }

  /// Filter by status
  Future<void> filterByStatus(String? status) async {
    if (state.selectedStatus == status) return;
    state = state.copyWith(
      selectedStatus: status,
      clearStatus: status == null,
    );
    await loadInitial();
  }

  /// Search by query text
  Future<void> setSearchQuery(String? query) async {
    final trimmed = query?.trim();
    if (state.searchQuery == trimmed) return;
    state = state.copyWith(
      searchQuery: trimmed,
      clearSearch: trimmed == null || trimmed.isEmpty,
    );
    await loadInitial();
  }

  /// Optimistic like / unlike toggle with automatic rollback on network failure
  Future<void> toggleLike(int complaintId) async {
    if (_likeInProgress.contains(complaintId)) return;
    _likeInProgress.add(complaintId);

    final index = state.complaints.indexWhere((c) => c.id == complaintId);
    if (index == -1) {
      _likeInProgress.remove(complaintId);
      return;
    }

    final original = state.complaints[index];
    final wasLiked = original.isLikedByMe;
    final optimisticLiked = !wasLiked;
    final optimisticCount = wasLiked
        ? (original.likesCount > 0 ? original.likesCount - 1 : 0)
        : original.likesCount + 1;

    // 1. Immediate Optimistic UI Update
    final updatedList = List<ComplaintEntity>.from(state.complaints);
    updatedList[index] = original.copyWith(
      isLikedByMe: optimisticLiked,
      likesCount: optimisticCount,
    );
    state = state.copyWith(complaints: updatedList, clearError: true);

    // 2. Network synchronization
    try {
      final ComplaintLikeResult result;
      if (optimisticLiked) {
        result = await _repository.likeComplaint(complaintId);
      } else {
        result = await _repository.unlikeComplaint(complaintId);
      }

      // 3. Confirm with authoritative server count
      final currentIndex = state.complaints.indexWhere((c) => c.id == complaintId);
      if (currentIndex != -1) {
        final confirmedList = List<ComplaintEntity>.from(state.complaints);
        confirmedList[currentIndex] = confirmedList[currentIndex].copyWith(
          isLikedByMe: result.isLiked,
          likesCount: result.likesCount,
        );
        state = state.copyWith(complaints: confirmedList);
      }
    } catch (e) {
      // 4. Automatic Rollback on failure
      final rollbackIndex = state.complaints.indexWhere((c) => c.id == complaintId);
      if (rollbackIndex != -1) {
        final rollbackList = List<ComplaintEntity>.from(state.complaints);
        rollbackList[rollbackIndex] = original;
        final message = e is ApiException
            ? 'تعذر تسجيل التفاعل: ${e.message}'
            : 'تعذر تسجيل التفاعل. يرجى التحقق من اتصال الشبكة.';
        state = state.copyWith(
          complaints: rollbackList,
          errorMessage: message,
        );
      }
      rethrow;
    } finally {
      _likeInProgress.remove(complaintId);
    }
  }
}
