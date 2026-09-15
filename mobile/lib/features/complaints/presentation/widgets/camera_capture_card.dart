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
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: hasImage ? AppColors.yemenEmerald : AppColors.borderSubtle,
          width: hasImage ? 1.5 : 1.0,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header Row
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: hasImage
                      ? AppColors.yemenEmeraldLight
                      : AppColors.yemenRedLight,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  hasImage
                      ? Icons.check_circle_rounded
                      : Icons.camera_alt_rounded,
                  color: hasImage ? AppColors.yemenEmerald : AppColors.yemenRed,
                  size: 22,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'التوثيق البصري بالكاميرا',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: AppColors.yemenBlack,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      hasImage
                          ? 'تم التقاط صورة الإثبات بنجاح'
                          : 'الصورة ملزمة ويتم التقاطها عبر الكاميرا الحية فقط',
                      style: TextStyle(
                        fontSize: 12,
                        color: hasImage
                            ? AppColors.yemenEmerald
                            : AppColors.textSecondary,
                        fontWeight: hasImage ? FontWeight.w600 : FontWeight.normal,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Main Action or Preview Area
          if (hasImage) ...[
            // Image Preview Container
            Stack(
              alignment: Alignment.topRight,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(14),
                  child: Image.file(
                    File(complaintState.imagePath!),
                    height: 190,
                    width: double.infinity,
                    fit: BoxFit.cover,
                  ),
                ),
                // Remove / Retake Actions Overlay
                Positioned(
                  top: 10,
                  left: 10,
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.65),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: IconButton(
                      tooltip: 'حذف الصورة',
                      icon: const Icon(Icons.delete_outline_rounded,
                          color: Colors.white, size: 20),
                      onPressed: () => notifier.removePhoto(),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: complaintState.isCameraLoading
                  ? null
                  : () => notifier.capturePhoto(),
              icon: const Icon(Icons.refresh_rounded, size: 18),
              label: const Text('إعادة التقاط الصورة'),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.yemenBlack,
                side: const BorderSide(color: AppColors.borderSubtle),
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ] else ...[
            // Camera Capture Button
            InkWell(
              onTap: complaintState.isCameraLoading
                  ? null
                  : () => notifier.capturePhoto(),
              borderRadius: BorderRadius.circular(14),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 16),
                decoration: BoxDecoration(
                  color: AppColors.backgroundSubtle,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: AppColors.yemenRed.withValues(alpha: 0.35),
                    style: BorderStyle.solid,
                    width: 1.5,
                  ),
                ),
                child: Column(
                  children: [
                    if (complaintState.isCameraLoading) ...[
                      const CircularProgressIndicator(
                        strokeWidth: 2.5,
                        color: AppColors.yemenRed,
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'جارٍ تشغيل الكاميرا...',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ] else ...[
                      CircleAvatar(
                        radius: 26,
                        backgroundColor: AppColors.yemenRedLight,
                        child: const Icon(
                          Icons.camera_enhance_rounded,
                          color: AppColors.yemenRed,
                          size: 28,
                        ),
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'اضغط هنا لفتح الكاميرا والتقاط المشهد',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.yemenBlack,
                        ),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'لا يُسمح بالاختيار من المعرض لضمان صحة البلاغ',
                        style: TextStyle(
                          fontSize: 11,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ],

          // Camera Error Handling
          if (complaintState.cameraError != null) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.yemenRedLight,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.yemenRedTint),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.error_outline_rounded,
                          color: AppColors.yemenRed, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          complaintState.cameraError!,
                          style: const TextStyle(
                            color: AppColors.yemenRed,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                  if (complaintState.isCameraPermanentlyDenied) ...[
                    const SizedBox(height: 8),
                    Align(
                      alignment: Alignment.centerLeft,
                      child: TextButton.icon(
                        onPressed: () => notifier.openAppSettings(),
                        icon: const Icon(Icons.settings_outlined, size: 16),
                        label: const Text('فتح إعدادات التطبيق'),
                        style: TextButton.styleFrom(
                          foregroundColor: AppColors.yemenRed,
                          padding: EdgeInsets.zero,
                          textStyle: const TextStyle(fontSize: 12),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}
