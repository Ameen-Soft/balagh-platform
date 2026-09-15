import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';

class CameraException implements Exception {
  final String message;
  const CameraException(this.message);

  @override
  String toString() => message;
}

class CameraService {
  final ImagePicker _picker;

  CameraService({ImagePicker? picker}) : _picker = picker ?? ImagePicker();

  /// Captures an image strictly from the device camera (Gallery is completely prohibited).
  Future<String?> capturePhoto() async {
    // 1. Verify and request camera permission
    final status = await Permission.camera.request();
    if (status.isPermanentlyDenied) {
      throw const CameraException(
        'تم رفض إذن الكاميرا بشكل دائم. يرجى تفعيل إذن الكاميرا من إعدادات التطبيق لتوثيق البلاغ.',
      );
    }

    if (status.isDenied) {
      throw const CameraException(
        'إذن استخدام الكاميرا مطلوب لالتقاط الصورة التوثيقية للبلاغ.',
      );
    }

    try {
      // 2. Open camera strictly using ImageSource.camera
      final XFile? photo = await _picker.pickImage(
        source: ImageSource.camera,
        imageQuality: 85,
        maxWidth: 1920,
        maxHeight: 1080,
        preferredCameraDevice: CameraDevice.rear,
      );

      return photo?.path;
    } catch (e) {
      throw CameraException('حدث خطأ أثناء فتح الكاميرا: ${e.toString()}');
    }
  }
}
