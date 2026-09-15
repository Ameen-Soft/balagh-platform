import '../../domain/entities/category_entity.dart';
import '../../domain/entities/complaint_entity.dart';
import '../../domain/entities/ministry_entity.dart';
import '../../domain/entities/paginated_complaints_result.dart';
import '../../domain/repositories/complaint_repository.dart';
import '../datasources/complaint_remote_data_source.dart';

class ComplaintRepositoryImpl implements ComplaintRepository {
  final ComplaintRemoteDataSource remoteDataSource;

  ComplaintRepositoryImpl({required this.remoteDataSource});

  @override
  Future<List<ComplaintEntity>> getComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  }) async {
    final models = await remoteDataSource.getComplaints(
      page: page,
      status: status,
      categoryId: categoryId,
      departmentId: departmentId,
      search: search,
    );
    return models.map((m) => m.toEntity()).toList();
  }

  @override
  Future<PaginatedComplaintsResult> getPaginatedComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  }) async {
    final result = await remoteDataSource.getPaginatedComplaints(
      page: page,
      status: status,
      categoryId: categoryId,
      departmentId: departmentId,
      search: search,
    );
    return PaginatedComplaintsResult(
      complaints: result.items.map((m) => m.toEntity()).toList(),
      currentPage: result.meta.currentPage,
      lastPage: result.meta.lastPage,
      total: result.meta.total,
      hasMore: result.meta.hasMore,
    );
  }

  @override
  Future<ComplaintEntity> getComplaintDetails(int id) async {
    final model = await remoteDataSource.getComplaintDetails(id);
    return model.toEntity();
  }

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
    final model = await remoteDataSource.createComplaint(
      title: title,
      description: description,
      categoryId: categoryId,
      latitude: latitude,
      longitude: longitude,
      priority: priority,
      attachmentPaths: attachmentPaths,
    );
    return model.toEntity();
  }

  @override
  Future<List<MinistryEntity>> getMinistries() async {
    final models = await remoteDataSource.getMinistries();
    return models.map((m) => m.toEntity()).toList();
  }

  @override
  Future<List<CategoryEntity>> getCategories({
    int? ministryId,
    int? departmentId,
    int? parentId,
    int? level,
  }) async {
    final models = await remoteDataSource.getCategories(
      ministryId: ministryId,
      departmentId: departmentId,
      parentId: parentId,
      level: level,
    );
    return models.map((m) => m.toEntity()).toList();
  }
}
