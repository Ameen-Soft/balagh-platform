import 'package:flutter/material.dart';
import 'package:mobile/core/constants/app_colors.dart';

/// Global application theme configuration
class AppTheme {
  AppTheme._();

  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      scaffoldBackgroundColor: AppColors.background,
      fontFamily: 'Cairo',
      colorScheme: ColorScheme.fromSeed(
        seedColor: AppColors.yemenRed,
        primary: AppColors.yemenRed,
        secondary: AppColors.yemenBlack,
        surface: AppColors.background,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: AppColors.background,
        elevation: 0,
        centerTitle: true,
        iconTheme: IconThemeData(color: AppColors.yemenBlack),
      ),
    );
  }
}
