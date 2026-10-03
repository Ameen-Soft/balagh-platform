import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../auth/application/providers.dart';
import '../../data/datasources/field_assignment_remote_data_source.dart';
import '../../data/repositories/field_assignment_repository_impl.dart';
import '../../domain/repositories/field_assignment_repository.dart';
import '../field_work_notifier.dart';
import '../field_work_state.dart';

/// Remote data source provider for field assignments.
final fieldAssignmentRemoteDataSourceProvider =
    Provider<FieldAssignmentRemoteDataSource>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return FieldAssignmentRemoteDataSourceImpl(apiClient: apiClient);
});

/// Repository provider for field assignments.
final fieldAssignmentRepositoryProvider =
    Provider<FieldAssignmentRepository>((ref) {
  final remoteDataSource = ref.watch(fieldAssignmentRemoteDataSourceProvider);
  return FieldAssignmentRepositoryImpl(remoteDataSource: remoteDataSource);
});

/// Main state notifier for the field work feature.
final fieldWorkNotifierProvider =
    NotifierProvider<FieldWorkNotifier, FieldWorkState>(FieldWorkNotifier.new);
