import 'package:geolocator/geolocator.dart';

class LocationException implements Exception {
  final String message;
  final bool isPermanentlyDenied;
  final bool isGpsDisabled;

  const LocationException(
    this.message, {
    this.isPermanentlyDenied = false,
    this.isGpsDisabled = false,
  });

  @override
  String toString() => message;
}

class LocationResult {
  final double latitude;
  final double longitude;
  final double accuracy;

  const LocationResult({
    required this.latitude,
    required this.longitude,
    required this.accuracy,
  });
}

class LocationService {
  /// Checks permissions, verifies GPS service, and returns current coordinates.
  Future<LocationResult> getCurrentLocation() async {
    // 1. Check if location services are enabled on the device
    final bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw const LocationException(
        'خدمات الموقع الجغرافي (GPS) غير مفعلة على جهازك. يرجى تشغيل الـ GPS ثم إعادة المحاولة.',
        isGpsDisabled: true,
      );
    }

    // 2. Check and request location permission
    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        throw const LocationException(
          'تم رفض إذن الوصول إلى الموقع الجغرافي. يتطلب البلاغ تحديد موقع الحدث لضمان الاستجابة.',
        );
      }
    }

    if (permission == LocationPermission.deniedForever) {
      throw const LocationException(
        'تم رفض إذن الموقع الجغرافي بشكل دائم. يرجى تفعيل الإذن من إعدادات الهاتف لمتابعة تقديم البلاغ.',
        isPermanentlyDenied: true,
      );
    }

    // 3. Obtain current position
    try {
      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: Duration(seconds: 15),
        ),
      );

      return LocationResult(
        latitude: position.latitude,
        longitude: position.longitude,
        accuracy: position.accuracy,
      );
    } catch (e) {
      // Fallback to last known position if current position timed out
      final lastKnown = await Geolocator.getLastKnownPosition();
      if (lastKnown != null) {
        return LocationResult(
          latitude: lastKnown.latitude,
          longitude: lastKnown.longitude,
          accuracy: lastKnown.accuracy,
        );
      }

      throw LocationException('تعذر تحديد إحداثيات الموقع الحالي: ${e.toString()}');
    }
  }

  /// Opens the system location settings.
  Future<bool> openLocationSettings() async {
    return await Geolocator.openLocationSettings();
  }

  /// Opens the app settings.
  Future<bool> openAppSettings() async {
    return await Geolocator.openAppSettings();
  }
}
