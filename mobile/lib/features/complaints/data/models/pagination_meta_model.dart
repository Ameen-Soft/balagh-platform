class PaginationMetaModel {
  final int currentPage;
  final int lastPage;
  final int perPage;
  final int total;
  final bool hasMore;

  const PaginationMetaModel({
    required this.currentPage,
    required this.lastPage,
    required this.perPage,
    required this.total,
    required this.hasMore,
  });

  factory PaginationMetaModel.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic val, int fallback) {
      if (val is num) return val.toInt();
      return int.tryParse(val?.toString() ?? '') ?? fallback;
    }

    final currentPage = parseInt(json['current_page'], 1);
    final lastPage = parseInt(json['last_page'], 1);
    final perPage = parseInt(json['per_page'], 15);
    final total = parseInt(json['total'], 0);
    final hasMore = json['has_more'] is bool
        ? json['has_more'] as bool
        : (currentPage < lastPage);

    return PaginationMetaModel(
      currentPage: currentPage,
      lastPage: lastPage,
      perPage: perPage,
      total: total,
      hasMore: hasMore,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'current_page': currentPage,
      'last_page': lastPage,
      'per_page': perPage,
      'total': total,
      'has_more': hasMore,
    };
  }

  factory PaginationMetaModel.empty() {
    return const PaginationMetaModel(
      currentPage: 1,
      lastPage: 1,
      perPage: 15,
      total: 0,
      hasMore: false,
    );
  }
}
