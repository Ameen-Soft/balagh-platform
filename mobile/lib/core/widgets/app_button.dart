import 'package:flutter/material.dart';
import '../constants/app_colors.dart';

enum AppButtonVariant {
  primary,
  secondary,
  outline,
  text,
}

/// Standard Balagh button component supporting primary, secondary, and loading states
class AppButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final bool isLoading;
  final bool isDisabled;
  final IconData? icon;
  final AppButtonVariant variant;
  final double height;
  final double? width;
  final double borderRadius;

  const AppButton({
    super.key,
    required this.text,
    required this.onPressed,
    this.isLoading = false,
    this.isDisabled = false,
    this.icon,
    this.variant = AppButtonVariant.primary,
    this.height = 52.0,
    this.width,
    this.borderRadius = 12.0,
  });

  @override
  Widget build(BuildContext context) {
    final bool effectiveDisabled = isDisabled || isLoading || onPressed == null;

    final baseTextStyle = TextStyle(
      fontSize: 15,
      fontWeight: FontWeight.w700,
      letterSpacing: -0.2,
      color: _getTextColor(effectiveDisabled),
    );

    Widget content;
    if (isLoading) {
      content = SizedBox(
        width: 22,
        height: 22,
        child: CircularProgressIndicator(
          strokeWidth: 2.4,
          color: variant == AppButtonVariant.primary ? Colors.white : AppColors.yemenRed,
        ),
      );
    } else if (icon != null) {
      content = Row(
        mainAxisSize: MainAxisSize.min,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 19, color: _getTextColor(effectiveDisabled)),
          const SizedBox(width: 8),
          Text(text, style: baseTextStyle),
        ],
      );
    } else {
      content = Text(text, style: baseTextStyle);
    }

    final buttonShape = RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(borderRadius),
      side: _getBorderSide(effectiveDisabled),
    );

    return SizedBox(
      height: height,
      width: width ?? double.infinity,
      child: Material(
        color: _getBackgroundColor(effectiveDisabled),
        shape: buttonShape,
        elevation: (variant == AppButtonVariant.primary && !effectiveDisabled) ? 0.5 : 0,
        shadowColor: AppColors.shadowColor,
        child: InkWell(
          onTap: effectiveDisabled ? null : onPressed,
          borderRadius: BorderRadius.circular(borderRadius),
          splashColor: variant == AppButtonVariant.primary
              ? Colors.white.withValues(alpha: 0.15)
              : AppColors.yemenRed.withValues(alpha: 0.08),
          highlightColor: variant == AppButtonVariant.primary
              ? Colors.white.withValues(alpha: 0.08)
              : AppColors.yemenRed.withValues(alpha: 0.04),
          child: Center(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: content,
            ),
          ),
        ),
      ),
    );
  }

  Color _getBackgroundColor(bool disabled) {
    switch (variant) {
      case AppButtonVariant.primary:
        return disabled
            ? AppColors.yemenRed.withValues(alpha: 0.5)
            : AppColors.yemenRed;
      case AppButtonVariant.secondary:
        return disabled ? const Color(0xFFF3F4F6) : const Color(0xFFF9FAFB);
      case AppButtonVariant.outline:
      case AppButtonVariant.text:
        return Colors.transparent;
    }
  }

  Color _getTextColor(bool disabled) {
    switch (variant) {
      case AppButtonVariant.primary:
        return Colors.white;
      case AppButtonVariant.secondary:
        return disabled ? AppColors.textMuted : AppColors.textPrimary;
      case AppButtonVariant.outline:
        return disabled ? AppColors.textMuted : AppColors.yemenRed;
      case AppButtonVariant.text:
        return disabled ? AppColors.textMuted : AppColors.yemenRed;
    }
  }

  BorderSide _getBorderSide(bool disabled) {
    switch (variant) {
      case AppButtonVariant.primary:
      case AppButtonVariant.text:
        return BorderSide.none;
      case AppButtonVariant.secondary:
        return const BorderSide(color: AppColors.borderSubtle, width: 1.0);
      case AppButtonVariant.outline:
        return BorderSide(
          color: disabled ? AppColors.borderSubtle : AppColors.yemenRed,
          width: 1.2,
        );
    }
  }
}
