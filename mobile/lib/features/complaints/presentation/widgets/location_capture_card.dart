import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/constants/app_colors.dart';
import '../../application/complaints_providers.dart';

class LocationCaptureCard extends ConsumerWidget {
  const LocationCaptureCard({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final complaintState = ref.watch(createComplaintNotifierProvider);
    final notifier = ref.read(createComplaintNotifierProvider.notifier);

    final bool hasLocation =
        complaintState.latitude != null && complaintState.longitude != null;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: hasLocation
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
                    Icons.location_on_outlined,
                    color: AppColors.yemenBlack,
                    size: 18,
                  ),
                  SizedBox(width: 8),
                  Text(
                    'الموقع الجغرافي',
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
                  color: hasLocation
                      ? AppColors.yemenEmeraldLight
                      : AppColors.backgroundSubtle,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  hasLocation ? 'تم التحديد' : 'مطلوب',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: hasLocation
                        ? AppColors.yemenEmerald
                        : AppColors.textSecondary,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 14),

          if (hasLocation) ...[
            // Sleek Coordinates Bar
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: AppColors.backgroundSubtle,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.borderSubtle, width: 0.8),
              ),
              child: Row(
                children: [
                  Container(
                    width: 34,
                    height: 34,
                    decoration: BoxDecoration(
                      color: AppColors.yemenEmeraldLight,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.my_location_rounded,
                      color: AppColors.yemenEmerald,
                      size: 17,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${complaintState.latitude!.toStringAsFixed(5)}, ${complaintState.longitude!.toStringAsFixed(5)}',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: AppColors.yemenBlack,
                            fontFamily: 'monospace',
                          ),
                        ),
                        if (complaintState.locationAccuracy != null) ...[
                          const SizedBox(height: 2),
                          Text(
                            'دقة الموقع: ±${complaintState.locationAccuracy!.toStringAsFixed(1)} متر',
                            style: const TextStyle(
                              fontSize: 11,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  IconButton(
                    tooltip: 'تحديث الموقع',
                    onPressed: complaintState.isLocationLoading
                        ? null
                        : () => notifier.captureLocation(),
                    icon: const Icon(
                      Icons.refresh_rounded,
                      size: 18,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
          ] else ...[
            // Clean Location Trigger Button
            InkWell(
              onTap: complaintState.isLocationLoading
                  ? null
                  : () => notifier.captureLocation(),
              borderRadius: BorderRadius.circular(12),
              child: Container(
                padding: const EdgeInsets.symmetric(
                  vertical: 14,
                  horizontal: 16,
                ),
                decoration: BoxDecoration(
                  color: AppColors.yemenBlack,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    if (complaintState.isLocationLoading) ...[
                      const SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(width: 10),
                      const Text(
                        'جارٍ تحديد الموقع...',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ] else ...[
                      const Icon(
                        Icons.my_location_rounded,
                        color: Colors.white,
                        size: 17,
                      ),
                      const SizedBox(width: 8),
                      const Text(
                        'تحديد موقعي الحالي',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 13.5,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ],

          // Subtle Location Error Handling
          if (complaintState.locationError != null) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                const Icon(
                  Icons.location_off_outlined,
                  color: AppColors.yemenRed,
                  size: 15,
                ),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    complaintState.locationError!,
                    style: const TextStyle(
                      color: AppColors.yemenRed,
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                if (complaintState.isGpsDisabled)
                  TextButton(
                    onPressed: () => notifier.openLocationSettings(),
                    style: TextButton.styleFrom(
                      foregroundColor: AppColors.yemenRed,
                      padding: const EdgeInsets.symmetric(horizontal: 6),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    child: const Text(
                      'تفعيل GPS',
                      style: TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  )
                else if (complaintState.isLocationPermanentlyDenied)
                  TextButton(
                    onPressed: () => notifier.openAppSettings(),
                    style: TextButton.styleFrom(
                      foregroundColor: AppColors.yemenRed,
                      padding: const EdgeInsets.symmetric(horizontal: 6),
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
}
