import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_exceptions.dart';
import '../domain/repositories/field_assignment_repository.dart';
import 'field_work_state.dart';
import 'providers/field_work_providers.dart';

class FieldWorkNotifier extends Notifier<FieldWorkState> {
  @override
  FieldWorkState build() {
    return FieldWorkState.initial();
  }

  FieldAssignmentRepository get _repository =>
      ref.read(fieldAssignmentRepositoryProvider);

  /// Load all field assignments for the current worker.
  Future<void> loadAssignments({String? status}) async {
    state = state.copyWith(status: FieldWorkStatus.loading);
    try {
      final assignments = await _repository.getAssignments(status: status);
      state = state.copyWith(
        status: FieldWorkStatus.loaded,
        assignments: assignments,
      );
    } on ApiException catch (e) {
      state = state.copyWith(
        status: FieldWorkStatus.error,
        errorMessage: e.message,
      );
    } catch (e) {
      state = state.copyWith(
        status: FieldWorkStatus.error,
        errorMessage: 'حدث خطأ غير متوقع أثناء تحميل المهام: ${e.toString()}',
      );
    }
  }

  /// Set the filter for assignment status display.
  void setFilter(String filter) {
    state = state.copyWith(selectedFilter: filter);
  }

  /// Select an assignment for detail view.
  void selectAssignment(int assignmentId) {
    final matches = state.assignments.where((a) => a.id == assignmentId);
    final assignment = matches.isNotEmpty
        ? matches.first
        : (state.assignments.isNotEmpty ? state.assignments.first : null);

    state = state.copyWith(
      selectedAssignment: assignment,
      // Reset verification when selecting a new assignment
      verificationResult: null,
    );
  }

  /// Accept a pending field assignment.
  Future<bool> acceptAssignment(int assignmentId) async {
    state = state.copyWith(status: FieldWorkStatus.accepting);
    try {
      final updated = await _repository.acceptAssignment(assignmentId);

      // Update the assignment in the list
      final updatedList = state.assignments.map((a) {
        return a.id == assignmentId ? updated : a;
      }).toList();

      state = state.copyWith(
        status: FieldWorkStatus.success,
        assignments: updatedList,
        selectedAssignment: updated,
        successMessage: 'تم قبول المهمة الميدانية بنجاح.',
      );
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(
        status: FieldWorkStatus.error,
        errorMessage: e.message,
      );
      return false;
    } catch (e) {
      state = state.copyWith(
        status: FieldWorkStatus.error,
        errorMessage: 'حدث خطأ أثناء قبول المهمة: ${e.toString()}',
      );
      return false;
    }
  }

  /// Verify the worker's current geographic location against the complaint site.
  Future<bool> verifyLocation({
    required int assignmentId,
    required double latitude,
    required double longitude,
  }) async {
    state = state.copyWith(status: FieldWorkStatus.verifyingLocation);
    try {
      final result = await _repository.verifyLocation(
        assignmentId: assignmentId,
        latitude: latitude,
        longitude: longitude,
      );

      state = state.copyWith(
        status: FieldWorkStatus.loaded,
        verificationResult: result,
        successMessage: result.isWithinRange ? result.message : null,
        errorMessage: result.isWithinRange ? null : result.message,
      );
      return result.isWithinRange;
    } on ApiException catch (e) {
      state = state.copyWith(
        status: FieldWorkStatus.error,
        errorMessage: e.message,
      );
      return false;
    } catch (e) {
      state = state.copyWith(
        status: FieldWorkStatus.error,
        errorMessage: 'حدث خطأ أثناء التحقق من الموقع: ${e.toString()}',
      );
      return false;
    }
  }

  /// Start the field assignment execution.
  Future<bool> startAssignment(int assignmentId) async {
    state = state.copyWith(status: FieldWorkStatus.starting);
    try {
      final updated = await _repository.startAssignment(assignmentId);

      final updatedList = state.assignments.map((a) {
        return a.id == assignmentId ? updated : a;
      }).toList();

      state = state.copyWith(
        status: FieldWorkStatus.success,
        assignments: updatedList,
        selectedAssignment: updated,
        successMessage: 'تم بدء تنفيذ المهمة الميدانية.',
      );
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(
        status: FieldWorkStatus.error,
        errorMessage: e.message,
      );
      return false;
    } catch (e) {
      state = state.copyWith(
        status: FieldWorkStatus.error,
        errorMessage: 'حدث خطأ أثناء بدء المهمة: ${e.toString()}',
      );
      return false;
    }
  }

  /// Complete a field assignment with report, coordinates, and evidence.
  Future<bool> completeAssignment({
    required int assignmentId,
    required String report,
    required double latitude,
    required double longitude,
    List<String>? attachmentPaths,
  }) async {
    state = state.copyWith(status: FieldWorkStatus.completing);
    try {
      final updated = await _repository.completeAssignment(
        assignmentId: assignmentId,
        report: report,
        latitude: latitude,
        longitude: longitude,
        attachmentPaths: attachmentPaths,
      );

      final updatedList = state.assignments.map((a) {
        return a.id == assignmentId ? updated : a;
      }).toList();

      state = state.copyWith(
        status: FieldWorkStatus.success,
        assignments: updatedList,
        selectedAssignment: updated,
        successMessage: 'تم إتمام المهمة الميدانية وتوثيق الإنجاز بنجاح.',
      );
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(
        status: FieldWorkStatus.error,
        errorMessage: e.message,
      );
      return false;
    } catch (e) {
      state = state.copyWith(
        status: FieldWorkStatus.error,
        errorMessage: 'حدث خطأ أثناء إتمام المهمة: ${e.toString()}',
      );
      return false;
    }
  }

  /// Clear error or success messages without changing other state.
  void clearMessages() {
    state = state.copyWith(
      errorMessage: null,
      successMessage: null,
    );
  }
}
