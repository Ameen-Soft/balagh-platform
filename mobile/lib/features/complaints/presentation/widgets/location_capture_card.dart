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

    final bool hasLocation = complaintState.latitude != null &&
        complaintState.longitude != null;

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: hasLocation ? AppColors.yemenEmerald : AppColors.borderSubtle,
          width: hasLocation ? 1.5 : 1.0,
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
                  color: hasLocation
                      ? AppColors.yemenEmeraldLight
                      : AppColors.yemenGoldLight,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  hasLocation
                      ? Icons.check_circle_rounded
                      : Icons.my_location_rounded,
                  color: hasLocation ? AppColors.yemenEmerald : AppColors.yemenGold,
                  size: 22,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'الموقع الجغرافي للحادثة (GPS)',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: AppColors.yemenBlack,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      hasLocation
                          ? 'تم تثبيت الإحداثيات الجغرافية بنجاح'
                          : 'يلزم التقاط إحداثيات موقعك الفعلي لتوثيق البلاغ',
                      style: TextStyle(
                        fontSize: 12,
                        color: hasLocation
                            ? AppColors.yemenEmerald
                            : AppColors.textSecondary,
                        fontWeight:
                            hasLocation ? FontWeight.w600 : FontWeight.normal,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Coordinates Box or Capture Button
          if (hasLocation) ...[
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.backgroundSubtle,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.borderSubtle),
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _buildCoordinateItem(
                        label: 'خط العرض (Latitude)',
                        value: complaintState.latitude!.toStringAsFixed(6),
                      ),
                      Container(
                        height: 32,
                        width: 1,
                        color: AppColors.borderSubtle,
                      ),
                      _buildCoordinateItem(
                        label: 'خط الطول (Longitude)',
                        value: complaintState.longitude!.toStringAsFixed(6),
                      ),
                    ],
                  ),
                  if (complaintState.locationAccuracy != null) ...[
                    const Divider(height: 20),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.gps_fixed_rounded,
                            size: 14, color: AppColors.yemenEmerald),
                        const SizedBox(width: 6),
                        Text(
                          'دقة الإشارة: ±${complaintState.locationAccuracy!.toStringAsFixed(1)} متر',
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: AppColors.yemenEmerald,
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: complaintState.isLocationLoading
                  ? null
                  : () => notifier.captureLocation(),
              icon: const Icon(Icons.refresh_rounded, size: 18),
              label: const Text('تحديث الموقع الجغرافي'),
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
            ElevatedButton.icon(
              onPressed: complaintState.isLocationLoading
                  ? null
                  : () => notifier.captureLocation(),
              icon: complaintState.isLocationLoading
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Icon(Icons.gps_fixed_rounded, size: 20),
              label: Text(
                complaintState.isLocationLoading
                    ? 'جارٍ الاتصال بالأقمار الاصطناعية (GPS)...'
                    : 'التقاط الإحداثيات الجغرافية الحالية',
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.yemenBlack,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
                elevation: 0,
              ),
            ),
          ],

          // Error and recovery options
          if (complaintState.locationError != null) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(14),
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
                      const Icon(Icons.location_off_rounded,
                          color: AppColors.yemenRed, size: 20),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          complaintState.locationError!,
                          style: const TextStyle(
                            color: AppColors.yemenRed,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      if (complaintState.isGpsDisabled)
                        TextButton.icon(
                          onPressed: () => notifier.openLocationSettings(),
                          icon: const Icon(Icons.settings_suggest_rounded, size: 16),
                          label: const Text('تشغيل GPS'),
                          style: TextButton.styleFrom(
                            foregroundColor: AppColors.yemenRed,
                            textStyle: const TextStyle(fontSize: 12),
                          ),
                        ),
                      if (complaintState.isLocationPermanentlyDenied)
                        TextButton.icon(
                          onPressed: () => notifier.openAppSettings(),
                          icon: const Icon(Icons.settings_outlined, size: 16),
                          label: const Text('إعدادات الإذن'),
                          style: TextButton.styleFrom(
                            foregroundColor: AppColors.yemenRed,
                            textStyle: const TextStyle(fontSize: 12),
                          ),
                        ),
                      TextButton.icon(
                        onPressed: () => notifier.captureLocation(),
                        icon: const Icon(Icons.refresh_rounded, size: 16),
                        label: const Text('إعادة المحاولة'),
                        style: TextButton.styleFrom(
                          foregroundColor: AppColors.yemenBlack,
                          textStyle: const TextStyle(fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildCoordinateItem({required String label, required String value}) {
    return Column(
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 11,
            color: AppColors.textSecondary,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: AppColors.yemenBlack,
            fontFamily: 'monospace',
          ),
        ),
      ],
    );
  }
}
