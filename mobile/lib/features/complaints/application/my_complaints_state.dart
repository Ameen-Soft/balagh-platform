import '../domain/entities/complaint_entity.dart';

class MyComplaintsState {
  final List<ComplaintEntity> complaints;
  final int currentPage;
  final int lastPage;
  final int total;
  final bool hasMore;
  final bool isLoadingInitial;
  final bool isLoadingMore;
  final bool isRefreshing;
  final String? errorMessage;
  final String? selectedStatus;

  const MyComplaintsState({
    required this.complaints,
    required this.currentPage,
    required this.lastPage,
    required this.total,
    required this.hasMore,
    required this.isLoadingInitial,
    required this.isLoadingMore,
    required this.isRefreshing,
    this.errorMessage,
    this.selectedStatus,
  });

  factory MyComplaintsState.initial() {
    return const MyComplaintsState(
      complaints: [],
      currentPage: 1,
      lastPage: 1,
      total: 0,
      hasMore: false,
      isLoadingInitial: false,
      isLoadingMore: false,
      isRefreshing: false,
      errorMessage: null,
      selectedStatus: null,
    );
  }

  MyComplaintsState copyWith({
    List<ComplaintEntity>? complaints,
    int? currentPage,
    int? lastPage,
    int? total,
    bool? hasMore,
    bool? isLoadingInitial,
    bool? isLoadingMore,
    bool? isRefreshing,
    String? errorMessage,
    bool clearError = false,
    String? selectedStatus,
    bool clearStatus = false,
  }) {
    return MyComplaintsState(
      complaints: complaints ?? this.complaints,
      currentPage: currentPage ?? this.currentPage,
      lastPage: lastPage ?? this.lastPage,
      total: total ?? this.total,
      hasMore: hasMore ?? this.hasMore,
      isLoadingInitial: isLoadingInitial ?? this.isLoadingInitial,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
      isRefreshing: isRefreshing ?? this.isRefreshing,
      errorMessage: clearError ? null : (errorMessage ?? this.errorMessage),
      selectedStatus: clearStatus ? null : (selectedStatus ?? this.selectedStatus),
    );
  }
}
