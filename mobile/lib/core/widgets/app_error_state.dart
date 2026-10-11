import 'package:flutter/material.dart';
import '../constants/app_colors.dart';

class AppErrorState extends StatelessWidget {
  final String? message;
  final bool isNetworkError;
  final VoidCallback? onRetry;

  const AppErrorState({
    super.key,
    this.message,
    this.isNetworkError = false,
    this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 28.0, vertical: 24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 68,
              height: 68,
              decoration: BoxDecoration(
                color: isNetworkError
                    ? const Color(0xFFFEF3C7)
                    : AppColors.yemenRedLight,
                shape: BoxShape.circle,
              ),
              child: Icon(
                isNetworkError
                    ? Icons.wifi_off_rounded
                    : Icons.error_outline_rounded,
                size: 32,
                color: isNetworkError
                    ? AppColors.yemenGold
                    : AppColors.yemenRed,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              isNetworkError ? 'تعذر الاتصال بالخادم' : 'حدث خطأ غير متوقع',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: AppColors.deepBlack,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              message ??
                  (isNetworkError
                      ? 'يرجى التحقق من اتصال الإنترنت والمحاولة مرة أخرى.'
                      : 'حدث خطأ أثناء معالجة الطلب، يرجى المحاولة لاحقاً.'),
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 13,
                color: AppColors.textSecondary,
                height: 1.4,
              ),
            ),
            if (onRetry != null) ...[
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: onRetry,
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: const Text(
                  'إعادة المحاولة',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.deepBlack,
                  foregroundColor: Colors.white,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 22, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  elevation: 0,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
