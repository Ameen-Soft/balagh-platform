import 'package:flutter/material.dart';
import '../constants/app_colors.dart';

/// Clean inline error banner to present authentication or validation errors
class AppErrorBanner extends StatelessWidget {
  final String message;
  final VoidCallback? onDismiss;

  const AppErrorBanner({
    super.key,
    required this.message,
    this.onDismiss,
  });

  @override
  Widget build(BuildContext context) {
    if (message.trim().isEmpty) return const SizedBox.shrink();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: AppColors.yemenRedLight,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.yemenRedTint, width: 1.0),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Padding(
            padding: EdgeInsetsDirectional.only(top: 1.0),
            child: Icon(
              Icons.error_outline_rounded,
              color: AppColors.yemenRed,
              size: 18,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: AppColors.yemenRed,
                height: 1.35,
              ),
            ),
          ),
          if (onDismiss != null) ...[
            const SizedBox(width: 6),
            InkWell(
              onTap: onDismiss,
              borderRadius: BorderRadius.circular(12),
              child: const Padding(
                padding: EdgeInsets.all(2),
                child: Icon(
                  Icons.close_rounded,
                  color: AppColors.yemenRed,
                  size: 16,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
