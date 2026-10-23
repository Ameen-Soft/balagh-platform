import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:cached_network_image/cached_network_image.dart';

import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/media_url_resolver.dart';
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
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          content: const Row(
            children: [
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
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        leading: Padding(
          padding: const EdgeInsets.all(8.0),
          child: _buildGlassIconButton(
            icon: Icons.arrow_back_ios_new_rounded,
            onTap: () {
              if (context.canPop()) {
                context.pop();
              } else {
                context.go('/projects');
              }
            },
          ),
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: _buildGlassIconButton(
              icon: Icons.refresh_rounded,
              tooltip: 'تحديث',
              onTap: () => ref
                  .read(projectDetailsNotifierProvider.notifier)
                  .loadDetails(widget.projectId),
            ),
          ),
          const SizedBox(width: 4),
        ],
      ),
      bottomNavigationBar: project != null ? _buildBottomBar(project) : null,
      body: Builder(
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
                    const Icon(
                      Icons.error_outline_rounded,
                      size: 52,
                      color: AppColors.yemenRed,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      state.errorMessage ??
                          'تعذر تحميل تفاصيل المشروع التنموي.',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 14,
                        color: AppColors.textSecondary,
                      ),
                    ),
                    const SizedBox(height: 20),
                    ElevatedButton.icon(
                      onPressed: () => ref
                          .read(projectDetailsNotifierProvider.notifier)
                          .loadDetails(widget.projectId),
                      icon: const Icon(Icons.refresh_rounded, size: 18),
                      label: const Text('إعادة المحاولة'),
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

          if (project == null) {
            return const Center(child: Text('المشروع غير متوفر.'));
          }

          final statusColor = _getStatusColor(project.status);
          final dateFormatter = DateFormat('yyyy/MM/dd', 'ar');
          final currencyFormatter = NumberFormat('#,##0.##', 'ar');
          final remainingAmount = (project.targetAmount - project.currentAmount)
              .clamp(0.0, double.infinity);

          final resolvedImageUrl = MediaUrlResolver.resolve(
            project.imageUrl ?? project.imagePath,
          );

          return RefreshIndicator(
            color: AppColors.yemenRed,
            onRefresh: () async {
              await ref
                  .read(projectDetailsNotifierProvider.notifier)
                  .loadDetails(widget.projectId);
            },
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // 1. Immersive Hero Image Banner with Overlay Details
                  Stack(
                    clipBehavior: Clip.none,
                    children: [
                      Container(
                        height: 330,
                        width: double.infinity,
                        clipBehavior: Clip.antiAlias,
                        decoration: const BoxDecoration(
                          color: AppColors.yemenBlack,
                          borderRadius: BorderRadius.only(
                            bottomLeft: Radius.circular(32),
                            bottomRight: Radius.circular(32),
                          ),
                        ),
                        child: Stack(
                          fit: StackFit.expand,
                          children: [
                            // Real Project Image from Laravel or Default Asset Image
                            if (resolvedImageUrl != null &&
                                resolvedImageUrl.isNotEmpty)
                              CachedNetworkImage(
                                imageUrl: resolvedImageUrl,
                                fit: BoxFit.cover,
                                placeholder: (context, url) => Container(
                                  color: AppColors.yemenBlack,
                                  child: const Center(
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: AppColors.yemenRed,
                                    ),
                                  ),
                                ),
                                errorWidget: (context, url, error) =>
                                    Image.asset(
                                      'assets/images/ProjectsPageImage.png',
                                      fit: BoxFit.cover,
                                    ),
                              )
                            else
                              Image.asset(
                                'assets/images/ProjectsPageImage.png',
                                fit: BoxFit.cover,
                              ),

                            // Gradient Overlay for Text Readability
                            Container(
                              decoration: BoxDecoration(
                                gradient: LinearGradient(
                                  begin: Alignment.topCenter,
                                  end: Alignment.bottomCenter,
                                  colors: [
                                    Colors.black.withValues(alpha: 0.55),
                                    Colors.black.withValues(alpha: 0.35),
                                    AppColors.yemenBlack.withValues(
                                      alpha: 0.92,
                                    ),
                                  ],
                                  stops: const [0.0, 0.4, 1.0],
                                ),
                              ),
                            ),

                            // Content Overlay (Badges, Title, Subtitle, etc.)
                            Padding(
                              padding: const EdgeInsets.fromLTRB(
                                20,
                                100,
                                20,
                                48,
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.end,
                                children: [
                                  // Badges Row (Status & Project Number)
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      // Status Pill
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 12,
                                          vertical: 6,
                                        ),
                                        decoration: BoxDecoration(
                                          color: statusColor.withValues(
                                            alpha: 0.22,
                                          ),
                                          borderRadius: BorderRadius.circular(
                                            20,
                                          ),
                                          border: Border.all(
                                            color: statusColor.withValues(
                                              alpha: 0.6,
                                            ),
                                          ),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Container(
                                              width: 7,
                                              height: 7,
                                              decoration: BoxDecoration(
                                                color: statusColor,
                                                shape: BoxShape.circle,
                                              ),
                                            ),
                                            const SizedBox(width: 6),
                                            Text(
                                              project.statusArabic,
                                              style: const TextStyle(
                                                fontSize: 12,
                                                fontWeight: FontWeight.w800,
                                                color: Colors.white,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),

                                      // Project Reference Number Pill
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 12,
                                          vertical: 6,
                                        ),
                                        decoration: BoxDecoration(
                                          color: Colors.white.withValues(
                                            alpha: 0.15,
                                          ),
                                          borderRadius: BorderRadius.circular(
                                            10,
                                          ),
                                          border: Border.all(
                                            color: Colors.white.withValues(
                                              alpha: 0.2,
                                            ),
                                          ),
                                        ),
                                        child: Text(
                                          project.projectNumber,
                                          style: const TextStyle(
                                            fontWeight: FontWeight.w800,
                                            fontSize: 12,
                                            color: Colors.white,
                                            letterSpacing: 0.5,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 14),

                                  // Project Title
                                  Text(
                                    project.title,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontSize: 20,
                                      fontWeight: FontWeight.w900,
                                      color: Colors.white,
                                      height: 1.35,
                                    ),
                                  ),
                                  const SizedBox(height: 10),

                                  // Ministry / Department & Date Row
                                  Row(
                                    children: [
                                      if (project.departmentName != null ||
                                          project.ministryName != null) ...[
                                        const Icon(
                                          Icons.account_balance_rounded,
                                          size: 15,
                                          color: Colors.white70,
                                        ),
                                        const SizedBox(width: 6),
                                        Expanded(
                                          child: Text(
                                            '${project.ministryName ?? ""}${project.departmentName != null ? " • ${project.departmentName}" : ""}',
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                            style: const TextStyle(
                                              fontSize: 12.5,
                                              color: Colors.white70,
                                              fontWeight: FontWeight.w600,
                                            ),
                                          ),
                                        ),
                                      ],
                                      if (project.createdAt != null) ...[
                                        const SizedBox(width: 10),
                                        const Icon(
                                          Icons.calendar_today_rounded,
                                          size: 13,
                                          color: Colors.white60,
                                        ),
                                        const SizedBox(width: 4),
                                        Text(
                                          dateFormatter.format(
                                            project.createdAt!,
                                          ),
                                          style: const TextStyle(
                                            fontSize: 11.5,
                                            color: Colors.white60,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),

                  // 2. Main Body Content (Overlapping slightly with Hero)
                  Transform.translate(
                    offset: const Offset(0, -26),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Financial Crowdfunding Card
                          Container(
                            padding: const EdgeInsets.all(20),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(22),
                              border: Border.all(
                                color: AppColors.borderSubtle.withValues(
                                  alpha: 0.7,
                                ),
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.05),
                                  blurRadius: 16,
                                  offset: const Offset(0, 6),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                // Card Header
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Row(
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(8),
                                          decoration: BoxDecoration(
                                            color: AppColors.yemenRed
                                                .withValues(alpha: 0.08),
                                            borderRadius: BorderRadius.circular(
                                              10,
                                            ),
                                          ),
                                          child: const Icon(
                                            Icons.pie_chart_outline_rounded,
                                            color: AppColors.yemenRed,
                                            size: 20,
                                          ),
                                        ),
                                        const SizedBox(width: 10),
                                        const Text(
                                          'مؤشر التمويل المجتمعي',
                                          style: TextStyle(
                                            fontSize: 15,
                                            fontWeight: FontWeight.w800,
                                            color: AppColors.yemenBlack,
                                          ),
                                        ),
                                      ],
                                    ),
                                    if (project.contributions.isNotEmpty)
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 10,
                                          vertical: 4,
                                        ),
                                        decoration: BoxDecoration(
                                          color: AppColors.backgroundSubtle,
                                          borderRadius: BorderRadius.circular(
                                            12,
                                          ),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            const Icon(
                                              Icons.people_alt_outlined,
                                              size: 14,
                                              color: AppColors.yemenBlack,
                                            ),
                                            const SizedBox(width: 4),
                                            Text(
                                              '${project.contributions.length} مساهم',
                                              style: const TextStyle(
                                                fontSize: 11.5,
                                                fontWeight: FontWeight.w800,
                                                color: AppColors.yemenBlack,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                  ],
                                ),
                                const SizedBox(height: 18),

                                // Modern Progress Bar
                                ProjectProgressBar(
                                  currentAmount: project.currentAmount,
                                  targetAmount: project.targetAmount,
                                  progressPercentage:
                                      project.progressPercentage,
                                  showAmounts: false,
                                ),
                                const SizedBox(height: 18),

                                // 3-Column Financial Metrics Grid
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    vertical: 14,
                                    horizontal: 10,
                                  ),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF9FAFB),
                                    borderRadius: BorderRadius.circular(16),
                                    border: Border.all(
                                      color: AppColors.borderSubtle,
                                    ),
                                  ),
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: _buildMetricItem(
                                          label: 'تم جمع',
                                          value:
                                              '${currencyFormatter.format(project.currentAmount)} ر.ي',
                                          valueColor: AppColors.yemenRed,
                                        ),
                                      ),
                                      Container(
                                        width: 1,
                                        height: 32,
                                        color: AppColors.borderSubtle,
                                      ),
                                      Expanded(
                                        child: _buildMetricItem(
                                          label: 'الهدف الكلي',
                                          value:
                                              '${currencyFormatter.format(project.targetAmount)} ر.ي',
                                          valueColor: AppColors.yemenBlack,
                                        ),
                                      ),
                                      Container(
                                        width: 1,
                                        height: 32,
                                        color: AppColors.borderSubtle,
                                      ),
                                      Expanded(
                                        child: _buildMetricItem(
                                          label: 'المتبقي',
                                          value: remainingAmount <= 0
                                              ? 'مكتمل 🎉'
                                              : '${currencyFormatter.format(remainingAmount)} ر.ي',
                                          valueColor: remainingAmount <= 0
                                              ? AppColors.yemenEmerald
                                              : AppColors.textSecondary,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),

                          const SizedBox(height: 16),

                          // 3. Project Overview Card
                          Container(
                            padding: const EdgeInsets.all(20),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(22),
                              border: Border.all(
                                color: AppColors.borderSubtle.withValues(
                                  alpha: 0.7,
                                ),
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.02),
                                  blurRadius: 10,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(8),
                                      decoration: BoxDecoration(
                                        color: AppColors.yemenBlack.withValues(
                                          alpha: 0.06,
                                        ),
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                      child: const Icon(
                                        Icons.subject_rounded,
                                        color: AppColors.yemenBlack,
                                        size: 20,
                                      ),
                                    ),
                                    const SizedBox(width: 10),
                                    const Text(
                                      'نبذة وتفاصيل المشروع',
                                      style: TextStyle(
                                        fontSize: 15,
                                        fontWeight: FontWeight.w800,
                                        color: AppColors.yemenBlack,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 12),
                                const Divider(
                                  height: 1,
                                  color: AppColors.borderSubtle,
                                ),
                                const SizedBox(height: 12),
                                Text(
                                  project.description,
                                  style: const TextStyle(
                                    fontSize: 13.5,
                                    color: AppColors.textSecondary,
                                    height: 1.7,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ],
                            ),
                          ),

                          const SizedBox(height: 20),

                          // 4. Executive Phases Section Header
                          Padding(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 4.0,
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Row(
                                  children: [
                                    Icon(
                                      Icons.layers_outlined,
                                      color: AppColors.yemenRed,
                                      size: 22,
                                    ),
                                    SizedBox(width: 8),
                                    Text(
                                      'المراحل التنفيذية للمشروع',
                                      style: TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.w900,
                                        color: AppColors.yemenBlack,
                                      ),
                                    ),
                                  ],
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 10,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: AppColors.yemenBlack,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    '${project.phases.length} مراحل',
                                    style: const TextStyle(
                                      fontSize: 11.5,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.white,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 14),

                          // Phases Timeline Widget
                          PhasesList(phases: project.phases),

                          const SizedBox(height: 16),
                        ],
                      ),
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

  /// Builds a translucent circular glass button for the AppBar
  Widget _buildGlassIconButton({
    required IconData icon,
    required VoidCallback onTap,
    String? tooltip,
  }) {
    return Material(
      color: Colors.black.withValues(alpha: 0.35),
      shape: const CircleBorder(),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Tooltip(
          message: tooltip ?? '',
          child: SizedBox(
            width: 40,
            height: 40,
            child: Icon(icon, color: Colors.white, size: 20),
          ),
        ),
      ),
    );
  }

  /// Helper for the 3-column financial stats
  Widget _buildMetricItem({
    required String label,
    required String value,
    required Color valueColor,
  }) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 11,
            color: AppColors.textMuted,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          textAlign: TextAlign.center,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w900,
            color: valueColor,
          ),
        ),
      ],
    );
  }

  /// Sticky Bottom Bar for contributing to the project
  Widget _buildBottomBar(ProjectEntity project) {
    final canContribute =
        (project.status == 'active' ||
            project.status == 'published' ||
            project.status == 'in_progress') &&
        !project.isFullyFunded;

    return Container(
      padding: EdgeInsets.fromLTRB(
        20,
        14,
        20,
        MediaQuery.of(context).padding.bottom + 14,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        border: const Border(top: BorderSide(color: AppColors.borderSubtle)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 12,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: canContribute
          ? ElevatedButton.icon(
              onPressed: () => _openContributionDialog(project),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.yemenRed,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 15),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                elevation: 0,
              ),
              icon: const Icon(Icons.volunteer_activism_rounded, size: 20),
              label: const Text(
                'المساهمة في دعم المشروع',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
              ),
            )
          : Container(
              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
              decoration: BoxDecoration(
                color: AppColors.backgroundSubtle,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.borderSubtle),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    project.status == 'completed'
                        ? Icons.verified_rounded
                        : Icons.lock_outline_rounded,
                    size: 18,
                    color: project.status == 'completed'
                        ? AppColors.yemenEmerald
                        : AppColors.textSecondary,
                  ),
                  const SizedBox(width: 8),
                  Flexible(
                    child: Text(
                      project.status == 'completed'
                          ? 'اكتمل تمويل هذا المشروع بنجاح، شكراً لجميع المساهمين!'
                          : 'المشروع غير متاح لقبول مساهمات جديدة حالياً.',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
