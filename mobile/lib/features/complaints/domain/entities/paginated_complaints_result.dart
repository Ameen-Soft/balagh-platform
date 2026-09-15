import 'complaint_entity.dart';

class PaginatedComplaintsResult {
  final List<ComplaintEntity> complaints;
  final int currentPage;
  final int lastPage;
  final int total;
  final bool hasMore;

  const PaginatedComplaintsResult({
    required this.complaints,
    required this.currentPage,
    required this.lastPage,
    required this.total,
    required this.hasMore,
  });

  factory PaginatedComplaintsResult.empty() {
    return const PaginatedComplaintsResult(
      complaints: [],
      currentPage: 1,
      lastPage: 1,
      total: 0,
      hasMore: false,
    );
  }
}
