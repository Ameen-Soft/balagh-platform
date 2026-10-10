import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:go_router/go_router.dart';
import '../../application/map_notifier.dart';
import '../../../complaints/domain/entities/complaint_entity.dart';
import '../../../projects/domain/entities/project_entity.dart';

class MapPage extends ConsumerWidget {
  const MapPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(mapNotifierProvider);
    final notifier = ref.read(mapNotifierProvider.notifier);

    // Filter complaints based on state
    final filteredComplaints = state.complaints.where((c) {
      if (!state.showComplaints) return false;
      if (state.selectedStatus != null && c.status != state.selectedStatus) {
        return false;
      }
      return true;
    }).toList();

    // Filter projects based on state
    final filteredProjects = state.projects.where((p) {
      if (!state.showProjects) return false;
      if (state.selectedStatus != null && p.status != state.selectedStatus) {
        return false;
      }
      return true;
    }).toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('الخريطة التفاعلية'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => notifier.loadData(),
          ),
        ],
      ),
      body: Stack(
        children: [
          FlutterMap(
            options: MapOptions(
              initialCenter: const LatLng(15.3694, 44.1910), // Sana'a default
              initialZoom: 12.0,
            ),
            children: [
              TileLayer(
                urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'com.ameen.balagh',
              ),
              MarkerLayer(
                markers: [
                  ...filteredComplaints.map((c) => _buildComplaintMarker(context, c)),
                  ...filteredProjects.map((p) => _buildProjectMarker(context, p)),
                ],
              ),
            ],
          ),
          
          // Filters overlay
          Positioned(
            top: 10,
            left: 10,
            right: 10,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  FilterChip(
                    label: const Text('الشكاوى'),
                    selected: state.showComplaints,
                    onSelected: (val) => notifier.toggleComplaints(val),
                  ),
                  const SizedBox(width: 8),
                  FilterChip(
                    label: const Text('المشاريع التنموية'),
                    selected: state.showProjects,
                    onSelected: (val) => notifier.toggleProjects(val),
                  ),
                  const SizedBox(width: 8),
                  FilterChip(
                    label: const Text('الكل (حالة)'),
                    selected: state.selectedStatus == null,
                    onSelected: (val) {
                      if (val) notifier.setStatusFilter(null);
                    },
                  ),
                  // Add specific statuses here if needed
                ],
              ),
            ),
          ),

          if (state.isLoading)
            const Center(child: CircularProgressIndicator()),
        ],
      ),
    );
  }

  Marker _buildComplaintMarker(BuildContext context, ComplaintEntity complaint) {
    Color markerColor = Colors.orange; // Default: new / under_review
    if (complaint.status == 'resolved' || complaint.status == 'closed') {
      markerColor = Colors.green;
    } else if (complaint.status == 'assigned' || complaint.status == 'in_progress') {
      markerColor = Colors.blue;
    }

    return Marker(
      point: LatLng(complaint.latitude, complaint.longitude),
      width: 40,
      height: 40,
      child: GestureDetector(
        onTap: () => _showComplaintDetails(context, complaint),
        child: Icon(
          Icons.location_on,
          color: markerColor,
          size: 40,
        ),
      ),
    );
  }

  Marker _buildProjectMarker(BuildContext context, ProjectEntity project) {
    return Marker(
      point: LatLng(project.latitude ?? 15.3694, project.longitude ?? 44.1910),
      width: 45,
      height: 45,
      child: GestureDetector(
        onTap: () => _showProjectDetails(context, project),
        child: const Icon(
          Icons.business,
          color: Colors.purple,
          size: 45,
        ),
      ),
    );
  }

  void _showComplaintDetails(BuildContext context, ComplaintEntity complaint) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              complaint.title,
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 8),
            Text('الحالة: \${complaint.status}'),
            const SizedBox(height: 8),
            Text(
              complaint.description,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  context.push('/complaints/\${complaint.id}');
                },
                child: const Text('عرض التفاصيل كاملة'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showProjectDetails(BuildContext context, ProjectEntity project) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              project.title,
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 8),
            Text('الحالة: \${project.status}'),
            const SizedBox(height: 8),
            Text(
              project.description,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  context.push('/projects/\${project.id}');
                },
                child: const Text('عرض التفاصيل كاملة'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
