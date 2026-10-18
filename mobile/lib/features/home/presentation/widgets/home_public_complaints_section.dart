import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../complaints/domain/entities/complaint_entity.dart';

class HomePublicComplaintsSection extends StatelessWidget {
  final List<ComplaintEntity> complaints;
  final bool isLoading;
  final String? errorMessage;
  final VoidCallback onViewAllTap;
  final void Function(ComplaintEntity complaint) onComplaintTap;
  final VoidCallback onRetry;

  const HomePublicComplaintsSection({
    super.key,
    required this.complaints,
    required this.isLoading,
    this.errorMessage,
    required this.onViewAllTap,
    required this.onComplaintTap,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Section Header: "أحدث البلاغات العامة" + "عرض الكل <"
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'أحدث البلاغات العامة',
                  style: TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.w800,
                    color: AppColors.yemenBlack,
                  ),
                ),
                const SizedBox(height: 4),
                Container(
                  width: 36,
                  height: 3.5,
                  decoration: BoxDecoration(
                    color: AppColors.yemenRed,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ],
            ),
            InkWell(
              onTap: onViewAllTap,
              borderRadius: BorderRadius.circular(8),
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: const [
                    Text(
                      'عرض الكل',
                      style: TextStyle(
                        color: AppColors.yemenRed,
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    SizedBox(width: 2),
                    Icon(
                      Icons.chevron_left_rounded,
                      size: 18,
                      color: AppColors.yemenRed,
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),

        const SizedBox(height: 14),

        // Section Content
        if (isLoading && complaints.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 24),
            child: Center(
              child: CircularProgressIndicator(color: AppColors.yemenRed),
            ),
          )
        else if (errorMessage != null && complaints.isEmpty)
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFF1F3F5)),
            ),
            child: Row(
              children: [
                const Icon(Icons.info_outline_rounded,
                    color: AppColors.textMuted, size: 22),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    errorMessage!,
                    style: const TextStyle(
                      fontSize: 12,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ),
                TextButton(
                  onPressed: onRetry,
                  child: const Text(
                    'إعادة المحاولة',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: AppColors.yemenRed,
                    ),
                  ),
                ),
              ],
            ),
          )
        else if (complaints.isEmpty)
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFF1F3F5)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.yemenRed.withValues(alpha: 0.08),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.campaign_outlined,
                    color: AppColors.yemenRed,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 14),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'لا توجد بلاغات عامة حتى الآن',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: AppColors.yemenBlack,
                        ),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'كن أول من يشارك في تحسين الحي عبر تقديم بلاغ عام.',
                        style: TextStyle(
                          fontSize: 11,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          )
        else
          Column(
            children: complaints.take(3).map((complaint) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 10.0),
                child: _buildComplaintCard(complaint),
              );
            }).toList(),
          ),
      ],
    );
  }

  Widget _buildComplaintCard(ComplaintEntity complaint) {
    final firstAttachment = complaint.attachments.isNotEmpty
        ? complaint.attachments.first
        : null;
    final imageUrl = firstAttachment?.fileUrl ?? firstAttachment?.filePath;

    // Location text fallback
    final locationText = complaint.departmentName ??
        complaint.ministryName ??
        'أمانة العاصمة';

    // Category name fallback
    final categoryText = complaint.category?.name ?? 'خدمات بلدية';

    // Priority label in Arabic
    final priorityText = _getPriorityArabic(complaint.priority);

    // Is New (e.g. created in last 7 days or status == 'new')
    final isNew = complaint.status == 'new' ||
        (complaint.createdAt != null &&
            DateTime.now().difference(complaint.createdAt!).inDays <= 5);

    return InkWell(
      onTap: () => onComplaintTap(complaint),
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: const Color(0xFFF1F3F5),
            width: 1.2,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.025),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            // Left Chevron indicator
            const Icon(
              Icons.chevron_left_rounded,
              color: Color(0xFF9CA3AF),
              size: 20,
            ),
            const SizedBox(width: 6),

            // Left Side: Real Attachment Image or Category Icon
            ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: SizedBox(
                width: 72,
                height: 72,
                child: (imageUrl != null && imageUrl.isNotEmpty)
                    ? CachedNetworkImage(
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
                        errorWidget: (context, url, error) =>
                            _buildFallbackThumbnail(),
                      )
                    : _buildFallbackThumbnail(),
              ),
            ),
            const SizedBox(width: 12),

            // Middle Column: Title + [جديد], Location, Category
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Title and [جديد] badge
                  Row(
                    children: [
                      if (isNew) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: AppColors.yemenRed,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'جديد',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 9.5,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        const SizedBox(width: 6),
                      ],
                      Expanded(
                        child: Text(
                          complaint.title,
                          style: const TextStyle(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w800,
                            color: AppColors.yemenBlack,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 5),

                  // Location
                  Row(
                    children: [
                      const Icon(
                        Icons.location_on_outlined,
                        size: 14,
                        color: Color(0xFF6B7280),
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          locationText,
                          style: const TextStyle(
                            fontSize: 11,
                            color: Color(0xFF6B7280),
                            fontWeight: FontWeight.w500,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 3),

                  // Category
                  Row(
                    children: [
                      const Icon(
                        Icons.construction_rounded,
                        size: 14,
                        color: Color(0xFF6B7280),
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          categoryText,
                          style: const TextStyle(
                            fontSize: 11,
                            color: Color(0xFF6B7280),
                            fontWeight: FontWeight.w500,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),

            // Far Right: Priority Pill and Likes count
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Priority Pill
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: AppColors.yemenRedTint,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    priorityText,
                    style: const TextStyle(
                      color: AppColors.yemenRed,
                      fontSize: 10.5,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                const SizedBox(height: 8),

                // Likes count (e.g. "24 👍")
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      complaint.likesCount.toString(),
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF374151),
                      ),
                    ),
                    const SizedBox(width: 4),
                    const Icon(
                      Icons.thumb_up_alt_outlined,
                      size: 14,
                      color: AppColors.yemenRed,
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFallbackThumbnail() {
    return Container(
      color: const Color(0xFFF3F4F6),
      child: const Center(
        child: Icon(
          Icons.broken_image_outlined,
          size: 26,
          color: Color(0xFF9CA3AF),
        ),
      ),
    );
  }

  String _getPriorityArabic(String? priority) {
    switch (priority?.toLowerCase()) {
      case 'urgent':
        return 'طارئ';
      case 'high':
        return 'عالي';
      case 'medium':
        return 'متوسط';
      case 'low':
        return 'منخفض';
      default:
        return 'عادي';
    }
  }
}
