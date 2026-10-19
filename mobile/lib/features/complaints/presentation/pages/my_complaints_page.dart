import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/widgets/app_empty_state.dart';
import '../../../../core/widgets/app_error_state.dart';
import '../../../auth/application/providers.dart';
import '../../../auth/domain/entities/user_entity.dart';
import '../../../notifications/application/notification_providers.dart';
import '../../application/complaints_providers.dart';
import '../../application/my_complaints_state.dart';
import '../../../home/presentation/widgets/home_bottom_bar.dart';
import '../../../home/presentation/widgets/home_drawer.dart';
import '../widgets/complaint_card.dart';

class MyComplaintsPage extends ConsumerStatefulWidget {
  const MyComplaintsPage({super.key});

  @override
  ConsumerState<MyComplaintsPage> createState() => _MyComplaintsPageState();
}

class _MyComplaintsPageState extends ConsumerState<MyComplaintsPage> {
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
      // Trigger loadMore when within 200px of bottom
      if (maxScroll - currentScroll <= 200) {
        ref.read(myComplaintsNotifierProvider.notifier).loadMore();
      }
    }
  }

  void _onSearchChanged(String query) {
    _debounceTimer?.cancel();
    _debounceTimer = Timer(const Duration(milliseconds: 400), () {
      ref.read(myComplaintsNotifierProvider.notifier).setSearchQuery(query);
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(myComplaintsNotifierProvider);
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;

    ref.listen<MyComplaintsState>(myComplaintsNotifierProvider, (previous, next) {
      if (next.errorMessage != null &&
          next.complaints.isNotEmpty &&
          previous?.errorMessage == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              next.errorMessage!.contains('اتصال') ||
                      next.errorMessage!.contains('SocketException')
                  ? 'تعذر الاتصال بالخادم، تم الاحتفاظ بالبيانات السابقة.'
                  : 'تعذر تحديث البيانات، تم الاحتفاظ بالبيانات السابقة.',
            ),
            backgroundColor: AppColors.deepBlack,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        );
      }
    });

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: const Color(0xFFF8F9FA),
      drawer: const HomeDrawer(),
      bottomNavigationBar: HomeBottomBar(
        currentTab: HomeBottomBarTab.complaints,
        onHomeTap: () => context.go('/home'),
        onMapTap: () => context.push('/map'),
        onCreateComplaintTap: () => context.push('/create-complaint'),
        onComplaintsTap: () {
          if (_scrollController.hasClients) {
            _scrollController.animateTo(
              0,
              duration: const Duration(milliseconds: 300),
              curve: Curves.easeOut,
            );
          }
        },
        onMoreTap: () => _scaffoldKey.currentState?.openDrawer(),
      ),
      body: SafeArea(
        top: false,
        child: Column(
          children: [
            // 1. Beautiful Header with Background Image, Top Bar, Search, and Filter Chips
            _buildHeader(context, ref, user, state.selectedStatus),

            // 2. Complaints List Content with Pull-To-Refresh
            Expanded(
              child: RefreshIndicator(
                color: AppColors.yemenRed,
                onRefresh: () async {
                  await ref
                      .read(myComplaintsNotifierProvider.notifier)
                      .refresh();
                },
                child: _buildBodyContent(state),
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
    String? selectedStatus,
  ) {
    final topPadding = MediaQuery.of(context).padding.top;

    return Stack(
      children: [
        // Heritage Background Image Banner
        Positioned(
          top: 0,
          left: 0,
          right: 0,
          height: topPadding + 140,
          child: Image.asset(
            'assets/images/MyBlaghImage.jpg',
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
              _buildSearchBar(),

              const SizedBox(height: 10),

              // Status Filter Chips
              _buildFilterBar(selectedStatus),

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
                    'سجل بلاغاتي',
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
                    'تابع جميع بلاغاتك وحالتها',
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

  Widget _buildSearchBar() {
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
          ref.read(myComplaintsNotifierProvider.notifier).setSearchQuery(val);
        },
        style: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w600,
          color: AppColors.yemenBlack,
        ),
        decoration: InputDecoration(
          hintText: 'ابحث في بلاغاتك...',
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
          // In RTL prefixIcon appears on the RIGHT side
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
                    ref
                        .read(myComplaintsNotifierProvider.notifier)
                        .setSearchQuery(null);
                  },
                )
              : null,
        ),
      ),
    );
  }

  Widget _buildFilterBar(String? selectedStatus) {
    final List<Map<String, dynamic>> filters = [
      {
        'label': 'الكل',
        'status': null,
        'icon': Icons.tune_rounded,
      },
      {
        'label': 'قيد المعالجة',
        'status': 'in_progress',
        'icon': Icons.access_time_rounded,
        'color': const Color(0xFFDC2626),
      },
      {
        'label': 'تم الحل',
        'status': 'resolved',
        'icon': Icons.check_circle_outline_rounded,
        'color': const Color(0xFF16A34A),
      },
      {
        'label': 'مرفوض',
        'status': 'rejected',
        'icon': Icons.highlight_off_rounded,
        'color': const Color(0xFF6B7280),
      },
      {
        'label': 'قيد المراجعة',
        'status': 'under_review',
        'icon': Icons.access_time_rounded,
        'color': const Color(0xFFD97706),
      },
      {
        'label': 'جديد وارد',
        'status': 'new',
        'icon': Icons.fiber_new_rounded,
        'color': const Color(0xFF2563EB),
      },
      {
        'label': 'مسند للميدان',
        'status': 'assigned',
        'icon': Icons.engineering_rounded,
        'color': const Color(0xFFD97706),
      },
      {
        'label': 'أُعيد فتحها',
        'status': 'reopened',
        'icon': Icons.replay_rounded,
        'color': const Color(0xFF7C3AED),
      },
    ];

    return SizedBox(
      height: 38,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16),
        scrollDirection: Axis.horizontal,
        itemCount: filters.length,
        separatorBuilder: (context, index) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final filter = filters[index];
          final status = filter['status'] as String?;
          final isSelected = selectedStatus == status;
          final IconData icon = filter['icon'] as IconData;
          final Color iconColor = isSelected
              ? Colors.white
              : (filter['color'] as Color? ?? const Color(0xFF6B7280));

          return InkWell(
            onTap: () {
              ref
                  .read(myComplaintsNotifierProvider.notifier)
                  .filterByStatus(status);
            },
            borderRadius: BorderRadius.circular(20),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
              decoration: BoxDecoration(
                color: isSelected ? AppColors.yemenBlack : Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: isSelected
                      ? AppColors.yemenBlack
                      : const Color(0xFFE5E7EB),
                  width: 1.0,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(
                      alpha: isSelected ? 0.08 : 0.02,
                    ),
                    blurRadius: 4,
                    offset: const Offset(0, 1),
                  ),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    icon,
                    size: 14,
                    color: iconColor,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    filter['label'] as String,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight:
                          isSelected ? FontWeight.w800 : FontWeight.w600,
                      color: isSelected ? Colors.white : const Color(0xFF374151),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildBodyContent(MyComplaintsState state) {
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
            onRetry: () =>
                ref.read(myComplaintsNotifierProvider.notifier).loadInitial(),
          ),
        ],
      );
    }

    if (state.complaints.isEmpty) {
      final isFiltered =
          state.selectedStatus != null || (state.searchQuery?.isNotEmpty == true);

      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.12),
          AppEmptyState(
            icon: isFiltered ? Icons.search_off_rounded : Icons.inbox_outlined,
            title: isFiltered
                ? 'لا توجد نتائج مطابقة'
                : 'لا توجد بلاغات مسجلة حتى الآن',
            description: isFiltered
                ? 'لم يتم العثور على أي بلاغات مطابقة لمعايير البحث الحالية.'
                : 'يمكنك تقديم بلاغ جديد والمساهمة في رصد ومعالجة المشكلات التنموية في مجتمعك.',
            actionLabel: isFiltered ? 'إلغاء الفلترة' : 'تقديم بلاغ جديد الآن',
            onAction: () {
              if (isFiltered) {
                _searchController.clear();
                ref.read(myComplaintsNotifierProvider.notifier).filterByStatus(null);
                ref.read(myComplaintsNotifierProvider.notifier).setSearchQuery(null);
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

        final item = state.complaints[index];
        return ComplaintCard(
          complaint: item,
          onTap: () {
            context.push('/complaints/${item.id}', extra: item);
          },
        );
      },
    );
  }
}
