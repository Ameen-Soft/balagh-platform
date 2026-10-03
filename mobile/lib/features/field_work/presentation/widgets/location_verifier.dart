import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/services/location_service.dart';
import '../../../complaints/application/complaints_providers.dart';
import '../../application/providers/field_work_providers.dart';
import '../../domain/entities/location_verification_result.dart';

enum LocationVerificationStep {
  idle,
  gettingGps,
  verifyingWithServer,
  verified,
  outsideGeofence,
  gpsDisabled,
  permissionDenied,
  error,
}

class LocationVerifier extends ConsumerStatefulWidget {
  final int assignmentId;
  final ValueChanged<LocationResult>? onLocationVerified;

  const LocationVerifier({
    super.key,
    required this.assignmentId,
    this.onLocationVerified,
  });

  @override
  ConsumerState<LocationVerifier> createState() => _LocationVerifierState();
}

class _LocationVerifierState extends ConsumerState<LocationVerifier> {
  LocationVerificationStep _step = LocationVerificationStep.idle;
  String? _errorMessage;
  LocationVerificationResult? _lastResult;

  @override
  void initState() {
    super.initState();
    final state = ref.read(fieldWorkNotifierProvider);
    if (state.verificationResult != null &&
        state.verificationResult!.assignmentId == widget.assignmentId) {
      _lastResult = state.verificationResult;
      _step = state.verificationResult!.isWithinRange
          ? LocationVerificationStep.verified
          : LocationVerificationStep.outsideGeofence;
    }
  }

