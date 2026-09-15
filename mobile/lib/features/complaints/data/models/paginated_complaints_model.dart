import 'complaint_model.dart';
import 'pagination_meta_model.dart';

class PaginatedComplaintsModel {
  final List<ComplaintModel> items;
  final PaginationMetaModel meta;

  const PaginatedComplaintsModel({
    required this.items,
    required this.meta,
  });

  factory PaginatedComplaintsModel.fromJson(Map<String, dynamic> json) {
    List<ComplaintModel> items = [];
    if (json['data'] is List) {
      items = (json['data'] as List)
          .whereType<Map<String, dynamic>>()
          .map((e) => ComplaintModel.fromJson(e))
          .toList();
    }

    PaginationMetaModel meta = PaginationMetaModel.empty();
    if (json['meta'] is Map<String, dynamic>) {
      meta = PaginationMetaModel.fromJson(json['meta'] as Map<String, dynamic>);
    }

    return PaginatedComplaintsModel(
      items: items,
      meta: meta,
    );
  }
}
