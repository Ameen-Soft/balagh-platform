import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../complaints/application/complaints_providers.dart';
import '../../complaints/domain/entities/complaint_entity.dart';
import '../../projects/application/providers/projects_providers.dart';
import 'map_state.dart';

class MapNotifier extends Notifier<MapState> {
  @override
  MapState build() {
    // Initial fetch
    Future.microtask(() => loadData());
    return MapState(isLoading: true);
  }

  Future<void> loadData() async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      // Fetch projects (public API)
      final projectsNotifier = ref.read(projectsNotifierProvider.notifier);
      await projectsNotifier.loadProjects(refresh: true);
      final projects = ref.read(projectsNotifierProvider).projects;

      // Fetch public complaints with geographic coordinates via getMapComplaints()
      List<ComplaintEntity> complaints = [];
      try {
        final complaintRepository = ref.read(complaintRepositoryProvider);
        complaints = await complaintRepository.getMapComplaints();
      } catch (e) {
        complaints = [];
      }

      final validComplaints = complaints.where((c) => c.latitude != 0.0 && c.longitude != 0.0).toList();
      final validProjects = projects.where((p) => p.latitude != null).toList();

      state = state.copyWith(
        isLoading: false,
        complaints: List.from(validComplaints),
        projects: List.from(validProjects),
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  Future<void> toggleLike(int complaintId) async {
    final index = state.complaints.indexWhere((c) => c.id == complaintId);
    if (index == -1) return;

    final original = state.complaints[index];
    final wasLiked = original.isLikedByMe;
    final optimisticLiked = !wasLiked;
    final optimisticCount = wasLiked
        ? (original.likesCount > 0 ? original.likesCount - 1 : 0)
        : original.likesCount + 1;

    // Optimistic UI update
    final updatedComplaints = List<ComplaintEntity>.from(state.complaints);
    updatedComplaints[index] = original.copyWith(
      isLikedByMe: optimisticLiked,
      likesCount: optimisticCount,
    );
    state = state.copyWith(complaints: updatedComplaints);

    try {
      final repository = ref.read(complaintRepositoryProvider);
      final result = optimisticLiked
          ? await repository.likeComplaint(complaintId)
          : await repository.unlikeComplaint(complaintId);

      final confirmedIndex = state.complaints.indexWhere((c) => c.id == complaintId);
      if (confirmedIndex != -1) {
        final confirmedComplaints = List<ComplaintEntity>.from(state.complaints);
        confirmedComplaints[confirmedIndex] = confirmedComplaints[confirmedIndex].copyWith(
          isLikedByMe: result.isLiked,
          likesCount: result.likesCount,
        );
        state = state.copyWith(complaints: confirmedComplaints);
      }
    } catch (e) {
      // Automatic rollback on network error
      final rollbackIndex = state.complaints.indexWhere((c) => c.id == complaintId);
      if (rollbackIndex != -1) {
        final rollbackComplaints = List<ComplaintEntity>.from(state.complaints);
        rollbackComplaints[rollbackIndex] = original;
        state = state.copyWith(complaints: rollbackComplaints);
      }
      rethrow;
    }
  }

  void toggleComplaints(bool show) {
    state = state.copyWith(showComplaints: show);
  }

  void toggleProjects(bool show) {
    state = state.copyWith(showProjects: show);
  }

  void setStatusFilter(String? status) {
    state = state.copyWith(
      selectedStatus: status,
      clearStatus: status == null,
    );
  }
}

final mapNotifierProvider = NotifierProvider<MapNotifier, MapState>(MapNotifier.new);