  Future<void> _verifyLocation() async {
    setState(() {
      _step = LocationVerificationStep.gettingGps;
      _errorMessage = null;
    });

    final locationService = ref.read(locationServiceProvider);

    try {
      // 1. Get GPS coordinates
      final location = await locationService.getCurrentLocation();

      if (!mounted) return;
      setState(() {
        _step = LocationVerificationStep.verifyingWithServer;
      });

      // 2. Verify with backend
      final notifier = ref.read(fieldWorkNotifierProvider.notifier);
      final isWithinRange = await notifier.verifyLocation(
        assignmentId: widget.assignmentId,
        latitude: location.latitude,
        longitude: location.longitude,
      );

      if (!mounted) return;
      final state = ref.read(fieldWorkNotifierProvider);
      _lastResult = state.verificationResult;

      setState(() {
        if (isWithinRange) {
          _step = LocationVerificationStep.verified;
          widget.onLocationVerified?.call(location);
        } else {
          _step = LocationVerificationStep.outsideGeofence;
          _errorMessage = state.errorMessage ??
              _lastResult?.message ??
              'أنت بعيد عن موقع البلاغ، يجب الاقتراب من الموقع لبدء تنفيذ المهمة.';
        }
      });
    } on LocationException catch (e) {
      if (!mounted) return;
      setState(() {
        if (e.isGpsDisabled) {
          _step = LocationVerificationStep.gpsDisabled;
          _errorMessage = e.message;
        } else if (e.isPermanentlyDenied) {
          _step = LocationVerificationStep.permissionDenied;
          _errorMessage = e.message;
        } else {
          _step = LocationVerificationStep.permissionDenied;
          _errorMessage = e.message;
        }
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _step = LocationVerificationStep.error;
        _errorMessage = 'تعذر التحقق من الموقع: ${e.toString()}';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _cardBackgroundColor,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _cardBorderColor, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: _iconBackgroundColor,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(_statusIcon, color: _iconColor, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _titleText,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: _titleColor,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      _subtitleText,
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          // Additional info for outsideGeofence or error
          if (_errorMessage != null &&
              _step != LocationVerificationStep.verified) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: Colors.red.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: Colors.red.withValues(alpha: 0.2),
                ),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.info_outline, size: 16, color: Colors.red),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _errorMessage!,
                      style: const TextStyle(
                        fontSize: 12,
                        color: Colors.red,
                        height: 1.4,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],

          // Distance info badge when available
          if (_lastResult != null) ...[
            const SizedBox(height: 10),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'المسافة المقدرة: ${_lastResult!.distanceMeters.toStringAsFixed(1)} متر',
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textPrimary,
                  ),
                ),
                Text(
                  'الحد المسموح: ${_lastResult!.allowedRadiusMeters.toStringAsFixed(0)} متر',
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.textMuted,
                  ),
                ),
              ],
            ),
          ],

          const SizedBox(height: 14),

          // Action Buttons depending on state
          _buildActionButton(),
        ],
      ),
    );
  }

  Widget _buildActionButton() {
    final locationService = ref.read(locationServiceProvider);

    switch (_step) {
      case LocationVerificationStep.idle:
        return ElevatedButton.icon(
          onPressed: _verifyLocation,
          icon: const Icon(Icons.my_location_rounded, size: 18),
          label: const Text('التحقق من موقعك الجغرافي'),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.deepBlack,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        );

      case LocationVerificationStep.gettingGps:
      case LocationVerificationStep.verifyingWithServer:
        return Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: Colors.black.withValues(alpha: 0.05),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              SizedBox(
                width: 16,
                height: 16,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(AppColors.deepBlack),
                ),
              ),
              const SizedBox(width: 10),
              Text(
                _step == LocationVerificationStep.gettingGps
                    ? 'جارٍ التقاط إحداثيات GPS...'
                    : 'جارٍ التحقق مع السيرفر...',
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textPrimary,
                ),
              ),
            ],
          ),
        );

      case LocationVerificationStep.verified:
        return OutlinedButton.icon(
          onPressed: _verifyLocation,
          icon: const Icon(Icons.refresh_rounded, size: 16),
          label: const Text('إعادة التحقق من الموقع'),
          style: OutlinedButton.styleFrom(
            foregroundColor: AppColors.yemenEmerald,
            side: const BorderSide(color: AppColors.yemenEmerald),
            padding: const EdgeInsets.symmetric(vertical: 10),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        );

      case LocationVerificationStep.outsideGeofence:
      case LocationVerificationStep.error:
        return ElevatedButton.icon(
          onPressed: _verifyLocation,
          icon: const Icon(Icons.refresh_rounded, size: 18),
          label: const Text('إعادة المحاولة بعد الاقتراب'),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.yemenRed,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        );

      case LocationVerificationStep.gpsDisabled:
        return ElevatedButton.icon(
          onPressed: () async {
            await locationService.openLocationSettings();
            _verifyLocation();
          },
          icon: const Icon(Icons.location_off_rounded, size: 18),
          label: const Text('فتح إعدادات الـ GPS لتفعيله'),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.yemenGold,
            foregroundColor: Colors.black,
            padding: const EdgeInsets.symmetric(vertical: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        );

      case LocationVerificationStep.permissionDenied:
        return ElevatedButton.icon(
          onPressed: () async {
            await locationService.openAppSettings();
          },
          icon: const Icon(Icons.settings_rounded, size: 18),
          label: const Text('فتح إعدادات التطبيق لمنح الإذن'),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.deepBlack,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        );
    }
  }

  Color get _cardBackgroundColor {
    switch (_step) {
      case LocationVerificationStep.verified:
        return AppColors.yemenEmerald.withValues(alpha: 0.05);
      case LocationVerificationStep.outsideGeofence:
      case LocationVerificationStep.error:
        return Colors.red.withValues(alpha: 0.04);
      case LocationVerificationStep.gpsDisabled:
        return AppColors.yemenGold.withValues(alpha: 0.06);
      default:
        return Colors.white;
    }
  }

  Color get _cardBorderColor {
    switch (_step) {
      case LocationVerificationStep.verified:
        return AppColors.yemenEmerald.withValues(alpha: 0.4);
      case LocationVerificationStep.outsideGeofence:
      case LocationVerificationStep.error:
        return Colors.red.withValues(alpha: 0.3);
      case LocationVerificationStep.gpsDisabled:
        return AppColors.yemenGold.withValues(alpha: 0.5);
      default:
        return AppColors.borderSubtle;
    }
  }

  Color get _iconBackgroundColor {
    switch (_step) {
      case LocationVerificationStep.verified:
        return AppColors.yemenEmerald.withValues(alpha: 0.15);
      case LocationVerificationStep.outsideGeofence:
      case LocationVerificationStep.error:
        return Colors.red.withValues(alpha: 0.1);
      case LocationVerificationStep.gpsDisabled:
        return AppColors.yemenGold.withValues(alpha: 0.2);
      default:
        return Colors.black.withValues(alpha: 0.05);
    }
  }

  Color get _iconColor {
    switch (_step) {
      case LocationVerificationStep.verified:
        return AppColors.yemenEmerald;
      case LocationVerificationStep.outsideGeofence:
      case LocationVerificationStep.error:
        return Colors.red;
      case LocationVerificationStep.gpsDisabled:
        return Colors.amber.shade900;
      default:
        return AppColors.deepBlack;
    }
  }

  IconData get _statusIcon {
    switch (_step) {
      case LocationVerificationStep.verified:
        return Icons.verified_rounded;
      case LocationVerificationStep.outsideGeofence:
        return Icons.wrong_location_rounded;
      case LocationVerificationStep.gpsDisabled:
        return Icons.location_disabled_rounded;
      case LocationVerificationStep.permissionDenied:
        return Icons.security_rounded;
      case LocationVerificationStep.gettingGps:
      case LocationVerificationStep.verifyingWithServer:
        return Icons.radar_rounded;
      default:
        return Icons.location_on_rounded;
    }
  }

  String get _titleText {
    switch (_step) {
      case LocationVerificationStep.idle:
        return 'التحقق الجغرافي للمهمة';
      case LocationVerificationStep.gettingGps:
        return 'جارٍ تحديد موقعك عبر GPS...';
      case LocationVerificationStep.verifyingWithServer:
        return 'جارٍ التحقق من النطاق الجغرافي...';
      case LocationVerificationStep.verified:
        return 'أنت ضمن نطاق موقع البلاغ';
      case LocationVerificationStep.outsideGeofence:
        return 'أنت خارج نطاق موقع البلاغ';
      case LocationVerificationStep.gpsDisabled:
        return 'خدمة الـ GPS غير مفعلة';
      case LocationVerificationStep.permissionDenied:
        return 'صلاحية الموقع مطلوبة';
      case LocationVerificationStep.error:
        return 'فشل التحقق من الموقع';
    }
  }

  String get _subtitleText {
    switch (_step) {
      case LocationVerificationStep.idle:
        return 'يجب التواجد في محيط موقع البلاغ (نطاق 500م) لبدء التنفيذ';
      case LocationVerificationStep.gettingGps:
        return 'يرجى الانتظار حتى يتم استقبال إشارات الأقمار الصناعية';
      case LocationVerificationStep.verifyingWithServer:
        return 'يتم إرسال الإحداثيات للسيرفر لمطابقة النطاق';
      case LocationVerificationStep.verified:
        return 'تم التحقق بنجاح من تواجدك الميداني، يمكنك الآن بدء التنفيذ';
      case LocationVerificationStep.outsideGeofence:
        return 'يجب الاقتراب من الموقع الجغرافي المحدد للبلاغ للمتابعة';
      case LocationVerificationStep.gpsDisabled:
        return 'يرجى تفعيل خدمة الموقع في جهازك للمتابعة';
      case LocationVerificationStep.permissionDenied:
        return 'يرجى منح تطبيق بادر صلاحية الوصول للموقع الجغرافي';
      case LocationVerificationStep.error:
        return 'حدث خطأ أثناء الاتصال أو جلب الإحداثيات';
    }
  }

  Color get _titleColor {
    switch (_step) {
      case LocationVerificationStep.verified:
        return AppColors.yemenEmerald;
      case LocationVerificationStep.outsideGeofence:
      case LocationVerificationStep.error:
        return Colors.red;
      case LocationVerificationStep.gpsDisabled:
        return Colors.amber.shade900;
      default:
        return AppColors.textPrimary;
    }
  }
}
