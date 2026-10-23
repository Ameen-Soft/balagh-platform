import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/constants/app_colors.dart';
import '../../application/complaints_providers.dart';

class CameraCaptureCard extends ConsumerWidget {
  const CameraCaptureCard({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final complaintState = ref.watch(createComplaintNotifierProvider);
    final notifier = ref.read(createComplaintNotifierProvider.notifier);

    final bool hasImage = complaintState.imagePath != null;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: hasImage
              ? AppColors.yemenEmerald.withValues(alpha: 0.4)
              : AppColors.borderSubtle,
          width: 0.8,
        ),
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
          // Minimalist Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(
                    Icons.camera_alt_outlined,
                    color: AppColors.yemenBlack,
                    size: 18,
                  ),
                  SizedBox(width: 8),
                  Text(
                    'صورة المشكلة',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: AppColors.yemenBlack,
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: hasImage
                      ? AppColors.yemenEmeraldLight
                      : AppColors.backgroundSubtle,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  hasImage ? 'تم الإرفاق' : 'مطلوب',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: hasImage
                        ? AppColors.yemenEmerald
                        : AppColors.textSecondary,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 14),

          if (hasImage) ...[
            // Sleek Image Preview with Floating Controls
            Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.file(
                    File(complaintState.imagePath!),
                    height: 185,
                    width: double.infinity,
                    fit: BoxFit.cover,
                  ),
                ),
                Positioned(
                  bottom: 10,
                  left: 10,
                  right: 10,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      _buildOverlayButton(
                        icon: Icons.refresh_rounded,
                        label: 'إعادة التقاط',
                        onTap: complaintState.isCameraLoading
                            ? null
                            : () => notifier.capturePhoto(),
                      ),
                      _buildOverlayButton(
                        icon: Icons.delete_outline_rounded,
                        label: 'حذف',
                        isDanger: true,
                        onTap: () => notifier.removePhoto(),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ] else ...[
            // Clean Minimalist Capture Area
            InkWell(
              onTap: complaintState.isCameraLoading
                  ? null
                  : () => notifier.capturePhoto(),
              borderRadius: BorderRadius.circular(12),
              child: Container(
                padding: const EdgeInsets.symmetric(
                  vertical: 28,
                  horizontal: 16,
                ),
                decoration: BoxDecoration(
                  color: AppColors.backgroundSubtle,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.borderSubtle, width: 0.8),
                ),
                child: Column(
                  children: [
                    if (complaintState.isCameraLoading) ...[
                      const SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: AppColors.yemenRed,
                        ),
                      ),
                      const SizedBox(height: 10),
                      const Text(
                        'جارٍ فتح الكاميرا...',
                        style: TextStyle(
                          fontSize: 12.5,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ] else ...[
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: AppColors.borderSubtle,
                            width: 0.8,
                          ),
                        ),
                        child: const Icon(
                          Icons.add_a_photo_outlined,
                          color: AppColors.yemenRed,
                          size: 20,
                        ),
                      ),
                      const SizedBox(height: 10),
                      const Text(
                        'التقاط صورة مباشرة',
                        style: TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w700,
                          color: AppColors.yemenBlack,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ],

          // Subtle Camera Error Message
          if (complaintState.cameraError != null) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                const Icon(
                  Icons.info_outline_rounded,
                  color: AppColors.yemenRed,
                  size: 15,
                ),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    complaintState.cameraError!,
                    style: const TextStyle(
                      color: AppColors.yemenRed,
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                if (complaintState.isCameraPermanentlyDenied)
                  TextButton(
                    onPressed: () => notifier.openAppSettings(),
                    style: TextButton.styleFrom(
                      foregroundColor: AppColors.yemenRed,
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    child: const Text(
                      'الإعدادات',
                      style: TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildOverlayButton({
    required IconData icon,
    required String label,
    required VoidCallback? onTap,
    bool isDanger = false,
  }) {
    return Material(
      color: Colors.black.withValues(alpha: 0.65),
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                color: isDanger ? const Color(0xFFFCA5A5) : Colors.white,
                size: 15,
              ),
              const SizedBox(width: 5),
              Text(
                label,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 11.5,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
