import 'api_endpoints.dart';

/// Centralized utility to resolve backend media and attachment URLs
/// into reachable URLs across Web, Emulator, and Physical Devices via Hotspot.
class MediaUrlResolver {
  const MediaUrlResolver._();

  /// Resolves any relative or localhost-bound media URL into an absolute, reachable URL.
  static String? resolve(String? rawUrl) {
    if (rawUrl == null) return null;
    final trimmed = rawUrl.trim();
    if (trimmed.isEmpty) return null;

    final serverBase = ApiEndpoints.serverBaseUrl;

    // Handle relative storage paths like '/storage/complaints/xyz.jpg' or 'storage/...'
    if (trimmed.startsWith('/storage/') || trimmed.startsWith('storage/')) {
      final sanitized = trimmed.startsWith('/') ? trimmed.substring(1) : trimmed;
      return '$serverBase/$sanitized';
    }

    // Handle relative paths without storage prefix like 'complaints/xyz.jpg'
    if (!trimmed.startsWith('http://') && !trimmed.startsWith('https://')) {
      final sanitized = trimmed.startsWith('/') ? trimmed.substring(1) : trimmed;
      return '$serverBase/storage/$sanitized';
    }

    // Handle full URLs originating from local development URLs (localhost, 127.0.0.1, or local IPs)
    if (trimmed.contains('localhost') || trimmed.contains('127.0.0.1') || trimmed.contains('192.168.')) {
      final uri = Uri.tryParse(trimmed);
      if (uri != null) {
        return '$serverBase${uri.path}';
      }
    }

    return trimmed;
  }
}
