import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/widgets/app_empty_state.dart';
import '../../../../core/widgets/app_error_state.dart';
import '../../../home/presentation/widgets/home_bottom_bar.dart';
import '../../../home/presentation/widgets/home_drawer.dart';
import '../../application/projects_state.dart';
import '../../application/providers/projects_providers.dart';
import '../widgets/project_card.dart';

class ProjectsListPage extends ConsumerStatefulWidget {
  const ProjectsListPage({super.key});

  @override
  ConsumerState<ProjectsListPage> createState() => _ProjectsListPageState();
}

class _ProjectsListPageState extends ConsumerState<ProjectsListPage> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();
  Timer? _debounceTimer;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _scrollController.removeListener(_onScroll);
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.hasClients) {
      final maxScroll = _scrollController.position.maxScrollExtent;
      final currentScroll = _scrollController.position.pixels;
      if (maxScroll - currentScroll <= 200) {
        ref.read(projectsNotifierProvider.notifier).loadMore();
      }
    }
  }

  void _onSearchChanged(String query) {
    _debounceTimer?.cancel();
    _debounceTimer = Timer(const Duration(milliseconds: 350), () {
      ref.read(projectsNotifierProvider.notifier).search(query.trim());
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(projectsNotifierProvider);

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: const Color(0xFFF8F9FA),
      drawer: const HomeDrawer(),
      bottomNavigationBar: HomeBottomBar(
        currentTab: HomeBottomBarTab.none,
        onHomeTap: () => context.go('/home'),
        onMapTap: () => context.push('/map'),
        onCreateComplaintTap: () => context.push('/create-complaint'),
        onComplaintsTap: () => context.push('/my-complaints'),
        onMoreTap: () => _scaffoldKey.currentState?.openDrawer(),
      ),
      body: SafeArea(
        top: false,
        child: Column(
          children: [
            // 1. Top Header Banner with ProjectsPageImage, navigation icons, search bar & filter chips
            _buildHeader(context, state.selectedStatus),

            // 2. Main Projects Content List with Pull-To-Refresh
            Expanded(
              child: RefreshIndicator(
                color: AppColors.yemenRed,
                onRefresh: () async {
                  try {
                    await ref.read(projectsNotifierProvider.notifier).refresh();
                  } catch (e) {
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: const Text('تعذر تحديث المشاريع، تم الاحتفاظ بالبيانات السابقة.'),
                          backgroundColor: AppColors.deepBlack,
                          behavior: SnackBarBehavior.floating,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      );
                    }
                  }
                },
                child: _buildBodyContent(state),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context, String? selectedStatus) {
    final topPadding = MediaQuery.of(context).padding.top;

    return Stack(
      children: [
        // Heritage Background Image Banner
        Positioned(
          top: 0,
          left: 0,
          right: 0,
          height: topPadding + 145,
          child: Image.asset(
            'assets/images/ProjectsPageImage.png',
            fit: BoxFit.cover,
            alignment: Alignment.topCenter,
          ),
        ),

        // Gradient fade to blend softly into page background
        Positioned(
          top: topPadding + 95,
          left: 0,
          right: 0,
          height: 55,
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Colors.white.withValues(alpha: 0.0),
                  const Color(0xFFF8F9FA),
                ],
              ),
            ),
          ),
        ),

        // Foreground Header Contents
        Padding(
          padding: EdgeInsets.only(top: topPadding + 6),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Top Bar: Back button & Title on right (in RTL), Hamburger on left
              _buildTopBar(context),

              const SizedBox(height: 12),

              // Search Bar
              _buildSearchBar(),

              const SizedBox(height: 12),

              // Filter Chips
              _buildFilterChips(selectedStatus),

              const SizedBox(height: 6),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildTopBar(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            // Right Side: Back Chevron + Title & Subtitle
            Row(
              children: [
                GestureDetector(
                  onTap: () {
                    if (context.canPop()) {
                      context.pop();
                    } else {
                      context.go('/home');
                    }
                  },
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.22),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.arrow_forward_ios_rounded,
                      color: Colors.white,
                      size: 16,
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'المشاريع التنموية',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 19,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 0.2,
                        shadows: [
                          Shadow(
                            color: Colors.black54,
                            blurRadius: 4,
                            offset: Offset(0, 1),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'معاً نبني مستقبلاً أفضل',
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.92),
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        shadows: const [
                          Shadow(
                            color: Colors.black45,
                            blurRadius: 3,
                            offset: Offset(0, 1),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),

            // Left Side: Drawer Hamburger Button
            IconButton(
              icon: const Icon(
                Icons.menu_rounded,
                color: Colors.white,
                size: 26,
              ),
              onPressed: () => _scaffoldKey.currentState?.openDrawer(),
              tooltip: 'القائمة',
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSearchBar() {
    return Container(
      height: 48,
      margin: const EdgeInsets.symmetric(horizontal: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(26),
        border: Border.all(color: const Color(0xFFE2E8F0), width: 1.0),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: TextField(
        controller: _searchController,
        textDirection: TextDirection.rtl,
        onChanged: (val) {
          setState(() {});
          _onSearchChanged(val);
        },
        onSubmitted: (val) {
          _debounceTimer?.cancel();
          ref.read(projectsNotifierProvider.notifier).search(val.trim());
        },
        style: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w600,
          color: AppColors.yemenBlack,
        ),
        decoration: InputDecoration(
          hintText: 'ابحث في المشاريع التنموية...',
          hintStyle: const TextStyle(
            color: Color(0xFF94A3B8),
            fontSize: 13,
            fontWeight: FontWeight.w500,
          ),
          hintTextDirection: TextDirection.rtl,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
          prefixIcon: const Icon(
            Icons.search_rounded,
            color: Color(0xFF94A3B8),
            size: 22,
          ),
          suffixIcon: _searchController.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(
                    Icons.close_rounded,
                    size: 18,
                    color: Color(0xFF94A3B8),
                  ),
                  onPressed: () {
                    _searchController.clear();
                    setState(() {});
                    _debounceTimer?.cancel();
                    ref.read(projectsNotifierProvider.notifier).search('');
                  },
                )
              : null,
        ),
      ),
    );
  }

  Widget _buildFilterChips(String? selectedStatus) {
    final List<Map<String, dynamic>> filters = [
      {'label': 'الكل', 'status': null, 'icon': Icons.grid_view_rounded},
      {'label': 'نشطة ومتاحة', 'status': 'active', 'icon': Icons.apartment_rounded},
      {'label': 'قيد التنفيذ', 'status': 'in_progress', 'icon': Icons.engineering_rounded},
      {'label': 'منشورة', 'status': 'published', 'icon': Icons.campaign_outlined},
      {'label': 'مكتملة التمويل', 'status': 'completed', 'icon': Icons.check_circle_outline_rounded},
    ];

    return SizedBox(
      height: 42,
      child: Directionality(
        textDirection: TextDirection.rtl,
        child: ListView.separated(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          scrollDirection: Axis.horizontal,
          itemCount: filters.length,
          separatorBuilder: (context, index) => const SizedBox(width: 8),
          itemBuilder: (context, index) {
            final filter = filters[index];
            final status = filter['status'] as String?;
            final label = filter['label'] as String;
            final icon = filter['icon'] as IconData;
            final isSelected = selectedStatus == status;

            return InkWell(
              onTap: () {
                ref.read(projectsNotifierProvider.notifier).filterByStatus(status);
              },
              borderRadius: BorderRadius.circular(24),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                decoration: BoxDecoration(
                  color: isSelected ? AppColors.yemenBlack : Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(
                    color: isSelected ? AppColors.yemenBlack : const Color(0xFFE2E8F0),
                    width: 1.1,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: isSelected ? 0.08 : 0.02),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      icon,
                      size: 15,
                      color: isSelected ? Colors.white : const Color(0xFF64748B),
                    ),
                    const SizedBox(width: 6),
                    Text(
                      label,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                        color: isSelected ? Colors.white : const Color(0xFF475569),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildBodyContent(ProjectsState state) {
    if (state.isLoading) {
      return const Center(
        child: CircularProgressIndicator(color: AppColors.yemenRed),
      );
    }

    if (state.isError) {
      final isNetwork = state.errorMessage?.contains('اتصال') == true ||
          state.errorMessage?.contains('الإنترنت') == true ||
          state.errorMessage?.contains('شبكة') == true;
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.15),
          AppErrorState(
            message: state.errorMessage,
            isNetworkError: isNetwork,
            onRetry: () => ref.read(projectsNotifierProvider.notifier).refresh(),
          ),
        ],
      );
    }

    if (state.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.15),
          const AppEmptyState(
            icon: Icons.construction_rounded,
            title: 'لا توجد مشاريع تنموية مطابقة',
            description: 'لم يتم العثور على مشاريع وفق معايير البحث أو الفلتر المحدد.',
          ),
        ],
      );
    }

    return ListView.builder(
      controller: _scrollController,
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      itemCount: state.projects.length + (state.isLoadingMore ? 1 : 0),
      itemBuilder: (context, index) {
        if (index == state.projects.length) {
          return const Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Center(
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: AppColors.yemenRed,
              ),
            ),
          );
        }

        final project = state.projects[index];
        return ProjectCard(project: project);
      },
    );
  }
}
