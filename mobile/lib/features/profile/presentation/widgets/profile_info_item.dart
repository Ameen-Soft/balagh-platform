import 'package:flutter/material.dart';

import '../../../../core/constants/app_colors.dart';

class ProfileInfoItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final bool isReadOnly;
  final Color? iconColor;

  const ProfileInfoItem({
    super.key,
    required this.icon,
    required this.label,
    required this.value,
    this.isReadOnly = false,
    this.iconColor,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // السهم الأيسر (Chevron pointing left) لإنشاء مظهر تصفح أنيق
          Icon(Icons.chevron_left_rounded, color: Colors.grey[400], size: 20),
          const Spacer(),
          // تفاصيل النصوص (Label on top, Value on bottom - RTL aligment)
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 11,
                    color: AppColors.textSecondary,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 13,
                    color: AppColors.yemenBlack,
                    fontWeight: FontWeight.w800,
                  ),
                  textAlign: TextAlign.right,
                ),
              ],
            ),
          ),
          const SizedBox(width: 14),
          // الأيقونة محاطة بخلفية خفيفة جداً وعصرية في الجهة اليمنى
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: (iconColor ?? AppColors.yemenBlack).withValues(alpha: 0.06),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              icon,
              size: 18,
              color: iconColor ?? AppColors.yemenBlack,
            ),
          ),
        ],
      ),
    );
  }
}
