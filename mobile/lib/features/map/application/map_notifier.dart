import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../complaints/application/complaints_providers.dart';
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

      // Fetch complaints (For now using my complaints, gap documented)
      // Since map requires location, we only keep items with lat/lng
      var complaints = <dynamic>[];
      try {
        final complaintsFuture = ref.read(recentComplaintsProvider.future);
        complaints = await complaintsFuture;
      } catch (e) {
        // User might not be authenticated, or other error. Ignore complaints for public map if not auth.
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
