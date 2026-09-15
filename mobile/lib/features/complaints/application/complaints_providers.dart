import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../auth/application/providers.dart';
import '../../../core/services/camera_service.dart';
import '../../../core/services/location_service.dart';
import '../data/datasources/complaint_remote_data_source.dart';
import '../data/repositories/complaint_repository_impl.dart';
import '../domain/entities/category_entity.dart';
import '../domain/entities/complaint_entity.dart';
import '../domain/entities/ministry_entity.dart';
import '../domain/repositories/complaint_repository.dart';
import 'create_complaint_notifier.dart';
import 'create_complaint_state.dart';

final cameraServiceProvider = Provider<CameraService>((ref) {
  return CameraService();
});

final locationServiceProvider = Provider<LocationService>((ref) {
  return LocationService();
});

final complaintRemoteDataSourceProvider =
    Provider<ComplaintRemoteDataSource>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return ComplaintRemoteDataSourceImpl(apiClient: apiClient);
});

final complaintRepositoryProvider = Provider<ComplaintRepository>((ref) {
  final remoteDataSource = ref.watch(complaintRemoteDataSourceProvider);
  return ComplaintRepositoryImpl(remoteDataSource: remoteDataSource);
});

final ministriesProvider = FutureProvider<List<MinistryEntity>>((ref) async {
  final repository = ref.watch(complaintRepositoryProvider);
  return repository.getMinistries();
});

final parentCategoriesProvider =
    FutureProvider.family<List<CategoryEntity>, int>((ref, ministryId) async {
  final repository = ref.watch(complaintRepositoryProvider);
  return repository.getCategories(ministryId: ministryId, level: 1);
});

final subCategoriesProvider =
    FutureProvider.family<List<CategoryEntity>, int>((ref, parentId) async {
  final repository = ref.watch(complaintRepositoryProvider);
  return repository.getCategories(parentId: parentId);
});

final myComplaintsProvider =
    FutureProvider<List<ComplaintEntity>>((ref) async {
  final repository = ref.watch(complaintRepositoryProvider);
  return repository.getComplaints();
});

final createComplaintNotifierProvider =
    NotifierProvider<CreateComplaintNotifier, CreateComplaintState>(() {
  return CreateComplaintNotifier();
});
