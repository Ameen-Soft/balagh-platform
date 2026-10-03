import '../domain/entities/field_assignment_entity.dart';
import '../domain/entities/location_verification_result.dart';

enum FieldWorkStatus {
  initial,
  loading,
  loaded,
  accepting,
  verifyingLocation,
  starting,
  completing,
  success,
  error,
}

class FieldWorkState {
  final FieldWorkStatus status;
  final List<FieldWorkAssignmentEntity> assignments;
  final FieldWorkAssignmentEntity? selectedAssignment;
  final LocationVerificationResult? verificationResult;
  final String? errorMessage;
  final String? successMessage;
  final String selectedFilter;

  const FieldWorkState({
    required this.status,
    this.assignments = const [],
    this.selectedAssignment,
    this.verificationResult,
    this.errorMessage,
    this.successMessage,
    this.selectedFilter = 'all',
  });

  factory FieldWorkState.initial() =>
      const FieldWorkState(status: FieldWorkStatus.initial);

  bool get isLoading => status == FieldWorkStatus.loading;
  bool get isLoaded => status == FieldWorkStatus.loaded;
  bool get isAccepting => status == FieldWorkStatus.accepting;
  bool get isVerifyingLocation => status == FieldWorkStatus.verifyingLocation;
  bool get isStarting => status == FieldWorkStatus.starting;
  bool get isCompleting => status == FieldWorkStatus.completing;
  bool get isSuccess => status == FieldWorkStatus.success;
  bool get isError => status == FieldWorkStatus.error;
  bool get isBusy =>
      isLoading || isAccepting || isVerifyingLocation || isStarting || isCompleting;

  /// Whether location was verified and worker is within range.
  bool get isLocationVerified =>
      verificationResult != null && verificationResult!.isWithinRange;

  List<FieldWorkAssignmentEntity> get filteredAssignments {
    if (selectedFilter == 'all') return assignments;
    return assignments.where((a) => a.status == selectedFilter).toList();
  }

  FieldWorkState copyWith({
    FieldWorkStatus? status,
    List<FieldWorkAssignmentEntity>? assignments,
    FieldWorkAssignmentEntity? selectedAssignment,
    LocationVerificationResult? verificationResult,
    String? errorMessage,
    String? successMessage,
    String? selectedFilter,
  }) {
    return FieldWorkState(
      status: status ?? this.status,
      assignments: assignments ?? this.assignments,
      selectedAssignment: selectedAssignment ?? this.selectedAssignment,
      verificationResult: verificationResult ?? this.verificationResult,
      errorMessage: errorMessage,
      successMessage: successMessage,
      selectedFilter: selectedFilter ?? this.selectedFilter,
    );
  }
}
