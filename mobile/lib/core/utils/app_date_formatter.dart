import 'package:intl/intl.dart';

/// Resilient Arabic and fallback date formatter for the application.
class AppDateFormatter {
  const AppDateFormatter._();

  /// Formats date to 'yyyy/MM/dd' (e.g. 2026/09/16).
  /// Safely catches any uninitialized locale errors and falls back to clean string formatting.
  static String formatDate(DateTime? date) {
    if (date == null) return '';
    try {
      return DateFormat('yyyy/MM/dd', 'ar').format(date);
    } catch (_) {
      try {
        return DateFormat('yyyy/MM/dd').format(date);
      } catch (_) {
        final m = date.month.toString().padLeft(2, '0');
        final d = date.day.toString().padLeft(2, '0');
        return '${date.year}/$m/$d';
      }
    }
  }

  /// Formats date and time to 'yyyy/MM/dd • hh:mm a' (e.g. 2026/09/16 • 01:25 ص).
  /// Safely catches any uninitialized locale errors and falls back gracefully.
  static String formatDateTime(DateTime? date) {
    if (date == null) return '';
    try {
      return DateFormat('yyyy/MM/dd • hh:mm a', 'ar').format(date);
    } catch (_) {
      try {
        return DateFormat('yyyy/MM/dd • hh:mm a').format(date);
      } catch (_) {
        final m = date.month.toString().padLeft(2, '0');
        final d = date.day.toString().padLeft(2, '0');
        final hour = date.hour > 12 ? date.hour - 12 : (date.hour == 0 ? 12 : date.hour);
        final min = date.minute.toString().padLeft(2, '0');
        final period = date.hour >= 12 ? 'م' : 'ص';
        return '${date.year}/$m/$d • ${hour.toString().padLeft(2, '0')}:$min $period';
      }
    }
  }
}
