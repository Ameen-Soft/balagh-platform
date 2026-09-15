import 'package:flutter/foundation.dart';
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

    final targetHost = kIsWeb ? '127.0.0.1' : ApiEndpoints.serverIp;
    final targetPort = ApiEndpoints.serverPort;

    // Handle relative storage paths like '/storage/complaints/xyz.jpg' or 'storage/...'
    if (trimmed.startsWith('/storage/') || trimmed.startsWith('storage/')) {
      final sanitized = trimmed.startsWith('/') ? trimmed.substring(1) : trimmed;
      return 'http://$targetHost:$targetPort/$sanitized';
    }

    // Handle full URLs originating from Laravel's APP_URL=http://localhost:8000 or 127.0.0.1:8000
    if (!kIsWeb) {
      if (trimmed.contains('localhost:$targetPort')) {
        return trimmed.replaceFirst('localhost:$targetPort', '$targetHost:$targetPort');
      }
      if (trimmed.contains('127.0.0.1:$targetPort')) {
        return trimmed.replaceFirst('127.0.0.1:$targetPort', '$targetHost:$targetPort');
      }
    }

    return trimmed;
  }
}
