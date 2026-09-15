import '../entities/category_entity.dart';
import '../entities/complaint_entity.dart';
import '../entities/ministry_entity.dart';
import '../entities/paginated_complaints_result.dart';

abstract class ComplaintRepository {
  Future<List<ComplaintEntity>> getComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  });

  Future<PaginatedComplaintsResult> getPaginatedComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  });

  Future<ComplaintEntity> getComplaintDetails(int id);

  Future<ComplaintEntity> createComplaint({
    required String title,
    required String description,
    required int categoryId,
    required double latitude,
    required double longitude,
    String? priority,
    List<String>? attachmentPaths,
  });

  Future<List<MinistryEntity>> getMinistries();

  Future<List<CategoryEntity>> getCategories({
    int? ministryId,
    int? departmentId,
    int? parentId,
    int? level,
  });
}
