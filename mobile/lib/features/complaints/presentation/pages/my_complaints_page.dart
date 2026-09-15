import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../application/complaints_providers.dart';
import '../../application/my_complaints_state.dart';
import '../widgets/complaint_card.dart';

class MyComplaintsPage extends ConsumerStatefulWidget {
  const MyComplaintsPage({super.key});

  @override
  ConsumerState<MyComplaintsPage> createState() => _MyComplaintsPageState();
}

class _MyComplaintsPageState extends ConsumerState<MyComplaintsPage> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.removeListener(_onScroll);
    _scrollController.dispose();
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

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(myComplaintsNotifierProvider);

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded,
              color: AppColors.yemenBlack, size: 20),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
        ),
        title: const Text(
          'سجل بلاغاتي',
          style: TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'تحديث',
            icon: const Icon(Icons.refresh_rounded, color: AppColors.yemenBlack),
            onPressed: () =>
                ref.read(myComplaintsNotifierProvider.notifier).refresh(),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Status Filter Bar
            _buildFilterBar(state.selectedStatus),

            // Content Area
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
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.yemenRed,
        foregroundColor: Colors.white,
        onPressed: () => context.push('/create-complaint'),
        icon: const Icon(Icons.add_rounded),
        label: const Text(
          'تقديم بلاغ',
          style: TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
    );
  }

  Widget _buildFilterBar(String? selectedStatus) {
    final List<Map<String, String?>> filters = [
      {'label': 'الكل', 'status': null},
      {'label': 'جديد وارد', 'status': 'new'},
      {'label': 'قيد المراجعة', 'status': 'under_review'},
      {'label': 'مسند للميدان', 'status': 'assigned'},
      {'label': 'قيد التنفيذ', 'status': 'in_progress'},
      {'label': 'تم الإنجاز', 'status': 'resolved'},
      {'label': 'أُعيد فتحها', 'status': 'reopened'},
      {'label': 'مغلق', 'status': 'closed'},
      {'label': 'مرفوض', 'status': 'rejected'},
    ];

    return Container(
      height: 48,
      margin: const EdgeInsets.only(top: 8, bottom: 4),
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16),
        scrollDirection: Axis.horizontal,
        itemCount: filters.length,
        separatorBuilder: (context, index) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final filter = filters[index];
          final status = filter['status'];
          final isSelected = selectedStatus == status;

          return ChoiceChip(
            label: Text(filter['label']!),
            selected: isSelected,
            onSelected: (_) {
              ref
                  .read(myComplaintsNotifierProvider.notifier)
                  .filterByStatus(status);
            },
            selectedColor: AppColors.yemenBlack,
            backgroundColor: Colors.white,
            labelStyle: TextStyle(
              fontSize: 12,
              fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
              color: isSelected ? Colors.white : AppColors.textSecondary,
            ),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
              side: BorderSide(
                color: isSelected ? AppColors.yemenBlack : AppColors.borderSubtle,
              ),
            ),
            showCheckmark: false,
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
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline_rounded,
                  size: 48, color: AppColors.yemenRed),
              const SizedBox(height: 16),
              const Text(
                'تعذر استرجاع قائمة البلاغات',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: AppColors.yemenBlack,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                state.errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(
                    fontSize: 12, color: AppColors.textSecondary),
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: () => ref
                    .read(myComplaintsNotifierProvider.notifier)
                    .loadInitial(),
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: const Text('إعادة المحاولة'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.yemenBlack,
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
      return _buildEmptyState(context);
    }

    final totalItems = state.complaints.length + (state.isLoadingMore ? 1 : 0);

    return ListView.separated(
      controller: _scrollController,
      padding: const EdgeInsets.all(16),
      itemCount: totalItems,
      separatorBuilder: (context, index) => const SizedBox(height: 14),
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

  Widget _buildEmptyState(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(32),
      children: [
        const SizedBox(height: 40),
        CircleAvatar(
          radius: 44,
          backgroundColor: AppColors.yemenRedLight,
          child: const Icon(
            Icons.inbox_rounded,
            size: 44,
            color: AppColors.yemenRed,
          ),
        ),
        const SizedBox(height: 20),
        const Text(
          'لا توجد بلاغات مسجلة حتى الآن',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            color: AppColors.yemenBlack,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'يمكنك تقديم بلاغ جديد والمساهمة في رصد ومعالجة المشكلات التنموية في مجتمعك.',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 13,
            color: AppColors.textSecondary,
            height: 1.5,
          ),
        ),
        const SizedBox(height: 28),
        Center(
          child: ElevatedButton.icon(
            onPressed: () => context.push('/create-complaint'),
            icon: const Icon(Icons.add_circle_outline_rounded, size: 20),
            label: const Text('تقديم بلاغ جديد الآن'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.yemenRed,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
          ),
        ),
      ],
    );
  }
}
