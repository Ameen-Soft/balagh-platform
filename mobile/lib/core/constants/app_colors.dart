import 'package:flutter/material.dart';

/// App color palette matching the official Yemen theme from the web app:
/// `backend/resources/css/app.css` & `tailwind.config.js`
class AppColors {
  AppColors._();

  // --- Brand Colors (Yemen National Palette) ---
  static const Color yemenRed = Color(0xFFCE1126);
  static const Color yemenRedHover = Color(0xFFB70E20);
  static const Color yemenRedLight = Color(0xFFFEF2F2);
  static const Color yemenRedTint = Color(0xFFFEE2E2);

  static const Color yemenBlack = Color(0xFF08080A);
  static const Color yemenBlackLight = Color(0xFF18181B);
  static const Color yemenBlackMuted = Color(0xFF27272A);

  static const Color yemenWhite = Color(0xFFFFFFFF);

  static const Color yemenGold = Color(0xFFD97706);
  static const Color yemenGoldLight = Color(0xFFFFFBEB);

  static const Color yemenEmerald = Color(0xFF059669);
  static const Color yemenEmeraldLight = Color(0xFFECFDF5);

  // --- Text Colors ---
  static const Color textPrimary = Color(0xFF08080A);
  static const Color textSecondary = Color(0xFF6B7280);
  static const Color textMuted = Color(0xFF9CA3AF);

  // --- UI Elements ---
  static const Color background = Color(0xFFFFFFFF);
  static const Color backgroundSubtle = Color(0xFFFBFBFB);
  static const Color borderSubtle = Color(0xFFE5E7EB);
  static const Color dotInactive = Color(0xFFE5E7EB);
  static const Color shadowColor = Color(0x14000000);
}
