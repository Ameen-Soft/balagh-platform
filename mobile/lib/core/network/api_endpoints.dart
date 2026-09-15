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

  // --- Auth Endpoints ---
  static const String login = '/auth/login';
  static const String register = '/auth/register';
  static const String logout = '/auth/logout';
  static const String me = '/auth/me';

  // --- Reference Data Endpoints ---
  static const String ministries = '/ministries';
  static String ministryDetails(int id) => '/ministries/$id';

  static const String categories = '/categories';
  static String categoryDetails(int id) => '/categories/$id';

  // --- Complaints Endpoints ---
  static const String complaints = '/complaints';
  static String complaintDetails(int id) => '/complaints/$id';
  static String updateComplaintStatus(int id) => '/complaints/$id/status';
  static String transferComplaint(int id) => '/complaints/$id/transfer';
  static String assignComplaint(int id) => '/complaints/$id/assign';

  // --- Field Assignments Endpoints ---
  static const String fieldAssignments = '/field-assignments';
  static String acceptFieldAssignment(int id) => '/field-assignments/$id/accept';
  static String startFieldAssignment(int id) => '/field-assignments/$id/start';
  static String verifyFieldWorkerLocation(int id) => '/field-assignments/$id/verify-location';
  static String completeFieldAssignment(int id) => '/field-assignments/$id/complete';

  // --- Developmental Projects Endpoints ---
  static const String projects = '/projects';
  static String projectDetails(int id) => '/projects/$id';
  static String contributeProject(int id) => '/projects/$id/contribute';

  // --- Notifications Endpoints ---
  static const String notifications = '/notifications';
  static String markNotificationAsRead(int id) => '/notifications/$id/read';
  static const String markAllNotificationsAsRead = '/notifications/read-all';
}
