import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';

import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/media_url_resolver.dart';
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

    final featuredBannerUrl = state.projects
        .map((p) => MediaUrlResolver.resolve(p.imageUrl ?? p.imagePath))
        .where((url) => url != null && url.isNotEmpty)
        .firstOrNull;

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: AppColors.backgroundSubtle,
      drawer: const HomeDrawer(),
      bottomNavigationBar: HomeBottomBar(
        currentTab: HomeBottomBarTab.none,
        onHomeTap: () => context.go('/home'),
        onMapTap: () => context.push('/map'),
        onCreateComplaintTap: () => context.push('/create-complaint'),
        onComplaintsTap: () => context.push('/my-complaints'),
        onMoreTap: () => _scaffoldKey.currentState?.openDrawer(),
      ),
      body: Column(
        children: [
          // 1. Modern Curved Header with Overlapping Search Bar & Filter Chips
          _buildHeader(
            context,
            state.selectedStatus,
            bannerImageUrl: featuredBannerUrl,
          ),

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
                        content: const Text(
                          'تعذر تحديث المشاريع، تم الاحتفاظ بالبيانات السابقة.',
                        ),
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
    );
  }

  Widget _buildHeader(
    BuildContext context,
    String? selectedStatus, {
    String? bannerImageUrl,
  }) {
    final topPadding = MediaQuery.of(context).padding.top;

    return Container(
      color: AppColors.backgroundSubtle,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Stack(
            clipBehavior: Clip.none,
            children: [
              // Curved Dark / Heritage Banner
              Container(
                width: double.infinity,
                clipBehavior: Clip.antiAlias,
                decoration: const BoxDecoration(
                  color: AppColors.yemenBlack,
                  borderRadius: BorderRadius.only(
                    bottomLeft: Radius.circular(28),
                    bottomRight: Radius.circular(28),
                  ),
                ),
                child: Stack(
                  children: [
                    // Background Image (Real Project Image or Default Heritage Banner)
                    Positioned.fill(
                      child: bannerImageUrl != null && bannerImageUrl.isNotEmpty
                          ? CachedNetworkImage(
                              imageUrl: bannerImageUrl,
                              fit: BoxFit.cover,
                              errorWidget: (context, url, error) => Image.asset(
                                'assets/images/ProjectsPageImage.png',
                                fit: BoxFit.cover,
                              ),
                            )
                          : Image.asset(
                              'assets/images/ProjectsPageImage.png',
                              fit: BoxFit.cover,
                            ),
                    ),
                    // Dark / Heritage Overlay
                    Positioned.fill(
                      child: Container(
                        color: AppColors.yemenBlack.withValues(alpha: 0.68),
                      ),
                    ),
                    // Header Content
                    Padding(
                      padding: EdgeInsets.fromLTRB(16, topPadding + 10, 16, 42),
                      child: _buildTopBar(context),
                    ),
                  ],
                ),
              ),

              // Overlapping Floating Search Bar
              Positioned(
                bottom: -24,
                left: 16,
                right: 16,
                child: _buildSearchBar(),
              ),
            ],
          ),

          const SizedBox(height: 36),

          // Horizontal Status Filter Pills
          _buildFilterChips(selectedStatus),

          const SizedBox(height: 8),
        ],
      ),
    );
  }

  Widget _buildTopBar(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // Right Side: Back Button
          _buildGlassIconButton(
            icon: Icons.arrow_back_ios_new_rounded,
            onTap: () {
              if (context.canPop()) {
                context.pop();
              } else {
                context.go('/home');
              }
            },
          ),

          // Center: Title & Subtitle
          Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text(
                'المشاريع التنموية',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 19,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                'معاً نبني مستقبلاً أفضل',
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.8),
                  fontSize: 11.5,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),

          // Left Side: Drawer Hamburger Button
          _buildGlassIconButton(
            icon: Icons.menu_rounded,
            size: 22,
            onTap: () => _scaffoldKey.currentState?.openDrawer(),
          ),
        ],
      ),
    );
  }

  Widget _buildGlassIconButton({
    required IconData icon,
    required VoidCallback onTap,
    double size = 18,
  }) {
    return Material(
      color: Colors.white.withValues(alpha: 0.14),
      shape: const CircleBorder(),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: SizedBox(
          width: 40,
          height: 40,
          child: Icon(icon, color: Colors.white, size: size),
        ),
      ),
    );
  }

  Widget _buildSearchBar() {
    return Container(
      height: 50,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(25),
        border: Border.all(color: AppColors.borderSubtle, width: 1.2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 14,
            offset: const Offset(0, 4),
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
          fontSize: 13.5,
          fontWeight: FontWeight.w700,
          color: AppColors.yemenBlack,
        ),
        decoration: InputDecoration(
          hintText: 'البحث في المشاريع التنموية...',
          hintStyle: const TextStyle(
            color: AppColors.textMuted,
            fontSize: 13,
            fontWeight: FontWeight.w500,
          ),
          hintTextDirection: TextDirection.rtl,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 18,
            vertical: 13,
          ),
          prefixIcon: const Icon(
            Icons.search_rounded,
            color: AppColors.textSecondary,
            size: 22,
          ),
          suffixIcon: _searchController.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(
                    Icons.close_rounded,
                    size: 18,
                    color: AppColors.textSecondary,
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
      {'label': 'الكل', 'status': null},
      {'label': 'نشطة ومتاحة', 'status': 'active'},
      {'label': 'منشورة', 'status': 'published'},
      {'label': 'قيد التنفيذ', 'status': 'in_progress'},
      {'label': 'مكتملة', 'status': 'completed'},
    ];

    return SizedBox(
      height: 38,
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
            final isSelected = selectedStatus == status;

            return GestureDetector(
              onTap: () {
                ref
                    .read(projectsNotifierProvider.notifier)
                    .filterByStatus(status);
              },
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(
                  horizontal: 18,
                  vertical: 8,
                ),
                decoration: BoxDecoration(
                  color: isSelected ? AppColors.yemenBlack : Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: isSelected
                        ? AppColors.yemenBlack
                        : AppColors.borderSubtle,
                    width: 1.2,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(
                        alpha: isSelected ? 0.08 : 0.015,
                      ),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Center(
                  child: Text(
                    label,
                    style: TextStyle(
                      fontSize: 12.5,
                      fontWeight: isSelected
                          ? FontWeight.w800
                          : FontWeight.w600,
                      color: isSelected
                          ? Colors.white
                          : AppColors.textSecondary,
                    ),
                  ),
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
      final isNetwork =
          state.errorMessage?.contains('اتصال') == true ||
          state.errorMessage?.contains('الإنترنت') == true ||
          state.errorMessage?.contains('شبكة') == true;
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.15),
          AppErrorState(
            message: state.errorMessage,
            isNetworkError: isNetwork,
            onRetry: () =>
                ref.read(projectsNotifierProvider.notifier).refresh(),
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
            description:
                'لم يتم العثور على مشاريع وفق معايير البحث أو الفلتر المحدد.',
          ),
        ],
      );
    }

    return ListView.builder(
      controller: _scrollController,
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 6, 16, 24),
      itemCount: state.projects.length + (state.isLoadingMore ? 1 : 0),
      itemBuilder: (context, index) {
        if (index == state.projects.length) {
          return const Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Center(
              child: CircularProgressIndicator(
                strokeWidth: 2.5,
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
