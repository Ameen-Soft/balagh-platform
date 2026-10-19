import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart' hide Path;
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import 'package:intl/intl.dart' hide TextDirection;
import '../../../../core/constants/app_colors.dart';
import '../../application/map_notifier.dart';
import '../../../auth/application/providers.dart';
import '../../../complaints/domain/entities/complaint_entity.dart';
import '../../../projects/domain/entities/project_entity.dart';
import '../../../home/presentation/widgets/home_drawer.dart';

enum MapStyleType {
  positron, // النمط الأبيض الناصع المتناغم مع هوية بادر (الافتراضي)
  satellite, // صور الأقمار الصناعية
}

class MapPage extends ConsumerStatefulWidget {
  const MapPage({super.key});

  @override
  ConsumerState<MapPage> createState() => _MapPageState();
}

class _MapPageState extends ConsumerState<MapPage>
    with SingleTickerProviderStateMixin {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  final MapController _mapController = MapController();
  MapStyleType _currentStyle = MapStyleType.positron;
  // مفتاح CARTO Basemaps API Key
  static const String cartoApiKey = 'cb1_4gj1_1_d7db88ce492589cc69ee506b';
  LatLng? _userLocation;
  ComplaintEntity? _selectedComplaint;
  ProjectEntity? _selectedProject;
  bool _didAutoFit = false;

  late final AnimationController _layersAnimController;
  late final Animation<double> _expandAnimation;
  bool _isLayersMenuOpen = false;

  final currencyFormatter = NumberFormat.currency(
    locale: 'ar',
    symbol: 'ر.ي',
    decimalDigits: 0,
  );

  @override
  void initState() {
    super.initState();
    _layersAnimController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 320),
      reverseDuration: const Duration(milliseconds: 240),
    );
    _expandAnimation = CurvedAnimation(
      parent: _layersAnimController,
      curve: Curves.easeOutBack,
      reverseCurve: Curves.easeInBack,
    );
  }

  @override
  void dispose() {
    _layersAnimController.dispose();
    super.dispose();
  }

  void _toggleLayersMenu() {
    setState(() {
      _isLayersMenuOpen = !_isLayersMenuOpen;
      if (_isLayersMenuOpen) {
        _layersAnimController.forward();
      } else {
        _layersAnimController.reverse();
      }
    });
  }

  void _selectStyle(MapStyleType style) {
    if (_currentStyle == style) {
      _toggleLayersMenu();
      return;
    }
    setState(() {
      _currentStyle = style;
      _isLayersMenuOpen = false;
      _layersAnimController.reverse();
    });
  }

  @override
  Widget build(BuildContext context) {
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

    // Collect all valid points for centering
    final allPoints = <LatLng>[
      ...filteredComplaints.map((c) => LatLng(c.latitude, c.longitude)),
      ...filteredProjects
          .where((p) => p.latitude != null && p.longitude != null)
          .map((p) => LatLng(p.latitude!, p.longitude!)),
    ];

    // Auto-fit on initial data load once
    if (!_didAutoFit && allPoints.isNotEmpty && !state.isLoading) {
      _didAutoFit = true;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _fitPoints(allPoints);
      });
    }

    return Scaffold(
      key: _scaffoldKey,
      drawer: const HomeDrawer(),
      backgroundColor: AppColors.backgroundSubtle,
      body: Stack(
        children: [
          // 1. The Interactive FlutterMap
          FlutterMap(
            mapController: _mapController,
            options: MapOptions(
              initialCenter: allPoints.isNotEmpty
                  ? allPoints.first
                  : const LatLng(15.3694, 44.1910), // صنعاء افتراضياً
              initialZoom: 13.0,
              onTap: (tapPosition, point) {
                if (_isLayersMenuOpen) {
                  _toggleLayersMenu();
                }
                if (_selectedComplaint != null || _selectedProject != null) {
                  setState(() {
                    _selectedComplaint = null;
                    _selectedProject = null;
                  });
                }
              },
            ),
            children: [
              // High-speed CDN TileLayer
              _buildTileLayer(),

              // Markers Layer
              MarkerLayer(
                markers: [
                  // User GPS Marker
                  if (_userLocation != null) _buildUserLocationMarker(),

                  // Projects Markers
                  ...filteredProjects.map((p) => _buildProjectMarker(p)),

                  // Complaints Markers
                  ...filteredComplaints.map((c) => _buildComplaintMarker(c)),
                ],
              ),
            ],
          ),

          // 2. Top Header & Glass Filter Bar
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 14.0, vertical: 8.0),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _buildTopNavigationBar(context, notifier),
                  const SizedBox(height: 8),
                  _buildFiltersBar(
                    state: state,
                    notifier: notifier,
                    complaintsCount: filteredComplaints.length,
                    projectsCount: filteredProjects.length,
                  ),
                ],
              ),
            ),
          ),

          // 3. Floating Map Controls (Right Side)
          Positioned(
            right: 16,
            bottom: (_selectedComplaint != null || _selectedProject != null)
                ? 260
                : 36,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                _buildLayersExpandableControl(),
                const SizedBox(height: 10),
                _buildMapFloatingButton(
                  icon: Icons.my_location_rounded,
                  tooltip: 'موقعي الحالي',
                  color: AppColors.yemenRed,
                  iconColor: Colors.white,
                  onTap: _goToUserLocation,
                ),
                const SizedBox(height: 10),
                if (allPoints.isNotEmpty) ...[
                  _buildMapFloatingButton(
                    icon: Icons.filter_center_focus_rounded,
                    tooltip: 'توسيط جميع المواقع',
                    onTap: () => _fitPoints(allPoints),
                  ),
                  const SizedBox(height: 10),
                ],
                _buildZoomControls(),
              ],
            ),
          ),

          // 4. Loading Indicator Overlay
          if (state.isLoading)
            Positioned(
              top: 130,
              left: 0,
              right: 0,
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.1),
                        blurRadius: 10,
                      ),
                    ],
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: AppColors.yemenRed,
                        ),
                      ),
                      SizedBox(width: 10),
                      Text(
                        'جاري تحديث الخريطة...',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: AppColors.deepBlack,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

          // 5. Selected Item Bottom Preview Card
          if (_selectedComplaint != null)
            Positioned(
              left: 14,
              right: 14,
              bottom: 24,
              child: _buildComplaintPreviewCard(_selectedComplaint!),
            )
          else if (_selectedProject != null)
            Positioned(
              left: 14,
              right: 14,
              bottom: 24,
              child: _buildProjectPreviewCard(_selectedProject!),
            ),
        ],
      ),
    );
  }

  // --- Tile Layer Selection ---
  Widget _buildTileLayer() {
    final keyParam = cartoApiKey.isNotEmpty ? '?key=$cartoApiKey' : '';

    switch (_currentStyle) {
      case MapStyleType.positron:
        // النمط الأبيض الناصع (الافتراضي المتطابق مع ألوان التطبيق)
        return TileLayer(
          key: const ValueKey('tile_positron'),
          urlTemplate:
              'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png$keyParam',
          subdomains: const ['a', 'b', 'c', 'd'],
          userAgentPackageName: 'com.ameen.balagh',
          maxZoom: 19,
        );
      case MapStyleType.satellite:
        // صور الأقمار الصناعية الواقعية
        return TileLayer(
          key: const ValueKey('tile_satellite'),
          urlTemplate:
              'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
          userAgentPackageName: 'com.ameen.balagh',
          maxZoom: 19,
        );
    }
  }

  // --- Top Navigation Bar ---
  Widget _buildTopNavigationBar(BuildContext context, dynamic notifier) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 12,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_forward_rounded, color: AppColors.deepBlack),
            onPressed: () => context.pop(),
            tooltip: 'رجوع',
          ),
          const SizedBox(width: 4),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'الخريطة التفاعلية',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: AppColors.deepBlack,
                  ),
                ),
                Text(
                  'استكشف البلاغات والمشاريع الجغرافية',
                  style: TextStyle(
                    fontSize: 11,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.menu_rounded, color: AppColors.deepBlack),
            tooltip: 'القائمة الرئيسية',
            onPressed: () => _scaffoldKey.currentState?.openDrawer(),
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppColors.deepBlack),
            tooltip: 'تحديث البيانات',
            onPressed: () => notifier.loadData(),
          ),
        ],
      ),
    );
  }

  // --- Filters Bar ---
  Widget _buildFiltersBar({
    required dynamic state,
    required dynamic notifier,
    required int complaintsCount,
    required int projectsCount,
  }) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      physics: const BouncingScrollPhysics(),
      child: Row(
        children: [
          _buildFilterChip(
            label: 'الشكاوى والبلاغات',
            count: complaintsCount,
            isSelected: state.showComplaints,
            activeColor: AppColors.yemenRed,
            icon: Icons.campaign_rounded,
            onTap: () => notifier.toggleComplaints(!state.showComplaints),
          ),
          const SizedBox(width: 8),
          _buildFilterChip(
            label: 'المشاريع التنموية',
            count: projectsCount,
            isSelected: state.showProjects,
            activeColor: AppColors.deepBlack,
            icon: Icons.apartment_rounded,
            onTap: () => notifier.toggleProjects(!state.showProjects),
          ),
          const SizedBox(width: 8),
          _buildStatusFilterMenu(state: state, notifier: notifier),
        ],
      ),
    );
  }

  Widget _buildFilterChip({
    required String label,
    required int count,
    required bool isSelected,
    required Color activeColor,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? activeColor : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? activeColor : AppColors.borderSubtle,
            width: 1.2,
          ),
          boxShadow: [
            BoxShadow(
              color: isSelected
                  ? activeColor.withValues(alpha: 0.3)
                  : Colors.black.withValues(alpha: 0.05),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 16,
              color: isSelected ? Colors.white : AppColors.deepBlack,
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: isSelected ? Colors.white : AppColors.deepBlack,
              ),
            ),
            const SizedBox(width: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: isSelected
                    ? Colors.white.withValues(alpha: 0.25)
                    : AppColors.backgroundSubtle,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                count.toString(),
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: isSelected ? Colors.white : AppColors.textSecondary,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _getStatusLabel(String status) {
    switch (status) {
      case 'new':
        return 'جديد وارد';
      case 'under_review':
        return 'قيد المراجعة';
      case 'assigned':
        return 'مسند للميدان';
      case 'in_progress':
        return 'قيد التنفيذ';
      case 'resolved':
        return 'تم الإنجاز';
      case 'closed':
        return 'مغلق';
      case 'reopened':
        return 'أُعيد فتحها';
      default:
        return status;
    }
  }

  Widget _buildStatusFilterMenu({required dynamic state, required dynamic notifier}) {
    final hasStatus = state.selectedStatus != null;
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        PopupMenuButton<String>(
          initialValue: state.selectedStatus ?? 'all',
          onSelected: (val) {
            final status = (val == 'all' || val.isEmpty) ? null : val;
            notifier.setStatusFilter(status);
          },
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          itemBuilder: (context) => [
            const PopupMenuItem<String>(
              value: 'all',
              child: Row(
                children: [
                  Icon(Icons.all_inclusive_rounded, size: 18, color: AppColors.textSecondary),
                  SizedBox(width: 8),
                  Text(
                    'جميع الحالات (الكل)',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                ],
              ),
            ),
            const PopupMenuDivider(),
            const PopupMenuItem<String>(
              value: 'new',
              child: Text('جديد وارد'),
            ),
            const PopupMenuItem<String>(
              value: 'under_review',
              child: Text('قيد المراجعة'),
            ),
            const PopupMenuItem<String>(
              value: 'assigned',
              child: Text('مسند للميدان'),
            ),
            const PopupMenuItem<String>(
              value: 'in_progress',
              child: Text('قيد التنفيذ'),
            ),
            const PopupMenuItem<String>(
              value: 'resolved',
              child: Text('تم الإنجاز / الحل'),
            ),
            const PopupMenuItem<String>(
              value: 'closed',
              child: Text('مغلق'),
            ),
          ],
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: hasStatus ? AppColors.deepBlack : Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: hasStatus ? AppColors.deepBlack : AppColors.borderSubtle,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  Icons.tune_rounded,
                  size: 16,
                  color: hasStatus ? Colors.white : AppColors.deepBlack,
                ),
                const SizedBox(width: 6),
                Text(
                  hasStatus
                      ? 'الحالة: ${_getStatusLabel(state.selectedStatus!)}'
                      : 'تصفية الحالة',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: hasStatus ? Colors.white : AppColors.deepBlack,
                  ),
                ),
                const SizedBox(width: 4),
                Icon(
                  Icons.arrow_drop_down,
                  size: 18,
                  color: hasStatus ? Colors.white : AppColors.deepBlack,
                ),
              ],
            ),
          ),
        ),
        if (hasStatus) ...[
          const SizedBox(width: 6),
          InkWell(
            onTap: () => notifier.setStatusFilter(null),
            borderRadius: BorderRadius.circular(16),
            child: Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: Colors.white,
                shape: BoxShape.circle,
                border: Border.all(color: AppColors.borderSubtle),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.05),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: const Icon(
                Icons.close_rounded,
                size: 14,
                color: AppColors.textSecondary,
              ),
            ),
          ),
        ],
      ],
    );
  }

  // --- Markers Builders ---
  Marker _buildUserLocationMarker() {
    return Marker(
      point: _userLocation!,
      width: 40,
      height: 40,
      child: Center(
        child: Container(
          width: 24,
          height: 24,
          decoration: BoxDecoration(
            color: const Color(0xFF2563EB),
            shape: BoxShape.circle,
            border: Border.all(color: Colors.white, width: 3),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF2563EB).withValues(alpha: 0.45),
                blurRadius: 10,
                spreadRadius: 4,
              ),
            ],
          ),
          child: const Center(
            child: Icon(Icons.circle, color: Colors.white, size: 8),
          ),
        ),
      ),
    );
  }

  Marker _buildComplaintMarker(ComplaintEntity complaint) {
    Color markerColor;
    IconData statusIcon;

    switch (complaint.status) {
      case 'resolved':
      case 'closed':
        markerColor = AppColors.yemenEmerald;
        statusIcon = Icons.check_rounded;
        break;
      case 'assigned':
      case 'in_progress':
        markerColor = AppColors.yemenGold;
        statusIcon = Icons.engineering_rounded;
        break;
      case 'rejected':
        markerColor = Colors.grey;
        statusIcon = Icons.close_rounded;
        break;
      default:
        markerColor = AppColors.yemenRed;
        statusIcon = Icons.priority_high_rounded;
    }

    final isSelected = _selectedComplaint?.id == complaint.id;

    return Marker(
      point: LatLng(complaint.latitude, complaint.longitude),
      width: isSelected ? 52 : 44,
      height: isSelected ? 62 : 52,
      alignment: Alignment.topCenter,
      child: GestureDetector(
        onTap: () {
          setState(() {
            _selectedComplaint = complaint;
            _selectedProject = null;
          });
          _mapController.move(
            LatLng(complaint.latitude, complaint.longitude),
            15.0,
          );
        },
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: EdgeInsets.all(isSelected ? 7 : 5),
              decoration: BoxDecoration(
                color: markerColor,
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white, width: 2.5),
                boxShadow: [
                  BoxShadow(
                    color: markerColor.withValues(alpha: 0.5),
                    blurRadius: isSelected ? 12 : 8,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Icon(
                statusIcon,
                color: Colors.white,
                size: isSelected ? 22 : 18,
              ),
            ),
            CustomPaint(
              size: const Size(10, 6),
              painter: _TrianglePainter(color: markerColor),
            ),
            Container(
              width: 6,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.black.withValues(alpha: 0.25),
                shape: BoxShape.circle,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Marker _buildProjectMarker(ProjectEntity project) {
    final lat = project.latitude ?? 15.3694;
    final lng = project.longitude ?? 44.1910;
    const projectColor = AppColors.deepBlack; // أسود ملكي متناسق مع هوية بادر
    final isSelected = _selectedProject?.id == project.id;

    return Marker(
      point: LatLng(lat, lng),
      width: isSelected ? 56 : 46,
      height: isSelected ? 66 : 56,
      alignment: Alignment.topCenter,
      child: GestureDetector(
        onTap: () {
          setState(() {
            _selectedProject = project;
            _selectedComplaint = null;
          });
          _mapController.move(LatLng(lat, lng), 15.0);
        },
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: EdgeInsets.all(isSelected ? 7 : 6),
              decoration: BoxDecoration(
                color: projectColor,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white, width: 2.5),
                boxShadow: [
                  BoxShadow(
                    color: projectColor.withValues(alpha: 0.5),
                    blurRadius: isSelected ? 12 : 8,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Icon(
                Icons.apartment_rounded,
                color: Colors.white,
                size: isSelected ? 24 : 19,
              ),
            ),
            CustomPaint(
              size: const Size(10, 6),
              painter: _TrianglePainter(color: projectColor),
            ),
            Container(
              width: 6,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.black.withValues(alpha: 0.25),
                shape: BoxShape.circle,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // --- Floating Controls Widgets ---
  Widget _buildMapFloatingButton({
    required IconData icon,
    required String tooltip,
    required VoidCallback onTap,
    Color color = Colors.white,
    Color iconColor = AppColors.deepBlack,
  }) {
    return Tooltip(
      message: tooltip,
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(
            color: color,
            shape: BoxShape.circle,
            border: Border.all(color: AppColors.borderSubtle),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.12),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Icon(icon, color: iconColor, size: 22),
        ),
      ),
    );
  }

  Widget _buildZoomControls() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppColors.borderSubtle),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.12),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          GestureDetector(
            onTap: () {
              final currentZoom = _mapController.camera.zoom;
              _mapController.move(_mapController.camera.center, currentZoom + 1);
            },
            child: const Padding(
              padding: EdgeInsets.all(10.0),
              child: Icon(Icons.add, color: AppColors.deepBlack, size: 20),
            ),
          ),
          Container(height: 1, width: 24, color: AppColors.borderSubtle),
          GestureDetector(
            onTap: () {
              final currentZoom = _mapController.camera.zoom;
              _mapController.move(_mapController.camera.center, currentZoom - 1);
            },
            child: const Padding(
              padding: EdgeInsets.all(10.0),
              child: Icon(Icons.remove, color: AppColors.deepBlack, size: 20),
            ),
          ),
        ],
      ),
    );
  }

  // --- Bottom Preview Cards ---
  Widget _buildComplaintPreviewCard(ComplaintEntity complaint) {
    Color badgeColor;
    switch (complaint.status) {
      case 'resolved':
      case 'closed':
        badgeColor = AppColors.yemenEmerald;
        break;
      case 'assigned':
      case 'in_progress':
        badgeColor = AppColors.yemenGold;
        break;
      default:
        badgeColor = AppColors.yemenRed;
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.15),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: badgeColor.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      complaint.statusArabic,
                      style: TextStyle(
                        color: badgeColor,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    complaint.complaintNumber,
                    style: const TextStyle(
                      color: AppColors.textSecondary,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
              IconButton(
                icon: const Icon(Icons.close_rounded, size: 20, color: AppColors.textSecondary),
                onPressed: () {
                  setState(() => _selectedComplaint = null);
                },
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            complaint.title,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppColors.deepBlack,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 4),
          Text(
            complaint.description,
            style: const TextStyle(
              fontSize: 13,
              color: AppColors.textSecondary,
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              // Like / Upvote Button
              InkWell(
                onTap: () {
                  final authState = ref.read(authNotifierProvider);
                  if (!authState.isAuthenticated) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('يرجى تسجيل الدخول لتأييد البلاغ.'),
                        duration: Duration(seconds: 2),
                      ),
                    );
                    return;
                  }

                  final wasLiked = complaint.isLikedByMe;
                  final newLiked = !wasLiked;
                  final newCount = wasLiked
                      ? (complaint.likesCount > 0 ? complaint.likesCount - 1 : 0)
                      : complaint.likesCount + 1;

                  setState(() {
                    _selectedComplaint = complaint.copyWith(
                      isLikedByMe: newLiked,
                      likesCount: newCount,
                    );
                  });

                  ref.read(mapNotifierProvider.notifier).toggleLike(complaint.id).catchError((err) {
                    if (mounted) {
                      setState(() {
                        _selectedComplaint = complaint; // rollback on failure
                      });
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('تعذر تسجيل التفاعل. يرجى التحقق من الاتصال بالشبكة.'),
                          duration: Duration(seconds: 2),
                        ),
                      );
                    }
                  });
                },
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  decoration: BoxDecoration(
                    color: complaint.isLikedByMe
                        ? AppColors.yemenRedLight
                        : AppColors.backgroundSubtle,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: complaint.isLikedByMe
                          ? AppColors.yemenRed
                          : AppColors.borderSubtle,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        complaint.isLikedByMe
                            ? Icons.favorite_rounded
                            : Icons.favorite_border_rounded,
                        size: 18,
                        color: complaint.isLikedByMe
                            ? AppColors.yemenRed
                            : AppColors.textSecondary,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        complaint.likesCount.toString(),
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                          color: complaint.isLikedByMe
                              ? AppColors.yemenRed
                              : AppColors.deepBlack,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'تأييد',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: complaint.isLikedByMe
                              ? AppColors.yemenRed
                              : AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () {
                    context.push('/complaints/${complaint.id}', extra: complaint);
                  },
                  icon: const Icon(Icons.arrow_back_rounded, size: 16),
                  label: const Text('عرض التفاصيل'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.deepBlack,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildProjectPreviewCard(ProjectEntity project) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.15),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.deepBlack.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      project.statusArabic,
                      style: const TextStyle(
                        color: AppColors.deepBlack,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    project.projectNumber,
                    style: const TextStyle(
                      color: AppColors.textSecondary,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
              IconButton(
                icon: const Icon(Icons.close_rounded, size: 20, color: AppColors.textSecondary),
                onPressed: () {
                  setState(() => _selectedProject = null);
                },
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            project.title,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppColors.deepBlack,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 10),
          // Progress bar
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LinearProgressIndicator(
              value: (project.progressPercentage / 100).clamp(0.0, 1.0),
              backgroundColor: AppColors.borderSubtle,
              valueColor: const AlwaysStoppedAnimation<Color>(AppColors.yemenEmerald),
              minHeight: 6,
            ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'نسبة الإنجاز: ${project.progressPercentage.toStringAsFixed(0)}%',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: AppColors.yemenEmerald,
                ),
              ),
              Text(
                'المستهدف: ${currencyFormatter.format(project.targetAmount)}',
                style: const TextStyle(
                  fontSize: 12,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () {
                context.push('/projects/${project.id}', extra: project);
              },
              icon: const Icon(Icons.arrow_back_rounded, size: 16),
              label: const Text('عرض تفاصيل المشروع والمساهمة'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.deepBlack,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                padding: const EdgeInsets.symmetric(vertical: 12),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // --- Helpers ---
  void _fitPoints(List<LatLng> points) {
    if (points.isEmpty) return;
    if (points.length == 1) {
      _mapController.move(points.first, 15.0);
      return;
    }
    final bounds = LatLngBounds.fromPoints(points);
    _mapController.fitCamera(
      CameraFit.bounds(
        bounds: bounds,
        padding: const EdgeInsets.symmetric(horizontal: 50, vertical: 120),
        maxZoom: 16.0,
      ),
    );
  }

  Future<void> _goToUserLocation() async {
    try {
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('خدمة تحديد الموقع (GPS) غير مفعلة بالجهاز')),
          );
        }
        return;
      }

      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('تم رفض إذن الوصول إلى الموقع')),
            );
          }
          return;
        }
      }

      if (permission == LocationPermission.deniedForever) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('أذونات الموقع مرفوضة دائماً، يرجى تفعيلها من إعدادات الهاتف'),
            ),
          );
        }
        return;
      }

      final pos = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(accuracy: LocationAccuracy.high),
      );
      final userPos = LatLng(pos.latitude, pos.longitude);

      if (mounted) {
        setState(() {
          _userLocation = userPos;
        });
        _mapController.move(userPos, 15.5);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('تعذر تحديد الموقع: $e')),
        );
      }
    }
  }

  // --- Animated Expandable Layers Menu ---
  Widget _buildLayersExpandableControl() {
    return Row(
      mainAxisSize: MainAxisSize.min,
      textDirection: TextDirection.rtl,
      children: [
        // 1. الزر الرئيسي المشغّل للأنيميشن
        Tooltip(
          message: 'نمط الخريطة',
          child: GestureDetector(
            onTap: _toggleLayersMenu,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 250),
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: _isLayersMenuOpen ? AppColors.deepBlack : Colors.white,
                shape: BoxShape.circle,
                border: Border.all(
                  color: _isLayersMenuOpen
                      ? AppColors.deepBlack
                      : AppColors.borderSubtle,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.12),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: AnimatedRotation(
                turns: _isLayersMenuOpen ? 0.25 : 0.0,
                duration: const Duration(milliseconds: 250),
                child: Icon(
                  _isLayersMenuOpen ? Icons.close_rounded : Icons.layers_rounded,
                  color: _isLayersMenuOpen ? Colors.white : AppColors.deepBlack,
                  size: 22,
                ),
              ),
            ),
          ),
        ),

        // 2. الأيقونتان الدائريتان المنبثقتان بأنيميشن
        AnimatedBuilder(
          animation: _expandAnimation,
          builder: (context, child) {
            if (_expandAnimation.value <= 0.01) {
              return const SizedBox.shrink();
            }
            return Row(
              mainAxisSize: MainAxisSize.min,
              textDirection: TextDirection.rtl,
              children: [
                const SizedBox(width: 8),

                // الأيقونة 1: النمط الأبيض الافتراضي
                _buildMiniAnimatedStyleButton(
                  title: 'النمط الأبيض (افتراضي)',
                  icon: Icons.map_outlined,
                  isSelected: _currentStyle == MapStyleType.positron,
                  onTap: () => _selectStyle(MapStyleType.positron),
                  scale: _expandAnimation.value,
                ),

                const SizedBox(width: 8),

                // الأيقونة 2: صور الأقمار الصناعية
                _buildMiniAnimatedStyleButton(
                  title: 'قمر صناعي',
                  icon: Icons.satellite_alt_rounded,
                  isSelected: _currentStyle == MapStyleType.satellite,
                  onTap: () => _selectStyle(MapStyleType.satellite),
                  scale: _expandAnimation.value,
                ),
              ],
            );
          },
        ),
      ],
    );
  }

  Widget _buildMiniAnimatedStyleButton({
    required String title,
    required IconData icon,
    required bool isSelected,
    required VoidCallback onTap,
    required double scale,
  }) {
    return Transform.scale(
      scale: scale,
      alignment: Alignment.centerRight,
      child: Opacity(
        opacity: scale.clamp(0.0, 1.0),
        child: Tooltip(
          message: title,
          child: GestureDetector(
            onTap: onTap,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: isSelected ? AppColors.yemenRed : Colors.white,
                shape: BoxShape.circle,
                border: Border.all(
                  color: isSelected ? AppColors.yemenRed : AppColors.borderSubtle,
                  width: isSelected ? 2.0 : 1.2,
                ),
                boxShadow: [
                  BoxShadow(
                    color: isSelected
                        ? AppColors.yemenRed.withValues(alpha: 0.35)
                        : Colors.black.withValues(alpha: 0.12),
                    blurRadius: 8,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Center(
                child: Icon(
                  icon,
                  color: isSelected ? Colors.white : AppColors.deepBlack,
                  size: 19,
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// Custom Triangle Pin Pointer for professional teardrop pins
class _TrianglePainter extends CustomPainter {
  final Color color;
  _TrianglePainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.fill;
    final path = Path()
      ..moveTo(0, 0)
      ..lineTo(size.width, 0)
      ..lineTo(size.width / 2, size.height)
      ..close();
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant _TrianglePainter oldDelegate) =>
      oldDelegate.color != color;
}
