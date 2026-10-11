import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/widgets/app_empty_state.dart';
import '../../../../core/widgets/app_error_state.dart';
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
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.15),
          AppEmptyState(
            icon: Icons.inbox_outlined,
            title: 'لا توجد بلاغات مسجلة حتى الآن',
            description:
                'يمكنك تقديم بلاغ جديد والمساهمة في رصد ومعالجة المشكلات التنموية في مجتمعك.',
            actionLabel: 'تقديم بلاغ جديد الآن',
            onAction: () => context.push('/create-complaint'),
          ),
        ],
      );
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

}
