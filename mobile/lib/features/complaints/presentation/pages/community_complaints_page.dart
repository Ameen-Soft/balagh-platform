import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/widgets/app_empty_state.dart';
import '../../../../core/widgets/app_error_state.dart';
import '../../../auth/application/providers.dart';
import '../../../auth/domain/entities/user_entity.dart';
import '../../../home/presentation/widgets/home_bottom_bar.dart';
import '../../../home/presentation/widgets/home_drawer.dart';
import '../../../notifications/application/notification_providers.dart';
import '../../application/community_complaints_notifier.dart';
import '../../application/community_complaints_state.dart';
import '../../application/complaints_providers.dart';
import '../widgets/community_complaint_card.dart';

class CommunityComplaintsPage extends ConsumerStatefulWidget {
  const CommunityComplaintsPage({super.key});

  @override
  ConsumerState<CommunityComplaintsPage> createState() =>
      _CommunityComplaintsPageState();
}

class _CommunityComplaintsPageState
    extends ConsumerState<CommunityComplaintsPage> {
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
    if (!_scrollController.hasClients) return;
    final maxScroll = _scrollController.position.maxScrollExtent;
    final currentScroll = _scrollController.position.pixels;
    if (currentScroll >= maxScroll - 250) {
      ref.read(communityComplaintsNotifierProvider.notifier).loadMore();
    }
  }

  void _onSearchChanged(String query) {
    _debounceTimer?.cancel();
    _debounceTimer = Timer(const Duration(milliseconds: 400), () {
      ref
          .read(communityComplaintsNotifierProvider.notifier)
          .setSearchQuery(query);
    });
  }

  void _showStatusFilterSheet(
    BuildContext context,
    CommunityComplaintsState state,
    CommunityComplaintsNotifier notifier,
  ) {
    final statuses = [
      {'label': 'جميع الحالات', 'status': null},
      {'label': 'جاري المعالجة', 'status': 'in_progress'},
      {'label': 'تم الحل', 'status': 'resolved'},
      {'label': 'قيد المراجعة', 'status': 'under_review'},
      {'label': 'مقترح', 'status': 'suggestion'},
      {'label': 'جديد وارد', 'status': 'new'},
      {'label': 'مسند للميدان', 'status': 'assigned'},
      {'label': 'مرفوض', 'status': 'rejected'},
    ];

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      backgroundColor: Colors.white,
      builder: (bottomSheetContext) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: const Color(0xFFE5E7EB),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'تصنيف البلاغات حسب الحالة',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: AppColors.yemenBlack,
                  ),
                ),
                const SizedBox(height: 14),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: statuses.map((item) {
                    final status = item['status'];
                    final label = item['label']!;
                    final isSelected = state.selectedStatus == status;

                    return InkWell(
                      onTap: () {
                        notifier.filterByStatus(status);
                        Navigator.of(bottomSheetContext).pop();
                      },
                      borderRadius: BorderRadius.circular(20),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 8,
                        ),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? AppColors.yemenBlack
                              : const Color(0xFFF3F4F6),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          label,
                          style: TextStyle(
                            fontSize: 12.5,
                            fontWeight: isSelected
                                ? FontWeight.w800
                                : FontWeight.w600,
                            color: isSelected
                                ? Colors.white
                                : const Color(0xFF374151),
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 12),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(communityComplaintsNotifierProvider);
    final notifier = ref.read(communityComplaintsNotifierProvider.notifier);
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;

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
            // 1. Beautiful Header with Background Image, Top Bar, Search, and Filter/Sort Chips
            _buildHeader(context, ref, user, state, notifier),

            // 2. Complaints List Content with Pull-To-Refresh
            Expanded(
              child: RefreshIndicator(
                color: AppColors.yemenRed,
                onRefresh: () async {
                  await notifier.refresh();
                },
                child: _buildBody(context, state, notifier),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader(
    BuildContext context,
    WidgetRef ref,
    UserEntity? user,
    CommunityComplaintsState state,
    CommunityComplaintsNotifier notifier,
  ) {
    final topPadding = MediaQuery.of(context).padding.top;

    return Stack(
      children: [
        // Heritage Background Image Banner (PublicBlaghImage)
        Positioned(
          top: 0,
          left: 0,
          right: 0,
          height: topPadding + 140,
          child: Image.asset(
            'assets/images/PublicBlaghImage.jpg',
            fit: BoxFit.cover,
            alignment: Alignment.topCenter,
          ),
        ),

        // Gradient fade to blend softly into background
        Positioned(
          top: topPadding + 85,
          left: 0,
          right: 0,
          height: 60,
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
          padding: EdgeInsets.only(top: topPadding + 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Top Bar: Hamburger on left, Centered Title/Subtitle, Bell & Avatar on right
              _buildTopBar(context, ref, user),

              const SizedBox(height: 8),

              // Search Bar
              _buildSearchBar(notifier),

              const SizedBox(height: 10),

              // Filter & Sort Row (الأكثر تفاعلاً، الأحدث تسجيلاً، تصنيف الحالة)
              _buildFilterAndSortRow(state, notifier),

              const SizedBox(height: 6),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildTopBar(BuildContext context, WidgetRef ref, UserEntity? user) {
    final unreadCount = ref.watch(unreadNotificationsCountProvider);
    final hasUnread = unreadCount > 0;

    return Directionality(
      textDirection: TextDirection.ltr,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16.0),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            // Left: Hamburger Menu Icon
            IconButton(
              icon: const Icon(
                Icons.menu_rounded,
                color: AppColors.yemenBlack,
                size: 28,
              ),
              tooltip: 'القائمة الرئيسية',
              onPressed: () => _scaffoldKey.currentState?.openDrawer(),
            ),

            // Center: Title and Subtitle
            const Expanded(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'البلاغات العامة',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: AppColors.yemenBlack,
                      fontWeight: FontWeight.w900,
                      fontSize: 22,
                      letterSpacing: -0.3,
                    ),
                  ),
                  SizedBox(height: 1),
                  Text(
                    'معاً لمدينة أجمل وخدمات أفضل',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: Color(0xFF6B7280),
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),

            // Right: Notification Bell & User Avatar
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Notification Bell with Red Badge
                Stack(
                  clipBehavior: Clip.none,
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.notifications_none_rounded,
                        color: AppColors.yemenBlack,
                        size: 26,
                      ),
                      tooltip: 'الإشعارات',
                      onPressed: () => context.push('/notifications'),
                    ),
                    if (hasUnread)
                      Positioned(
                        top: 8,
                        right: 8,
                        child: Container(
                          width: 9,
                          height: 9,
                          decoration: const BoxDecoration(
                            color: AppColors.yemenRed,
                            shape: BoxShape.circle,
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(width: 4),

                // User Profile Avatar
                GestureDetector(
                  onTap: () => context.push('/profile'),
                  child: _buildUserAvatar(user),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildUserAvatar(UserEntity? user) {
    final name = user?.name.trim() ?? '';
    final initial = name.isNotEmpty ? name.characters.first : 'م';

    return Container(
      width: 38,
      height: 38,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        border: Border.all(color: const Color(0xFFE5E7EB), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipOval(
        child: Container(
          color: AppColors.yemenBlack,
          alignment: Alignment.center,
          child: Text(
            initial,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 17,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSearchBar(CommunityComplaintsNotifier notifier) {
    return Container(
      height: 46,
      margin: const EdgeInsets.symmetric(horizontal: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: const Color(0xFFE5E7EB), width: 1.0),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.035),
            blurRadius: 8,
            offset: const Offset(0, 2),
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
          notifier.setSearchQuery(val);
        },
        style: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w600,
          color: AppColors.yemenBlack,
        ),
        decoration: InputDecoration(
          hintText: 'ابحث في البلاغات العامة...',
          hintStyle: const TextStyle(
            color: Color(0xFF9CA3AF),
            fontSize: 13,
            fontWeight: FontWeight.w500,
          ),
          hintTextDirection: TextDirection.rtl,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 11,
          ),
          prefixIcon: const Icon(
            Icons.search_rounded,
            color: Color(0xFF9CA3AF),
            size: 22,
          ),
          suffixIcon: _searchController.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(
                    Icons.close_rounded,
                    size: 18,
                    color: Color(0xFF9CA3AF),
                  ),
                  onPressed: () {
                    _searchController.clear();
                    setState(() {});
                    _debounceTimer?.cancel();
                    notifier.setSearchQuery(null);
                  },
                )
              : null,
        ),
      ),
    );
  }

  Widget _buildFilterAndSortRow(
    CommunityComplaintsState state,
    CommunityComplaintsNotifier notifier,
  ) {
    final isPopular = state.sortBy == 'popular';
    final isLatest = state.sortBy == 'latest';
    final hasStatus = state.selectedStatus != null;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          // 1. الأكثر تفاعلاً (Popular)
          Expanded(
            child: InkWell(
              onTap: () => notifier.setSortBy('popular'),
              borderRadius: BorderRadius.circular(20),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: isPopular ? AppColors.yemenRed : Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: isPopular
                        ? AppColors.yemenRed
                        : const Color(0xFFE5E7EB),
                    width: 1.0,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: isPopular
                          ? AppColors.yemenRed.withValues(alpha: 0.25)
                          : Colors.black.withValues(alpha: 0.02),
                      blurRadius: 4,
                      offset: const Offset(0, 1),
                    ),
                  ],
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.local_fire_department_rounded,
                      size: 16,
                      color: isPopular ? Colors.white : AppColors.yemenRed,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'الأكثر تفاعلاً',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight:
                            isPopular ? FontWeight.w800 : FontWeight.w600,
                        color: isPopular
                            ? Colors.white
                            : const Color(0xFF374151),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          const SizedBox(width: 8),

          // 2. الأحدث تسجيلاً (Latest)
          Expanded(
            child: InkWell(
              onTap: () => notifier.setSortBy('latest'),
              borderRadius: BorderRadius.circular(20),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: isLatest ? AppColors.yemenRed : Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: isLatest
                        ? AppColors.yemenRed
                        : const Color(0xFFE5E7EB),
                    width: 1.0,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: isLatest
                          ? AppColors.yemenRed.withValues(alpha: 0.25)
                          : Colors.black.withValues(alpha: 0.02),
                      blurRadius: 4,
                      offset: const Offset(0, 1),
                    ),
                  ],
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.access_time_rounded,
                      size: 15,
                      color: isLatest ? Colors.white : const Color(0xFF6B7280),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'الأحدث تسجيلاً',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight:
                            isLatest ? FontWeight.w800 : FontWeight.w600,
                        color: isLatest
                            ? Colors.white
                            : const Color(0xFF374151),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          const SizedBox(width: 8),

          // 3. تصنيف الحالة (Status Filter)
          Expanded(
            child: InkWell(
              onTap: () => _showStatusFilterSheet(context, state, notifier),
              borderRadius: BorderRadius.circular(20),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: hasStatus ? AppColors.yemenBlack : Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: hasStatus
                        ? AppColors.yemenBlack
                        : const Color(0xFFE5E7EB),
                    width: 1.0,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(
                        alpha: hasStatus ? 0.08 : 0.02,
                      ),
                      blurRadius: 4,
                      offset: const Offset(0, 1),
                    ),
                  ],
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.filter_alt_outlined,
                      size: 15,
                      color: hasStatus
                          ? Colors.white
                          : const Color(0xFF6B7280),
                    ),
                    const SizedBox(width: 4),
                    Flexible(
                      child: Text(
                        hasStatus
                            ? _formatStatusShort(state.selectedStatus!)
                            : 'تصنيف الحالة',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight:
                              hasStatus ? FontWeight.w800 : FontWeight.w600,
                          color: hasStatus
                              ? Colors.white
                              : const Color(0xFF374151),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _formatStatusShort(String status) {
    switch (status) {
      case 'in_progress':
      case 'assigned':
        return 'قيد المعالجة';
      case 'resolved':
      case 'closed':
        return 'تم الحل';
      case 'under_review':
        return 'قيد المراجعة';
      case 'suggestion':
        return 'مقترح';
      case 'rejected':
        return 'مرفوض';
      case 'new':
        return 'جديد';
      default:
        return 'حالة محددة';
    }
  }

  Widget _buildBody(
    BuildContext context,
    CommunityComplaintsState state,
    CommunityComplaintsNotifier notifier,
  ) {
    if (state.isLoadingInitial) {
      return const Center(
        child: CircularProgressIndicator(color: AppColors.yemenRed),
      );
    }

    if (state.errorMessage != null && state.complaints.isEmpty) {
      final isNetwork = state.errorMessage!.contains('اتصال') ||
          state.errorMessage!.contains('الإنترنت') ||
          state.errorMessage!.contains('شبكة');
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.15),
          AppErrorState(
            message: state.errorMessage,
            isNetworkError: isNetwork,
            onRetry: () => notifier.loadInitial(),
          ),
        ],
      );
    }

    if (state.complaints.isEmpty) {
      final isFiltered = state.selectedStatus != null ||
          state.selectedCategoryId != null ||
          (state.searchQuery?.isNotEmpty == true);

      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.12),
          AppEmptyState(
            icon: isFiltered ? Icons.search_off_rounded : Icons.campaign_outlined,
            title: isFiltered
                ? 'لا توجد نتائج مطابقة'
                : 'لا توجد بلاغات عامة حتى الآن',
            description: isFiltered
                ? 'لم نتمكن من العثور على أي بلاغات عامة وفقاً لمعايير البحث الحالية.'
                : 'كن أول من يشارك بلاغاً عاماً ويساهم في تطوير المجتمع وتحسين الخدمات.',
            actionLabel: isFiltered ? 'إلغاء الفلترة' : 'تقديم بلاغ جديد الآن',
            onAction: () {
              if (isFiltered) {
                _searchController.clear();
                notifier.filterByStatus(null);
                notifier.filterByCategory(null);
                notifier.setSearchQuery(null);
              } else {
                context.push('/create-complaint');
              }
            },
          ),
        ],
      );
    }

    final totalItems = state.complaints.length + (state.isLoadingMore ? 1 : 0);

    return ListView.separated(
      controller: _scrollController,
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.only(
        top: 6,
        left: 16,
        right: 16,
        bottom: 24,
      ),
      itemCount: totalItems,
      separatorBuilder: (context, index) => const SizedBox(height: 10),
      itemBuilder: (context, index) {
        if (index == state.complaints.length) {
          return const Padding(
            padding: EdgeInsets.symmetric(vertical: 16.0),
            child: Center(
              child: SizedBox(
                width: 24,
                height: 24,
                child: CircularProgressIndicator(
                  strokeWidth: 2.5,
                  color: AppColors.yemenRed,
                ),
              ),
            ),
          );
        }

        final complaint = state.complaints[index];
        return CommunityComplaintCard(
          complaint: complaint,
          onTap: () {
            context.push('/complaints/${complaint.id}', extra: complaint);
          },
          onLikePressed: () {
            notifier.toggleLike(complaint.id);
          },
        );
      },
    );
  }
}
