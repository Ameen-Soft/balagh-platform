import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
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
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
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

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(communityComplaintsNotifierProvider);
    final notifier = ref.read(communityComplaintsNotifierProvider.notifier);

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        centerTitle: false,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.deepBlack,
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Text(
                'مجتمع بادر',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w900,
                  fontSize: 13,
                ),
              ),
            ),
            const SizedBox(width: 8),
            const Text(
              'البلاغات العامة',
              style: TextStyle(
                color: AppColors.deepBlack,
                fontWeight: FontWeight.w800,
                fontSize: 17,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.map_outlined, color: AppColors.deepBlack),
            tooltip: 'عرض الخريطة التفاعلية',
            onPressed: () => context.push('/map'),
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppColors.deepBlack),
            tooltip: 'تحديث',
            onPressed: () => notifier.refresh(),
          ),
          const SizedBox(width: 6),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // 1. Search Bar & Filter Options
            _buildSearchAndFiltersHeader(context, state, notifier),

            // 2. Main Content
            Expanded(
              child: _buildBody(context, state, notifier),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/create-complaint'),
        backgroundColor: AppColors.yemenRed,
        foregroundColor: Colors.white,
        elevation: 4,
        icon: const Icon(Icons.add_rounded),
        label: const Text(
          'تقديم بلاغ',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
        ),
      ),
    );
  }

  Widget _buildSearchAndFiltersHeader(
    BuildContext context,
    CommunityComplaintsState state,
    CommunityComplaintsNotifier notifier,
  ) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Search Field
          TextField(
            controller: _searchController,
            textInputAction: TextInputAction.search,
            onSubmitted: (val) => notifier.setSearchQuery(val),
            decoration: InputDecoration(
              hintText: 'ابحث في البلاغات العامة أو رقم المرجع...',
              hintStyle: const TextStyle(
                color: AppColors.textMuted,
                fontSize: 13,
              ),
              filled: true,
              fillColor: AppColors.backgroundSubtle,
              prefixIcon: const Icon(
                Icons.search_rounded,
                color: AppColors.textSecondary,
                size: 20,
              ),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded, size: 18),
                      onPressed: () {
                        _searchController.clear();
                        notifier.setSearchQuery(null);
                      },
                    )
                  : null,
              contentPadding: const EdgeInsets.symmetric(vertical: 10),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: AppColors.borderSubtle),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: AppColors.borderSubtle),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(
                  color: AppColors.yemenRed,
                  width: 1.5,
                ),
              ),
            ),
          ),
          const SizedBox(height: 10),

          // Filters Row: Sorting & Status
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            child: Row(
              children: [
                // Sort by: Popular (الأكثر تفاعلاً)
                _buildSortChip(
                  label: 'الأكثر تفاعلاً',
                  icon: Icons.local_fire_department_rounded,
                  isSelected: state.sortBy == 'popular',
                  onTap: () => notifier.setSortBy('popular'),
                ),
                const SizedBox(width: 8),

                // Sort by: Latest (الأحدث)
                _buildSortChip(
                  label: 'الأحدث تسجيلاً',
                  icon: Icons.access_time_rounded,
                  isSelected: state.sortBy == 'latest',
                  onTap: () => notifier.setSortBy('latest'),
                ),
                const SizedBox(width: 8),

                // Status Filter Menu
                _buildStatusMenu(state, notifier),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSortChip({
    required String label,
    required IconData icon,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.yemenRed : AppColors.backgroundSubtle,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: isSelected ? AppColors.yemenRed : AppColors.borderSubtle,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 15,
              color: isSelected ? Colors.white : AppColors.deepBlack,
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                color: isSelected ? Colors.white : AppColors.deepBlack,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusMenu(
    CommunityComplaintsState state,
    CommunityComplaintsNotifier notifier,
  ) {
    final hasStatus = state.selectedStatus != null;

    return PopupMenuButton<String?>(
      initialValue: state.selectedStatus,
      onSelected: (val) => notifier.filterByStatus(val),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      itemBuilder: (context) => [
        const PopupMenuItem<String?>(
          value: null,
          child: Text('جميع الحالات'),
        ),
        const PopupMenuDivider(),
        const PopupMenuItem<String?>(
          value: 'new',
          child: Text('جديد وارد'),
        ),
        const PopupMenuItem<String?>(
          value: 'in_progress',
          child: Text('قيد التنفيذ'),
        ),
        const PopupMenuItem<String?>(
          value: 'resolved',
          child: Text('تم الإنجاز'),
        ),
      ],
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
        decoration: BoxDecoration(
          color: hasStatus ? AppColors.deepBlack : AppColors.backgroundSubtle,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: hasStatus ? AppColors.deepBlack : AppColors.borderSubtle,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              Icons.tune_rounded,
              size: 15,
              color: hasStatus ? Colors.white : AppColors.deepBlack,
            ),
            const SizedBox(width: 6),
            Text(
              hasStatus ? 'الحالة: ${state.selectedStatus}' : 'تصفية الحالة',
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
    );
  }

  Widget _buildBody(
    BuildContext context,
    CommunityComplaintsState state,
    CommunityComplaintsNotifier notifier,
  ) {
    if (state.isLoadingInitial) {
      return const Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircularProgressIndicator(color: AppColors.yemenRed),
            SizedBox(height: 14),
            Text(
              'جاري تحميل بلاغات المجتمع...',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
          ],
        ),
      );
    }

    if (state.errorMessage != null && state.complaints.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(28.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.yemenRedLight,
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.wifi_off_rounded,
                  size: 36,
                  color: AppColors.yemenRed,
                ),
              ),
              const SizedBox(height: 16),
              Text(
                state.errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: AppColors.deepBlack,
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: () => notifier.loadInitial(),
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: const Text('إعادة المحاولة'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.deepBlack,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (state.complaints.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(28.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: AppColors.backgroundSubtle,
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.forum_outlined,
                  size: 48,
                  color: AppColors.textMuted,
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'لا توجد بلاغات عامة مطابقة حالياً',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                  color: AppColors.deepBlack,
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'كن أول من يشارك بلاغاً مع مجتمع بادر لتسليط الضوء على احتياجات منطقتك.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 12,
                  color: AppColors.textSecondary,
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: () => context.push('/create-complaint'),
                icon: const Icon(Icons.add_rounded, size: 18),
                label: const Text('تقديم بلاغ مجتمعي'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.yemenRed,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      color: AppColors.yemenRed,
      onRefresh: () => notifier.refresh(),
      child: ListView.separated(
        controller: _scrollController,
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        itemCount: state.complaints.length + (state.isLoadingMore ? 1 : 0),
        separatorBuilder: (_, __) => const SizedBox(height: 12),
        itemBuilder: (context, index) {
          if (index >= state.complaints.length) {
            return const Center(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 16.0),
                child: CircularProgressIndicator(
                  strokeWidth: 2.5,
                  color: AppColors.yemenRed,
                ),
              ),
            );
          }

          final complaint = state.complaints[index];
          return CommunityComplaintCard(
            complaint: complaint,
            onTap: () =>
                context.push('/complaints/${complaint.id}', extra: complaint),
            onLikePressed: () {
              notifier.toggleLike(complaint.id).catchError((err) {
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text(
                        'تعذر تسجيل التفاعل. يرجى التحقق من الاتصال بالشبكة.',
                      ),
                      duration: Duration(seconds: 2),
                    ),
                  );
                }
              });
            },
            onMapPressed: () => context.push('/map'),
          );
        },
      ),
    );
  }
}
