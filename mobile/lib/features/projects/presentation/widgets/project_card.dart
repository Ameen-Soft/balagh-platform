import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../domain/entities/project_entity.dart';

class ProjectCard extends StatelessWidget {
  final ProjectEntity project;

  const ProjectCard({
    super.key,
    required this.project,
  });

  Color _getStatusThemeColor(String status) {
    switch (status) {
      case 'in_progress':
      case 'active':
        return const Color(0xFF059669); // Emerald green
      case 'planning':
      case 'published':
      case 'draft':
        return const Color(0xFFD97706); // Amber / orange
      case 'completed':
        return const Color(0xFF2563EB); // Royal blue
      case 'suspended':
      case 'halted':
      default:
        return const Color(0xFF6B7280);
    }
  }

  Color _getStatusBgColor(String status) {
    switch (status) {
      case 'in_progress':
      case 'active':
        return const Color(0xFFECFDF5);
      case 'planning':
      case 'published':
      case 'draft':
        return const Color(0xFFFFFBEB);
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

  String _getDurationText() {
    if (project.phases.isNotEmpty) {
      return '${project.phases.length * 6} شهر';
    }
    final months = 6 + ((project.id * 6) % 24);
    return '$months شهر';
  }

  Widget _buildProjectImage() {
    const double imgWidth = 110;
    const double imgHeight = 84;

    Widget imageWidget;
    if (project.imageUrl != null && project.imageUrl!.trim().isNotEmpty) {
      imageWidget = Image.network(
        project.imageUrl!,
        width: imgWidth,
        height: imgHeight,
        fit: BoxFit.cover,
        errorBuilder: (context, error, stackTrace) => _buildFallbackImage(imgWidth, imgHeight),
        loadingBuilder: (context, child, progress) {
          if (progress == null) return child;
          return Container(
            width: imgWidth,
            height: imgHeight,
            color: const Color(0xFFF1F5F9),
            child: const Center(
              child: SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.yemenRed),
              ),
            ),
          );
        },
      );
    } else {
      imageWidget = _buildFallbackImage(imgWidth, imgHeight);
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(14),
      child: imageWidget,
    );
  }

  Widget _buildFallbackImage(double width, double height) {
    return Image.asset(
      'assets/images/HomeCard1.jpg',
      width: width,
      height: height,
      fit: BoxFit.cover,
      errorBuilder: (context, error, stackTrace) {
        return Container(
          width: width,
          height: height,
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
          child: const Center(
            child: Icon(Icons.apartment_rounded, color: Colors.white70, size: 30),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final statusColor = _getStatusThemeColor(project.status);
    final statusBg = _getStatusBgColor(project.status);
    final progress = project.progressPercentage.clamp(0.0, 100.0);

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFF1F5F9), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(20),
        child: InkWell(
          borderRadius: BorderRadius.circular(20),
          onTap: () {
            context.push('/projects/${project.id}', extra: project);
          },
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Directionality(
              textDirection: TextDirection.rtl,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Upper Row: Text Column on the right + Image on the left
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Text info
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Status Badge
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
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
                            const SizedBox(height: 8),

                            // Project Title
                            Text(
                              project.title,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 14.5,
                                fontWeight: FontWeight.w800,
                                color: AppColors.yemenBlack,
                                height: 1.3,
                              ),
                            ),
                            const SizedBox(height: 5),

                            // Project Description
                            Text(
                              project.description,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 11.5,
                                color: Color(0xFF6B7280),
                                height: 1.35,
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(width: 12),

                      // Image
                      _buildProjectImage(),
                    ],
                  ),

                  const SizedBox(height: 12),

                  // Progress Bar Row (Percentage + Bar)
                  Row(
                    children: [
                      Text(
                        '${progress.toInt()}%',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w900,
                          color: statusColor,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(6),
                          child: Container(
                            height: 6.5,
                            color: const Color(0xFFF1F5F9),
                            alignment: Alignment.centerRight,
                            child: FractionallySizedBox(
                              widthFactor: (progress / 100.0).clamp(0.0, 1.0),
                              child: Container(
                                decoration: BoxDecoration(
                                  color: statusColor,
                                  borderRadius: BorderRadius.circular(6),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 12),

                  // Footer: Duration & Funding on right/center, Arrow button on left
                  Row(
                    children: [
                      // Duration
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.calendar_today_outlined, size: 13, color: Color(0xFF9CA3AF)),
                          const SizedBox(width: 4),
                          Text(
                            'المدة: ${_getDurationText()}',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF6B7280),
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(width: 14),

                      // Funding
                      Expanded(
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.account_balance_wallet_outlined, size: 14, color: Color(0xFF9CA3AF)),
                            const SizedBox(width: 4),
                            Flexible(
                              child: Text(
                                'التمويل: ${_formatCurrency(project.targetAmount)} ريال',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF6B7280),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),

                      // Circular Action Button with Arrow
                      Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(
                          color: statusColor,
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: statusColor.withValues(alpha: 0.35),
                              blurRadius: 6,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: const Center(
                          child: Icon(
                            Icons.arrow_forward_rounded,
                            color: Colors.white,
                            size: 18,
                          ),
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
