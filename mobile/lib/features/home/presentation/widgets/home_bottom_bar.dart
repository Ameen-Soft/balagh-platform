import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';

class HomeBottomBar extends StatelessWidget {
  final VoidCallback onHomeTap;
  final VoidCallback onMapTap;
  final VoidCallback onCreateComplaintTap;
  final VoidCallback onComplaintsTap;
  final VoidCallback onMoreTap;

  const HomeBottomBar({
    super.key,
    required this.onHomeTap,
    required this.onMapTap,
    required this.onCreateComplaintTap,
    required this.onComplaintsTap,
    required this.onMoreTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: const Border(
          top: BorderSide(
            color: Color(0xFFE5E7EB),
            width: 1.0,
          ),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 68,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // 1. الرئيسية (Selected with Red indicator)
              _buildNavItem(
                icon: Icons.home_rounded,
                label: 'الرئيسية',
                isSelected: true,
                onTap: onHomeTap,
              ),

              // 2. الخريطة
              _buildNavItem(
                icon: Icons.map_outlined,
                label: 'الخريطة',
                isSelected: false,
                onTap: onMapTap,
              ),

              // 3. Center FAB: بلاغ جديد
              _buildCenterFab(
                label: 'بلاغ جديد',
                onTap: onCreateComplaintTap,
              ),

              // 4. البلاغات
              _buildNavItem(
                icon: Icons.assignment_outlined,
                label: 'البلاغات',
                isSelected: false,
                onTap: onComplaintsTap,
              ),

              // 5. المزيد
              _buildNavItem(
                icon: Icons.more_horiz_rounded,
                label: 'المزيد',
                isSelected: false,
                onTap: onMoreTap,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem({
    required IconData icon,
    required String label,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    final color = isSelected ? AppColors.yemenRed : const Color(0xFF6B7280);

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 24,
              color: color,
            ),
            const SizedBox(height: 3),
            Text(
              label,
              style: TextStyle(
                color: color,
                fontSize: 11,
                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w500,
              ),
            ),
            const SizedBox(height: 3),
            // Active underline indicator
            Container(
              width: 22,
              height: 2.5,
              decoration: BoxDecoration(
                color: isSelected ? AppColors.yemenRed : Colors.transparent,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCenterFab({
    required String label,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: AppColors.yemenRed,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: AppColors.yemenRed.withValues(alpha: 0.38),
                  blurRadius: 8,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: const Icon(
              Icons.add_rounded,
              color: Colors.white,
              size: 28,
            ),
          ),
          const SizedBox(height: 3),
          Text(
            label,
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w800,
              color: AppColors.yemenBlack,
            ),
          ),
        ],
      ),
    );
  }
}
