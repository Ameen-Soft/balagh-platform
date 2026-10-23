import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:cached_network_image/cached_network_image.dart';

import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/media_url_resolver.dart';
import '../../domain/entities/project_entity.dart';

class ProjectCard extends StatelessWidget {
  final ProjectEntity project;

  const ProjectCard({super.key, required this.project});

  Color _getStatusThemeColor(String status) {
    switch (status) {
      case 'in_progress':
      case 'active':
        return AppColors.yemenEmerald;
      case 'planning':
      case 'published':
      case 'draft':
        return AppColors.yemenGold;
      case 'completed':
        return const Color(0xFF2563EB);
      case 'suspended':
      case 'halted':
      default:
        return AppColors.textSecondary;
    }
  }

  Color _getStatusBgColor(String status) {
    switch (status) {
      case 'in_progress':
      case 'active':
        return AppColors.yemenEmeraldLight;
      case 'planning':
      case 'published':
      case 'draft':
        return AppColors.yemenGoldLight;
      case 'completed':
        return const Color(0xFFEFF6FF);
      case 'suspended':
      case 'halted':
      default:
        return const Color(0xFFF3F4F6);
    }
  }

  String _formatCurrency(double amount) {
    final whole = amount.toInt();
    final s = whole.toString();
    final regExp = RegExp(r'\B(?=(\d{3})+(?!\d))');
    return s.replaceAllMapped(regExp, (match) => ',');
  }

  Widget _buildProjectThumbnail() {
    const double size = 68;

    final resolvedUrl = MediaUrlResolver.resolve(
      project.imageUrl ?? project.imagePath,
    );

    Widget imageWidget;
    if (resolvedUrl != null && resolvedUrl.isNotEmpty) {
      imageWidget = CachedNetworkImage(
        imageUrl: resolvedUrl,
        width: size,
        height: size,
        fit: BoxFit.cover,
        placeholder: (context, url) => Container(
          width: size,
          height: size,
          color: const Color(0xFFF1F5F9),
          child: const Center(
            child: SizedBox(
              width: 18,
              height: 18,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: AppColors.yemenRed,
              ),
            ),
          ),
        ),
        errorWidget: (context, url, error) => _buildFallbackImage(size),
      );
    } else {
      imageWidget = _buildFallbackImage(size);
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(14),
      child: imageWidget,
    );
  }

  Widget _buildFallbackImage(double size) {
    return Image.asset(
      'assets/images/ProjectsPageImage.png',
      width: size,
      height: size,
      fit: BoxFit.cover,
      errorBuilder: (context, error, stackTrace) {
        return Container(
          width: size,
          height: size,
          color: const Color(0xFFF1F5F9),
          child: const Icon(
            Icons.apartment_rounded,
            color: AppColors.textSecondary,
            size: 26,
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final statusColor = _getStatusThemeColor(project.status);
    final statusBg = _getStatusBgColor(project.status);
    final validPercentage =
        project.progressPercentage.isNaN ||
            project.progressPercentage.isInfinite
        ? 0.0
        : project.progressPercentage.clamp(0.0, 100.0);
    final ratio = (validPercentage / 100.0).clamp(0.0, 1.0);
    final isCompleted = validPercentage >= 100;

    final agencyName =
        project.departmentName ??
        project.ministryName ??
        'المبادرات والمشاريع التنموية';

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(
          color: AppColors.borderSubtle.withValues(alpha: 0.7),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 14,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(22),
        child: InkWell(
          borderRadius: BorderRadius.circular(22),
          onTap: () {
            context.push('/projects/${project.id}', extra: project);
          },
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Directionality(
              textDirection: TextDirection.rtl,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // 1. Top Row: Project Code Badge (Right) & Status Pill (Left)
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Reference Number Badge
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 5,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF3F4F6),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          project.projectNumber,
                          style: const TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w800,
                            color: AppColors.yemenBlack,
                            letterSpacing: 0.3,
                          ),
                        ),
                      ),

                      // Status Badge
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 5,
                        ),
                        decoration: BoxDecoration(
                          color: statusBg,
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
                            const SizedBox(width: 5),
                            Text(
                              project.statusArabic,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                                color: statusColor,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 14),

                  // 2. Title, Agency Subtitle & Thumbnail Row
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              project.title,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w900,
                                color: AppColors.yemenBlack,
                                height: 1.35,
                              ),
                            ),
                            const SizedBox(height: 6),
                            Text(
                              agencyName,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w600,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      _buildProjectThumbnail(),
                    ],
                  ),

                  const SizedBox(height: 16),

                  // 3. Thick Crowdfunding Gradient Progress Bar (Matches Mockup)
                  Container(
                    height: 28,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: LayoutBuilder(
                      builder: (context, constraints) {
                        final fillWidth = (constraints.maxWidth * ratio).clamp(
                          0.0,
                          constraints.maxWidth,
                        );

                        return Stack(
                          alignment: Alignment.centerRight,
                          children: [
                            // Gradient Fill
                            AnimatedContainer(
                              duration: const Duration(milliseconds: 500),
                              curve: Curves.easeOutCubic,
                              width: fillWidth,
                              height: double.infinity,
                              decoration: BoxDecoration(
                                gradient: LinearGradient(
                                  begin: Alignment.centerRight,
                                  end: Alignment.centerLeft,
                                  colors: isCompleted
                                      ? [
                                          AppColors.yemenEmerald,
                                          const Color(0xFF059669),
                                        ]
                                      : [
                                          AppColors.yemenRed,
                                          const Color(0xFFEA580C),
                                        ],
                                ),
                                borderRadius: BorderRadius.circular(10),
                              ),
                            ),

                            // Percentage Pill Overlay
                            Positioned(
                              left: 10,
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: ratio > 0.8
                                      ? Colors.black.withValues(alpha: 0.25)
                                      : AppColors.yemenRed.withValues(
                                          alpha: 0.1,
                                        ),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  '${validPercentage.toStringAsFixed(1)}%',
                                  style: TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w900,
                                    color: ratio > 0.8
                                        ? Colors.white
                                        : AppColors.yemenRed,
                                  ),
                                ),
                              ),
                            ),
                          ],
                        );
                      },
                    ),
                  ),

                  const SizedBox(height: 10),

                  // 4. Financial Goal vs Raised Row
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'الهدف: ${_formatCurrency(project.targetAmount)} ريال',
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: AppColors.yemenBlack,
                        ),
                      ),
                      Text(
                        'تم جمع: ${_formatCurrency(project.currentAmount)} ريال',
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),

                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 12),
                    child: Divider(height: 1, color: AppColors.borderSubtle),
                  ),

                  // 5. Bottom Footer: Phases Count (Right) & Red Action Pill (Left)
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Executive Phases Count
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(
                            Icons.layers_outlined,
                            size: 16,
                            color: AppColors.textSecondary,
                          ),
                          const SizedBox(width: 5),
                          Text(
                            '${project.phases.isNotEmpty ? project.phases.length : 3} مراحل تنفيذية',
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ],
                      ),

                      // Red CTA Pill Button ("تفاصيل ومساهمة <")
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 8,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.yemenRed,
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: AppColors.yemenRed.withValues(alpha: 0.22),
                              blurRadius: 8,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              'تفاصيل ومساهمة',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            SizedBox(width: 4),
                            Icon(
                              Icons.chevron_left_rounded,
                              color: Colors.white,
                              size: 16,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
