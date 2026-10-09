import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../../core/constants/app_colors.dart';
import '../../domain/entities/project_entity.dart';
import '../widgets/contribution_dialog.dart';
import '../widgets/phases_list.dart';
import '../widgets/progress_bar.dart';
import '../../application/providers/projects_providers.dart';

class ProjectDetailsPage extends ConsumerStatefulWidget {
  final int projectId;
  final ProjectEntity? initialProject;

  const ProjectDetailsPage({
    super.key,
    required this.projectId,
    this.initialProject,
  });

  @override
  ConsumerState<ProjectDetailsPage> createState() => _ProjectDetailsPageState();
}

class _ProjectDetailsPageState extends ConsumerState<ProjectDetailsPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref
          .read(projectDetailsNotifierProvider.notifier)
          .loadDetails(widget.projectId, initialProject: widget.initialProject);
    });
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'active':
      case 'published':
        return AppColors.yemenEmerald;
      case 'completed':
        return const Color(0xFF2563EB);
      case 'in_progress':
        return AppColors.yemenGold;
      case 'suspended':
        return AppColors.textSecondary;
      default:
        return AppColors.yemenRed;
    }
  }

  void _openContributionDialog(ProjectEntity project) async {
    final success = await ContributionDialog.show(
      context,
      project: project,
      onConfirmContribution: (amount) async {
        return await ref
            .read(projectDetailsNotifierProvider.notifier)
            .contribute(amount: amount);
      },
    );

    if (success == true && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: AppColors.yemenEmerald,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          content: Row(
            children: const [
              Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  'شكراً لك! تم تسجيل مساهمتك التنموية وتحديث بيانات المشروع بنجاح.',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                ),
              ),
            ],
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(projectDetailsNotifierProvider);
    final project = state.project ?? widget.initialProject;

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
              context.go('/projects');
            }
          },
        ),
        title: Text(
          project?.projectNumber ?? 'تفاصيل المشروع',
          style: const TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 17,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'تحديث',
            icon: const Icon(Icons.refresh_rounded, color: AppColors.yemenBlack),
            onPressed: () => ref
                .read(projectDetailsNotifierProvider.notifier)
                .loadDetails(widget.projectId),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: Builder(
          builder: (context) {
            if (state.isLoading && project == null) {
              return const Center(
                child: CircularProgressIndicator(color: AppColors.yemenRed),
              );
            }

            if (state.isError && project == null) {
              return Center(
                child: Padding(
                  padding: const EdgeInsets.all(24.0),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.error_outline_rounded,
                          size: 48, color: AppColors.yemenRed),
                      const SizedBox(height: 16),
                      Text(
                        state.errorMessage ?? 'تعذر تحميل تفاصيل المشروع التنموي.',
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          fontSize: 14,
                          color: AppColors.textSecondary,
                        ),
                      ),
                      const SizedBox(height: 20),
                      ElevatedButton(
                        onPressed: () => ref
                            .read(projectDetailsNotifierProvider.notifier)
                            .loadDetails(widget.projectId),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.yemenRed,
                          foregroundColor: Colors.white,
                        ),
                        child: const Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                ),
              );
            }

            if (project == null) {
              return const Center(child: Text('المشروع غير متوفر.'));
            }

            final statusColor = _getStatusColor(project.status);
            final dateFormatter = DateFormat('yyyy/MM/dd', 'ar');
            final canContribute =
                project.status == 'active' || project.status == 'published';

            return RefreshIndicator(
              color: AppColors.yemenRed,
              onRefresh: () async {
                await ref
                    .read(projectDetailsNotifierProvider.notifier)
                    .loadDetails(widget.projectId);
              },
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // 1. Hero Card: Status & Title & Ministry
                    Container(
                      padding: const EdgeInsets.all(18),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: AppColors.borderSubtle),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.03),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 10, vertical: 5),
                                decoration: BoxDecoration(
                                  color: AppColors.yemenBlack.withValues(alpha: 0.06),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  project.projectNumber,
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                    color: AppColors.yemenBlack,
                                  ),
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 12, vertical: 5),
                                decoration: BoxDecoration(
                                  color: statusColor.withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Container(
                                      width: 6,
                                      height: 6,
                                      decoration: BoxDecoration(
                                        color: statusColor,
                                        shape: BoxShape.circle,
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                    Text(
                                      project.statusArabic,
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                        color: statusColor,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 14),

                          Text(
                            project.title,
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w900,
                              color: AppColors.yemenBlack,
                              height: 1.3,
                            ),
                          ),
                          const SizedBox(height: 10),

                          if (project.departmentName != null ||
                              project.ministryName != null) ...[
                            Row(
                              children: [
                                const Icon(Icons.account_balance_outlined,
                                    size: 16, color: AppColors.textSecondary),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    '${project.ministryName ?? ""} ${project.departmentName != null ? "• ${project.departmentName}" : ""}',
                                    style: const TextStyle(
                                      fontSize: 13,
                                      color: AppColors.textSecondary,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                          ],

                          if (project.createdAt != null) ...[
                            Row(
                              children: [
                                const Icon(Icons.access_time_rounded,
                                    size: 16, color: AppColors.textMuted),
                                const SizedBox(width: 6),
                                Text(
                                  'تاريخ النشر: ${dateFormatter.format(project.createdAt!)}',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    color: AppColors.textMuted,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // 2. Financial Progress Card
                    Container(
                      padding: const EdgeInsets.all(18),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: AppColors.borderSubtle),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: const [
                              Text(
                                'حالة التمويل المجتمعي',
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.yemenBlack,
                                ),
                              ),
                              Icon(Icons.volunteer_activism_outlined,
                                  color: AppColors.yemenEmerald, size: 20),
                            ],
                          ),
                          const SizedBox(height: 16),

                          ProjectProgressBar(
                            currentAmount: project.currentAmount,
                            targetAmount: project.targetAmount,
                            progressPercentage: project.progressPercentage,
                          ),
                          const SizedBox(height: 14),

                          // Contributions count if any
                          if (project.contributions.isNotEmpty) ...[
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: AppColors.backgroundSubtle,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.people_outline_rounded,
                                      size: 16, color: AppColors.textSecondary),
                                  const SizedBox(width: 6),
                                  Text(
                                    'إجمالي المساهمات المسجلة: ${project.contributions.length} مساهمة',
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                      color: AppColors.yemenBlack,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // 3. Project Description
                    Container(
                      padding: const EdgeInsets.all(18),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: AppColors.borderSubtle),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'نظرة عامة على المشروع',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w800,
                              color: AppColors.yemenBlack,
                            ),
                          ),
                          const SizedBox(height: 10),
                          Text(
                            project.description,
                            style: const TextStyle(
                              fontSize: 14,
                              color: AppColors.textSecondary,
                              height: 1.6,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // 4. Project Phases Section
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'المراحل التنفيذية للمشروع',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: AppColors.yemenBlack,
                          ),
                        ),
                        Text(
                          '${project.phases.length} مراحل',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textMuted,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    PhasesList(phases: project.phases),

                    const SizedBox(height: 24),

                    // 5. Action Button: Support / Contribute (Simulated)
                    if (canContribute) ...[
                      ElevatedButton.icon(
                        onPressed: () => _openContributionDialog(project),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.yemenRed,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                          elevation: 2,
                        ),
                        icon: const Icon(Icons.volunteer_activism_rounded),
                        label: const Text(
                          'المساهمة في دعم المشروع (محاكاة)',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ] else ...[
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.backgroundSubtle,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.borderSubtle),
                        ),
                        child: Center(
                          child: Text(
                            project.status == 'completed'
                                ? 'اكتمل تمويل هذا المشروع بنجاح، شكراً لجميع المساهمين!'
                                : 'المشروع غير متاح لقبول مساهمات جديدة حالياً.',
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ),
                      ),
                    ],

                    const SizedBox(height: 24),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}
