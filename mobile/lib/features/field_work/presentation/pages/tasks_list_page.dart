import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/widgets/app_empty_state.dart';
import '../../../../core/widgets/app_error_state.dart';
import '../../../auth/application/providers.dart';
import '../../application/field_work_state.dart';
import '../../application/providers/field_work_providers.dart';
import '../widgets/task_card.dart';

class TasksListPage extends ConsumerStatefulWidget {
  const TasksListPage({super.key});

  @override
  ConsumerState<TasksListPage> createState() => _TasksListPageState();
}

class _TasksListPageState extends ConsumerState<TasksListPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(fieldWorkNotifierProvider.notifier).loadAssignments();
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(fieldWorkNotifierProvider);
    final user = ref.watch(authNotifierProvider).user;

    final pendingCount = state.assignments.where((a) => a.isPending).length;
    final inProgressCount =
        state.assignments.where((a) => a.isInProgress).length;
    final completedCount = state.assignments.where((a) => a.isCompleted).length;

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
          'سجل المهام الميدانية',
          style: TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'تحديث المهام',
            icon: const Icon(Icons.refresh_rounded, color: AppColors.yemenBlack),
            onPressed: () {
              ref.read(fieldWorkNotifierProvider.notifier).loadAssignments();
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Top Executive Header Banner
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: const BoxDecoration(
                color: Colors.white,
                border: Border(
                  bottom: BorderSide(color: AppColors.borderSubtle),
                ),
              ),
              child: Column(
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: AppColors.deepBlack.withValues(alpha: 0.06),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(
                          Icons.engineering_rounded,
                          size: 24,
                          color: AppColors.deepBlack,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              user?.name ?? 'الموظف الميداني',
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              user?.department != null
                                  ? user!.department!.name
                                  : 'فريق الاستجابة والمعاينة الميدانية',
                              style: const TextStyle(
                                fontSize: 11,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: AppColors.yemenRed.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          '${state.assignments.length} مهام',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: AppColors.yemenRed,
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 12),

                  // Mini Quick Stats Row
                  Row(
                    children: [
                      Expanded(
                        child: _buildMiniStat(
                          label: 'الكل',
                          count: state.assignments.length,
                          color: AppColors.deepBlack,
                          isSelected: state.selectedFilter == 'all',
                          onTap: () => ref
                              .read(fieldWorkNotifierProvider.notifier)
                              .setFilter('all'),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: _buildMiniStat(
                          label: 'بالانتظار',
                          count: pendingCount,
                          color: AppColors.yemenGold,
                          isSelected: state.selectedFilter == 'pending',
                          onTap: () => ref
                              .read(fieldWorkNotifierProvider.notifier)
                              .setFilter('pending'),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: _buildMiniStat(
                          label: 'بالتنفيذ',
                          count: inProgressCount,
                          color: AppColors.yemenRed,
                          isSelected: state.selectedFilter == 'in_progress',
                          onTap: () => ref
                              .read(fieldWorkNotifierProvider.notifier)
                              .setFilter('in_progress'),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: _buildMiniStat(
                          label: 'مكتملة',
                          count: completedCount,
                          color: AppColors.yemenEmerald,
                          isSelected: state.selectedFilter == 'completed',
                          onTap: () => ref
                              .read(fieldWorkNotifierProvider.notifier)
                              .setFilter('completed'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // Main Content Area
            Expanded(
              child: RefreshIndicator(
                color: AppColors.yemenRed,
                onRefresh: () async {
                  try {
                    await ref
                        .read(fieldWorkNotifierProvider.notifier)
                        .loadAssignments(isRefresh: true);
                  } catch (e) {
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: const Text(
                            'تعذر تحديث المهام الميدانية، تم الاحتفاظ بالبيانات السابقة.',
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
                child: _buildContent(state),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMiniStat({
    required String label,
    required int count,
    required Color color,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: isSelected
              ? color.withValues(alpha: 0.12)
              : AppColors.backgroundSubtle,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: isSelected ? color : AppColors.borderSubtle,
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Column(
          children: [
            Text(
              count.toString(),
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.bold,
                color: isSelected ? color : AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                color: isSelected ? color : AppColors.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent(FieldWorkState state) {
    if (state.isLoading && state.assignments.isEmpty) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(AppColors.yemenRed),
        ),
      );
    }

    if (state.isError && state.assignments.isEmpty) {
      final isNetwork = state.errorMessage?.contains('اتصال') == true ||
          state.errorMessage?.contains('الإنترنت') == true ||
          state.errorMessage?.contains('شبكة') == true;
      return LayoutBuilder(
        builder: (context, constraints) => SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: ConstrainedBox(
            constraints: BoxConstraints(minHeight: constraints.maxHeight),
            child: AppErrorState(
              message: state.errorMessage,
              isNetworkError: isNetwork,
              onRetry: () {
                ref
                    .read(fieldWorkNotifierProvider.notifier)
                    .loadAssignments();
              },
            ),
          ),
        ),
      );
    }

    final filtered = state.filteredAssignments;

    if (filtered.isEmpty) {
      return LayoutBuilder(
        builder: (context, constraints) => SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: ConstrainedBox(
            constraints: BoxConstraints(minHeight: constraints.maxHeight),
            child: AppEmptyState(
              icon: Icons.assignment_turned_in_outlined,
              title: 'لا توجد مهام ضمن هذا التصنيف',
              description:
                  'يمكنك اختيار تصنيف آخر من الأعلى أو سحب الشاشة للأسفل لتحديث القائمة.',
              actionLabel: 'عرض جميع المهام',
              onAction: () {
                ref
                    .read(fieldWorkNotifierProvider.notifier)
                    .setFilter('all');
                ref
                    .read(fieldWorkNotifierProvider.notifier)
                    .loadAssignments();
              },
            ),
          ),
        ),
      );
    }


    return ListView.separated(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      itemCount: filtered.length,
      separatorBuilder: (context, index) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final assignment = filtered[index];
        return TaskCard(
          assignment: assignment,
          onTap: () {
            ref
                .read(fieldWorkNotifierProvider.notifier)
                .selectAssignment(assignment.id);
            context.push('/tasks/${assignment.id}', extra: assignment);
          },
        );
      },
    );
  }
}
