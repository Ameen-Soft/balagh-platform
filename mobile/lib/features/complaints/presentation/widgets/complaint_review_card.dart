import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/constants/app_colors.dart';
import '../../application/complaints_providers.dart';

class ComplaintReviewCard extends ConsumerWidget {
  const ComplaintReviewCard({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(createComplaintNotifierProvider);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Summary Card
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
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.yemenRedLight,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.assignment_turned_in_rounded,
                      color: AppColors.yemenRed,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 10),
                  const Text(
                    'ملخص بيانات البلاغ',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                      color: AppColors.yemenBlack,
                    ),
                  ),
                ],
              ),
              const Divider(height: 24),

              // Ministry & Category
              _buildReviewRow(
                icon: Icons.account_balance_outlined,
                label: 'الجهة / الوزارة',
                value: state.selectedMinistry?.name ?? 'غير محدد',
              ),
              const SizedBox(height: 12),
              _buildReviewRow(
                icon: Icons.category_outlined,
                label: 'التصنيف المعتمد',
                value: state.selectedCategory?.name ?? 'غير محدد',
              ),
              const SizedBox(height: 12),
              _buildReviewRow(
                icon: Icons.flag_outlined,
                label: 'درجة الأولوية',
                value: _getPriorityArabic(state.priority),
                valueColor: _getPriorityColor(state.priority),
              ),
              const Divider(height: 24),

              // Title & Description
              const Text(
                'عنوان البلاغ',
                style: TextStyle(
                  fontSize: 12,
                  color: AppColors.textSecondary,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                state.title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: AppColors.yemenBlack,
                ),
              ),
              const SizedBox(height: 12),
              const Text(
                'تفاصيل المشكلة',
                style: TextStyle(
                  fontSize: 12,
                  color: AppColors.textSecondary,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                state.description,
                style: const TextStyle(
                  fontSize: 13,
                  color: AppColors.textPrimary,
                  height: 1.5,
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 16),

        // Photo and Location Summary Card
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
                'التوثيق البصري والمكاني',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: AppColors.yemenBlack,
                ),
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  // Photo Thumbnail
                  if (state.imagePath != null)
                    ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: Image.file(
                        File(state.imagePath!),
                        width: 70,
                        height: 70,
                        fit: BoxFit.cover,
                      ),
                    )
                  else
                    Container(
                      width: 70,
                      height: 70,
                      decoration: BoxDecoration(
                        color: AppColors.backgroundSubtle,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.broken_image_rounded,
                          color: AppColors.textMuted),
                    ),
                  const SizedBox(width: 14),
                  // Coordinates details
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.location_on_rounded,
                                size: 16, color: AppColors.yemenEmerald),
                            const SizedBox(width: 4),
                            Text(
                              '${state.latitude?.toStringAsFixed(5) ?? "-"}, ${state.longitude?.toStringAsFixed(5) ?? "-"}',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w700,
                                fontFamily: 'monospace',
                                color: AppColors.yemenBlack,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'تم تثبيت وتشفير إحداثيات الموقع عبر الأقمار الاصطناعية',
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
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildReviewRow({
    required IconData icon,
    required String label,
    required String value,
    Color? valueColor,
  }) {
    return Row(
      children: [
        Icon(icon, size: 18, color: AppColors.textSecondary),
        const SizedBox(width: 8),
        Text(
          label,
          style: const TextStyle(
            fontSize: 13,
            color: AppColors.textSecondary,
          ),
        ),
        const Spacer(),
        Text(
          value,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w700,
            color: valueColor ?? AppColors.textPrimary,
          ),
        ),
      ],
    );
  }

  String _getPriorityArabic(String priority) {
    switch (priority) {
      case 'low':
        return 'منخفض';
      case 'high':
        return 'مرتفع';
      case 'urgent':
        return 'عاجل جداً';
      case 'medium':
      default:
        return 'عادي';
    }
  }

  Color _getPriorityColor(String priority) {
    switch (priority) {
      case 'urgent':
      case 'high':
        return AppColors.yemenRed;
      case 'low':
        return AppColors.textSecondary;
      case 'medium':
      default:
        return AppColors.yemenGold;
    }
  }
}
