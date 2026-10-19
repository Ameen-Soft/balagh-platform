import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/network/media_url_resolver.dart';
import '../../domain/entities/complaint_entity.dart';

class CommunityComplaintCard extends StatelessWidget {
  final ComplaintEntity complaint;
  final VoidCallback? onTap;
  final VoidCallback? onLikePressed;
  final VoidCallback? onMapPressed;

  const CommunityComplaintCard({
    super.key,
    required this.complaint,
    this.onTap,
    this.onLikePressed,
    this.onMapPressed,
  });

  @override
  Widget build(BuildContext context) {
    final statusConfig = _getStatusConfig(complaint.status);
    final formattedDate = complaint.createdAt != null
        ? '${complaint.createdAt!.year}/${complaint.createdAt!.month.toString().padLeft(2, '0')}/${complaint.createdAt!.day.toString().padLeft(2, '0')}'
        : '';

    final locationText = _buildLocationText();
    final imageUrl = _extractImageUrl();
    final isLiked = complaint.isLikedByMe;

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Ink(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: isLiked
                  ? AppColors.yemenRed.withValues(alpha: 0.35)
                  : const Color(0xFFF1F3F5),
              width: 1.2,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 10,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          padding: const EdgeInsets.all(12),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // 1. Top Section: Thumbnail (Right) + Title & Description (Center) + Status & Chevron (Left)
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Right: Thumbnail with status colored corner ribbon
                  _buildThumbnail(imageUrl, statusConfig.color),

                  const SizedBox(width: 12),

                  // Center: Title + 2-line Description snippet
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Title
                        Text(
                          complaint.title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 14.5,
                            fontWeight: FontWeight.w800,
                            color: AppColors.yemenBlack,
                            height: 1.25,
                          ),
                        ),
                        const SizedBox(height: 5),

                        // Description Snippet
                        Text(
                          complaint.description,
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

                  const SizedBox(width: 8),

                  // Left: Status Badge & Chevron Arrow
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Status Badge Pill
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: statusConfig.backgroundColor,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              statusConfig.icon,
                              size: 12,
                              color: statusConfig.color,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              statusConfig.label,
                              style: TextStyle(
                                color: statusConfig.color,
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 14),

                      // Navigation Chevron
                      const Padding(
                        padding: EdgeInsets.only(right: 6),
                        child: Icon(
                          Icons.chevron_left_rounded,
                          size: 20,
                          color: Color(0xFF9CA3AF),
                        ),
                      ),
                    ],
                  ),
                ],
              ),

              const SizedBox(height: 10),
              const Divider(height: 1, color: Color(0xFFF3F4F6)),
              const SizedBox(height: 8),

              // 2. Bottom Row: Date (Right) + Location (Center) + Engagement Counts (Left)
              Row(
                children: [
                  // Date with Calendar Icon
                  if (formattedDate.isNotEmpty)
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          Icons.calendar_today_outlined,
                          size: 12,
                          color: Color(0xFF9CA3AF),
                        ),
                        const SizedBox(width: 4),
                        Text(
                          formattedDate,
                          style: const TextStyle(
                            fontSize: 11,
                            color: Color(0xFF9CA3AF),
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),

                  const SizedBox(width: 10),

                  // Location Pin Row
                  Expanded(
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          Icons.location_on_rounded,
                          size: 13,
                          color: Color(0xFF9CA3AF),
                        ),
                        const SizedBox(width: 3),
                        Flexible(
                          child: Text(
                            locationText,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 11,
                              color: Color(0xFF6B7280),
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(width: 8),

                  // Engagement Counts (Likes and Comments)
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Likes interactive button / count
                      InkWell(
                        onTap: onLikePressed,
                        borderRadius: BorderRadius.circular(10),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 4,
                            vertical: 2,
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                isLiked
                                    ? Icons.favorite_rounded
                                    : Icons.favorite_border_rounded,
                                size: 15,
                                color: isLiked
                                    ? AppColors.yemenRed
                                    : const Color(0xFFEF4444),
                              ),
                              const SizedBox(width: 3),
                              Text(
                                '${complaint.likesCount}',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w800,
                                  color: isLiked
                                      ? AppColors.yemenRed
                                      : const Color(0xFF374151),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),

                      const SizedBox(width: 8),

                      // Comments Count
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(
                            Icons.chat_bubble_outline_rounded,
                            size: 14,
                            color: Color(0xFF9CA3AF),
                          ),
                          const SizedBox(width: 3),
                          Text(
                            '${complaint.timeline.length}',
                            style: const TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF6B7280),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildThumbnail(String? imageUrl, Color statusColor) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(12),
      child: SizedBox(
        width: 74,
        height: 74,
        child: Stack(
          fit: StackFit.expand,
          children: [
            // Image or Fallback
            if (imageUrl != null && imageUrl.isNotEmpty)
              CachedNetworkImage(
                imageUrl: imageUrl,
                fit: BoxFit.cover,
                placeholder: (context, url) => Container(
                  color: const Color(0xFFF3F4F6),
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
                errorWidget: (context, url, error) => _buildFallbackThumbnail(),
              )
            else
              _buildFallbackThumbnail(),

            // Top-right status ribbon / corner triangle
            Positioned(
              top: 0,
              right: 0,
              child: CustomPaint(
                size: const Size(20, 20),
                painter: _CornerRibbonPainter(statusColor),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFallbackThumbnail() {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFFE5E7EB), Color(0xFFF3F4F6)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: const Center(
        child: Icon(
          Icons.photo_outlined,
          size: 26,
          color: Color(0xFF9CA3AF),
        ),
      ),
    );
  }

  String? _extractImageUrl() {
    if (complaint.attachments.isNotEmpty) {
      final first = complaint.attachments.first;
      final raw = first.fileUrl ?? first.filePath;
      if (raw.isNotEmpty) {
        return MediaUrlResolver.resolve(raw);
      }
    }
    return null;
  }

  String _buildLocationText() {
    if (complaint.ministryName != null && complaint.departmentName != null) {
      return '${complaint.ministryName} - ${complaint.departmentName}';
    }
    if (complaint.ministryName != null) {
      return '${complaint.ministryName} - ${complaint.category?.name ?? "القسم المعني"}';
    }
    if (complaint.category != null) {
      return complaint.category!.name;
    }
    return 'أمانة العاصمة - قسم البلاغات';
  }

  _StatusConfig _getStatusConfig(String status) {
    switch (status) {
      case 'in_progress':
      case 'assigned':
        return const _StatusConfig(
          label: 'جاري المعالجة',
          color: Color(0xFFDC2626),
          backgroundColor: Color(0xFFFEE2E2),
          icon: Icons.access_time_rounded,
        );
      case 'resolved':
      case 'closed':
        return const _StatusConfig(
          label: 'تم الحل',
          color: Color(0xFF16A34A),
          backgroundColor: Color(0xFFDCFCE7),
          icon: Icons.check_circle_outline_rounded,
        );
      case 'under_review':
        return const _StatusConfig(
          label: 'قيد المراجعة',
          color: Color(0xFFD97706),
          backgroundColor: Color(0xFFFEF3C7),
          icon: Icons.access_time_rounded,
        );
      case 'suggestion':
        return const _StatusConfig(
          label: 'مقترح',
          color: Color(0xFF7C3AED),
          backgroundColor: Color(0xFFEDE9FE),
          icon: Icons.lightbulb_outline_rounded,
        );
      case 'rejected':
        return const _StatusConfig(
          label: 'مرفوض',
          color: Color(0xFF6B7280),
          backgroundColor: Color(0xFFF3F4F6),
          icon: Icons.highlight_off_rounded,
        );
      case 'new':
      case 'submitted':
        return const _StatusConfig(
          label: 'جديد وارد',
          color: Color(0xFF2563EB),
          backgroundColor: Color(0xFFDBEAFE),
          icon: Icons.fiber_new_rounded,
        );
      case 'reopened':
        return const _StatusConfig(
          label: 'أُعيد فتحها',
          color: Color(0xFF7C3AED),
          backgroundColor: Color(0xFFEDE9FE),
          icon: Icons.replay_rounded,
        );
      default:
        return _StatusConfig(
          label: complaint.statusArabic,
          color: const Color(0xFF4B5563),
          backgroundColor: const Color(0xFFF3F4F6),
          icon: Icons.info_outline_rounded,
        );
    }
  }
}

class _StatusConfig {
  final String label;
  final Color color;
  final Color backgroundColor;
  final IconData icon;

  const _StatusConfig({
    required this.label,
    required this.color,
    required this.backgroundColor,
    required this.icon,
  });
}

class _CornerRibbonPainter extends CustomPainter {
  final Color color;

  const _CornerRibbonPainter(this.color);

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.fill;

    final path = Path()
      ..moveTo(0, 0)
      ..lineTo(size.width, 0)
      ..lineTo(size.width, size.height)
      ..close();

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant _CornerRibbonPainter oldDelegate) {
    return oldDelegate.color != color;
  }
}
