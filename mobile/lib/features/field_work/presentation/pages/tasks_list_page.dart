import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
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
  final List<({String label, String value})> _filterOptions = const [
    (label: 'الكل', value: 'all'),
    (label: 'بانتظار القبول', value: 'pending'),
    (label: 'تم القبول', value: 'accepted'),
    (label: 'قيد التنفيذ', value: 'in_progress'),
    (label: 'مكتملة', value: 'completed'),
  ];

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
          'المهام الميدانية',
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
            // Field worker welcome strip
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              color: Colors.white,
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.deepBlack.withValues(alpha: 0.05),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.engineering_rounded,
                      size: 20,
                      color: AppColors.deepBlack,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'الموظف الميداني: ${user?.name ?? "ميداني"}',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'إجمالي المهام المسندة: ${state.assignments.length}',
                          style: const TextStyle(
                            fontSize: 11,
                            color: AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const Divider(height: 1, color: AppColors.borderSubtle),

            // Horizontal Filter bar
            _buildFilterBar(state.selectedFilter),

            // Main Content Area
            Expanded(
              child: RefreshIndicator(
                color: AppColors.yemenRed,
                onRefresh: () async {
                  await ref
                      .read(fieldWorkNotifierProvider.notifier)
                      .loadAssignments();
                },
                child: _buildContent(state),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterBar(String activeFilter) {
    return Container(
      height: 52,
      color: Colors.white,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        scrollDirection: Axis.horizontal,
        itemCount: _filterOptions.length,
        separatorBuilder: (context, index) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final option = _filterOptions[index];
          final isSelected = activeFilter == option.value;

          return ChoiceChip(
            label: Text(
              option.label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                color: isSelected ? Colors.white : AppColors.textPrimary,
              ),
            ),
            selected: isSelected,
            selectedColor: AppColors.deepBlack,
            backgroundColor: AppColors.backgroundSubtle,
            checkmarkColor: Colors.white,
            showCheckmark: false,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
              side: BorderSide(
                color: isSelected ? AppColors.deepBlack : AppColors.borderSubtle,
              ),
            ),
            onSelected: (_) {
              ref
                  .read(fieldWorkNotifierProvider.notifier)
                  .setFilter(option.value);
            },
          );
        },
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
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.red.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.error_outline_rounded,
                  size: 48,
                  color: AppColors.yemenRed,
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'تعذر تحميل المهام الميدانية',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                state.errorMessage ?? 'تأكد من الاتصال بالإنترنت ثم حاول مجدداً',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 13,
                  color: AppColors.textSecondary,
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: () {
                  ref
                      .read(fieldWorkNotifierProvider.notifier)
                      .loadAssignments();
                },
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: const Text('إعادة المحاولة'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.deepBlack,
                  foregroundColor: Colors.white,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
            ],
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
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.04),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.assignment_turned_in_outlined,
                        size: 56,
                        color: AppColors.textMuted,
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'لا توجد مهام ميدانية مسندة إليك حالياً',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'عندما تسند الإدارة المختصة بلاغاً جديداً إليك، سيظهر هنا مباشرة.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 13,
                        color: AppColors.textSecondary,
                        height: 1.4,
                      ),
                    ),
                    const SizedBox(height: 24),
                    OutlinedButton.icon(
                      onPressed: () {
                        ref
                            .read(fieldWorkNotifierProvider.notifier)
                            .loadAssignments();
                      },
                      icon: const Icon(Icons.refresh_rounded, size: 16),
                      label: const Text('تحديث القائمة'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.deepBlack,
                        side: const BorderSide(color: AppColors.borderSubtle),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
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
            context.push('/tasks/${assignment.id}');
          },
        );
      },
    );
  }
}
