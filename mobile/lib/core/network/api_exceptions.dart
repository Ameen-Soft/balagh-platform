import 'package:dio/dio.dart';

abstract class ApiException implements Exception {
  final String message;
  final int? statusCode;

  const ApiException(this.message, [this.statusCode]);

  @override
  String toString() => message;
}

class ValidationException extends ApiException {
  final Map<String, List<String>> errors;

  const ValidationException(
    String message, {
    this.errors = const {},
    int? statusCode = 422,
  }) : super(message, statusCode);

  String get firstErrorMessage {
    if (errors.isNotEmpty) {
      final firstList = errors.values.first;
      if (firstList.isNotEmpty) {
        return firstList.first;
      }
    }
    return message;
  }
}

class UnauthorizedException extends ApiException {
  const UnauthorizedException([
    super.message = 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مجدداً.',
    super.statusCode = 401,
  ]);
}

class ForbiddenException extends ApiException {
  const ForbiddenException([
    super.message = 'ليس لديك الصلاحية للقيام بهذا الإجراء.',
    super.statusCode = 403,
  ]);
}

class NotFoundException extends ApiException {
  const NotFoundException([
    super.message = 'البيانات المطلوبة غير موجودة.',
    super.statusCode = 404,
  ]);
}

class ConflictException extends ApiException {
  const ConflictException([
    super.message = 'حدث تعارض في البيانات، يرجى تحديث الصفحة والمحاولة مجدداً.',
    super.statusCode = 409,
  ]);
}

class ServerException extends ApiException {
  const ServerException([
    super.message = 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً.',
    super.statusCode = 500,
  ]);
}

class NetworkException extends ApiException {
  const NetworkException([
    super.message = 'تعذر الاتصال بالخادم، يرجى التحقق من اتصال الإنترنت.',
  ]);
}

class ApiExceptionHandler {
  static ApiException handleDioError(DioException error) {
    switch (error.type) {
      case DioExceptionType.connectionTimeout:
        return const NetworkException('انتهت مهلة الاتصال بالخادم، يرجى المحاولة لاحقاً.');
      case DioExceptionType.sendTimeout:
        return const NetworkException('انتهت مهلة إرسال البيانات، يرجى التحقق من سرعة الاتصال والمحاولة مجدداً.');
      case DioExceptionType.receiveTimeout:
        return const NetworkException('استغرق الخادم وقتاً طويلاً للاستجابة، يرجى المحاولة لاحقاً.');
      case DioExceptionType.connectionError:
        return const NetworkException('تعذر الاتصال بالخادم، يرجى التحقق من اتصال الإنترنت.');

      case DioExceptionType.badResponse:
        final response = error.response;
        final statusCode = response?.statusCode;
        final data = response?.data;

        String message = 'حدث خطأ أثناء معالجة الطلب.';
        if (data is Map<String, dynamic>) {
          if (data['message'] is String &&
              (data['message'] as String).isNotEmpty) {
            message = data['message'];
          }
        }

        if (statusCode == 422 && data is Map<String, dynamic>) {
          final rawErrors = data['errors'];
          final Map<String, List<String>> parsedErrors = {};

          if (rawErrors is Map<String, dynamic>) {
            rawErrors.forEach((key, value) {
              if (value is List) {
                parsedErrors[key] = value.map((e) => e.toString()).toList();
              } else if (value is String) {
                parsedErrors[key] = [value];
              }
            });
          }

          return ValidationException(
            message,
            errors: parsedErrors,
            statusCode: statusCode,
          );
        }

        if (statusCode == 401) {
          final arabicMessage = (message == 'Unauthenticated.' || message.isEmpty)
              ? 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مجدداً.'
              : message;
          return UnauthorizedException(arabicMessage, statusCode ?? 401);
        }

        if (statusCode == 403) {
          final arabicMessage = (message == 'This action is unauthorized.' || message.isEmpty)
              ? 'ليس لديك الصلاحية للقيام بهذا الإجراء.'
              : message;
          return ForbiddenException(arabicMessage, statusCode ?? 403);
        }

        if (statusCode == 404) {
          return NotFoundException(message, statusCode ?? 404);
        }

        if (statusCode == 409) {
          return ConflictException(message, statusCode ?? 409);
        }

        if (statusCode == 413) {
          return const ServerException('حجم الملفات المرفقة يتجاوز الحد المسموح به.', 413);
        }

        if (statusCode != null && statusCode >= 500) {
          return ServerException(message, statusCode);
        }

        return ServerException(message, statusCode);

      case DioExceptionType.cancel:
        return const NetworkException('تم إلغاء الطلب.');

      default:
        return NetworkException(
          error.message ?? 'حدث خطأ غير متوقع في الشبكة.',
        );
    }
  }
}
