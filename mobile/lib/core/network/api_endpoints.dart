import 'package:flutter/foundation.dart';

class ApiEndpoints {
  const ApiEndpoints._();

  /// عنوان IP الخاص باللابتوب المتصل بنقطة اتصال الهاتف (Hotspot)
  /// تم استخراجه من محول الـ Wi-Fi في اللابتوب: 192.168.43.172
  static const String serverIp = '192.168.43.172';
  static const String serverPort = '8000';

  static String get baseUrl {
    const customUrl = String.fromEnvironment('API_BASE_URL');
    if (customUrl.isNotEmpty) return customUrl;

    if (kIsWeb) return 'http://127.0.0.1:$serverPort/api/v1';

    // الاتصال بسيرفر Laravel على اللابتوب من الهاتف الحقيقي
    return 'http://$serverIp:$serverPort/api/v1';
  }

  static const String login = '/auth/login';
  static const String register = '/auth/register';
  static const String logout = '/auth/logout';
  static const String me = '/auth/me';
}
