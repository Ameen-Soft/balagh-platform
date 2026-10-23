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

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle, width: 0.8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.015),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Top Image Preview (if available)
          if (state.imagePath != null)
            ClipRRect(
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(15),
              ),
              child: Image.file(
                File(state.imagePath!),
                height: 165,
                width: double.infinity,
                fit: BoxFit.cover,
              ),
            ),

          Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Priority Badge & Coordinates Row
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: _getPriorityColor(state.priority)
                            .withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        'الأهمية: ${_getPriorityArabic(state.priority)}',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: _getPriorityColor(state.priority),
                        ),
                      ),
                    ),
                    if (state.latitude != null && state.longitude != null)
                      Row(
                        children: [
                          const Icon(
                            Icons.location_on_rounded,
                            size: 14,
                            color: AppColors.yemenEmerald,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            '${state.latitude!.toStringAsFixed(4)}, ${state.longitude!.toStringAsFixed(4)}',
                            style: const TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w600,
                              color: AppColors.textSecondary,
                              fontFamily: 'monospace',
                            ),
                          ),
                        ],
                      ),
                  ],
                ),

                const SizedBox(height: 14),

                // Title
                Text(
                  state.title,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: AppColors.yemenBlack,
                  ),
                ),
                const SizedBox(height: 6),

                // Description
                Text(
                  state.description,
                  style: const TextStyle(
                    fontSize: 13,
                    color: AppColors.textSecondary,
                    height: 1.55,
                  ),
                ),

                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 14),
                  child: Divider(height: 1, color: AppColors.borderSubtle),
                ),

                // Clean Metadata Rows
                _buildInfoRow(
                  label: 'الجهة المختصة',
                  value: state.selectedMinistry?.name ?? '-',
                ),
                const SizedBox(height: 10),
                _buildInfoRow(
                  label: 'التصنيف',
                  value: state.selectedCategory?.name ?? '-',
                ),
                const SizedBox(height: 10),
                _buildInfoRow(
                  label: 'النشر على الخريطة العامة',
                  value: state.isPublic ? 'مفعل' : 'خاص',
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow({required String label, required String value}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 12.5,
            color: AppColors.textSecondary,
          ),
        ),
        const SizedBox(width: 12),
        Flexible(
          child: Text(
            value,
            textAlign: TextAlign.left,
            style: const TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w700,
              color: AppColors.yemenBlack,
            ),
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
        return 'عاجل';
      case 'urgent':
        return 'طارئ';
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
