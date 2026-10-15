import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/utils/app_date_formatter.dart';
import '../../domain/entities/complaint_entity.dart';
import 'status_badge.dart';

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
    final formattedDate = complaint.createdAt != null
        ? AppDateFormatter.formatDate(complaint.createdAt)
        : null;

    final isLiked = complaint.isLikedByMe;

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Ink(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(
              color: isLiked
                  ? AppColors.yemenRed.withValues(alpha: 0.3)
                  : AppColors.borderSubtle,
              width: isLiked ? 1.4 : 1.0,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 1. Top Bar: Ticket Reference, Category & Official Status
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Expanded(
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.backgroundSubtle,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: AppColors.borderSubtle),
                          ),
                          child: Text(
                            complaint.complaintNumber,
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                              color: AppColors.yemenBlack,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ),
                        if (complaint.category != null) ...[
                          const SizedBox(width: 8),
                          Flexible(
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: AppColors.deepBlack.withValues(alpha: 0.05),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                complaint.category!.name,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.deepBlack,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  StatusBadge(status: complaint.status),
                ],
              ),
              const SizedBox(height: 12),

              // 2. Complaint Title
              Text(
                complaint.title,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w800,
                  color: AppColors.deepBlack,
                  height: 1.35,
                ),
              ),
              const SizedBox(height: 6),

              // 3. Complaint Description snippet
              Text(
                complaint.description,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontSize: 12.5,
                  color: AppColors.textSecondary,
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 12),

              // 4. Department & Location Line
              Row(
                children: [
                  const Icon(
                    Icons.account_balance_outlined,
                    size: 14,
                    color: AppColors.textSecondary,
                  ),
                  const SizedBox(width: 5),
                  Expanded(
                    child: Text(
                      complaint.departmentName != null
                          ? '${complaint.ministryName ?? "الجهة المختصة"} • ${complaint.departmentName}'
                          : (complaint.ministryName ?? "الجهة الحكومية المختصة"),
                      style: const TextStyle(
                        fontSize: 11.5,
                        color: AppColors.textSecondary,
                        fontWeight: FontWeight.w500,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  if (formattedDate != null) ...[
                    const SizedBox(width: 8),
                    Row(
                      children: [
                        const Icon(
                          Icons.schedule_rounded,
                          size: 13,
                          color: AppColors.textMuted,
                        ),
                        const SizedBox(width: 3),
                        Text(
                          formattedDate,
                          style: const TextStyle(
                            fontSize: 11,
                            color: AppColors.textMuted,
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
              const Divider(height: 24),

              // 5. Interactive Bottom Bar: Likes, Map, and Details
              Row(
                children: [
                  // Interactive Like Button
                  InkWell(
                    onTap: onLikePressed,
                    borderRadius: BorderRadius.circular(12),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: isLiked
                            ? AppColors.yemenRedLight
                            : AppColors.backgroundSubtle,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isLiked
                              ? AppColors.yemenRed
                              : AppColors.borderSubtle,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            isLiked
                                ? Icons.favorite_rounded
                                : Icons.favorite_border_rounded,
                            size: 18,
                            color: isLiked
                                ? AppColors.yemenRed
                                : AppColors.textSecondary,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            complaint.likesCount.toString(),
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              color: isLiked
                                  ? AppColors.yemenRed
                                  : AppColors.deepBlack,
                            ),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            'تأييد',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: isLiked
                                  ? AppColors.yemenRed
                                  : AppColors.textSecondary,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),

                  // View on Map Button
                  if (complaint.latitude != 0.0 && complaint.longitude != 0.0)
                    InkWell(
                      onTap: onMapPressed,
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.borderSubtle),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.location_on_outlined,
                              size: 16,
                              color: AppColors.deepBlack,
                            ),
                            SizedBox(width: 4),
                            Text(
                              'الخريطة',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: AppColors.deepBlack,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                  const Spacer(),

                  // Full Details Link
                  TextButton.icon(
                    onPressed: onTap,
                    icon: const Icon(
                      Icons.arrow_forward_rounded,
                      size: 14,
                      color: AppColors.yemenRed,
                    ),
                    label: const Text(
                      'التفاصيل',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                        color: AppColors.yemenRed,
                      ),
                    ),
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
