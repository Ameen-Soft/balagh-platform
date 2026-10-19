import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/api_exceptions.dart';
import '../domain/repositories/complaint_repository.dart';
import 'complaints_providers.dart';
import 'my_complaints_state.dart';

class MyComplaintsNotifier extends Notifier<MyComplaintsState> {
  ComplaintRepository get _repository => ref.read(complaintRepositoryProvider);

  @override
  MyComplaintsState build() {
    // Initial fetch triggered automatically on provider construction
    Future.microtask(() => loadInitial());
    return MyComplaintsState.initial();
  }

  /// Initial load or reload of complaints (page 1)
  Future<void> loadInitial() async {
    state = state.copyWith(
      isLoadingInitial: true,
      clearError: true,
    );

    try {
      final result = await _repository.getPaginatedComplaints(
        page: 1,
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
      final message = e is ApiException ? e.message : 'تعذر تحميل قائمة البلاغات. يرجى المحاولة مجدداً.';
      state = state.copyWith(
        isLoadingInitial: false,
        errorMessage: message,
      );
    }
  }

  /// Load next page when approaching end of the list
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
      final result = await _repository.getPaginatedComplaints(
        page: nextPage,
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
      // On pagination error, do not clear existing data; just stop loading
      state = state.copyWith(isLoadingMore: false);
    }
  }

  /// Pull-to-refresh: fetch fresh page 1 while keeping current data visible
  Future<void> refresh() async {
    if (state.isLoadingInitial || state.isRefreshing) return;

    state = state.copyWith(isRefreshing: true);

    try {
      final result = await _repository.getPaginatedComplaints(
        page: 1,
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

  /// Filter complaints by status (null resets to all)
  Future<void> filterByStatus(String? status) async {
    if (state.selectedStatus == status) return;

    state = state.copyWith(
      selectedStatus: status,
      clearStatus: status == null,
    );

    await loadInitial();
  }

  /// Search complaints by keyword or tracking number
  Future<void> setSearchQuery(String? query) async {
    final trimmed = query?.trim();
    if (state.searchQuery == trimmed) return;

    state = state.copyWith(
      searchQuery: trimmed,
      clearSearch: trimmed == null || trimmed.isEmpty,
    );

    await loadInitial();
  }
}
