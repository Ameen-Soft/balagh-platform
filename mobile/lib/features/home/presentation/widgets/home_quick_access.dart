import 'package:flutter/material.dart';

import '../../../../core/constants/app_colors.dart';

class HomeQuickAccess extends StatelessWidget {
  final VoidCallback onPublicComplaintsTap;
  final VoidCallback onMyComplaintsTap;
  final VoidCallback onProjectsTap;
  final VoidCallback onMapTap;
  final VoidCallback onProfileTap;
  final VoidCallback onNotificationsTap;
  final VoidCallback onSupportTap;

  const HomeQuickAccess({
    super.key,
    required this.onPublicComplaintsTap,
    required this.onMyComplaintsTap,
    required this.onProjectsTap,
    required this.onMapTap,
    required this.onProfileTap,
    required this.onNotificationsTap,
    required this.onSupportTap,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Section Title with red accent underline
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'الوصول السريع',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.w800,
                color: AppColors.yemenBlack,
              ),
            ),
            const SizedBox(height: 4),
            Container(
              width: 36,
              height: 3.5,
              decoration: BoxDecoration(
                color: AppColors.yemenRed,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ],
        ),
        const SizedBox(height: 14),

        // Row 1: 4 Cards (Clean icons & titles without subtitles for optimal UX)
        Row(
          children: [
            Expanded(
              child: _buildQuickCard(
                icon: Icons.campaign_rounded,
                iconColor: AppColors.yemenRed,
                title: 'البلاغات العامة',
                onTap: onPublicComplaintsTap,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildQuickCard(
                icon: Icons.assignment_outlined,
                iconColor: const Color(0xFF374151),
                title: 'متابعة البلاغات',
                onTap: onMyComplaintsTap,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildQuickCard(
                icon: Icons.account_tree_outlined,
                iconColor: AppColors.yemenRed,
                title: 'المشاريع المجتمعية',
                onTap: onProjectsTap,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildQuickCard(
                icon: Icons.map_outlined,
                iconColor: const Color(0xFF374151),
                title: 'الخريطة',
                onTap: onMapTap,
              ),
            ),
          ],
        ),

        const SizedBox(height: 10),

        // Row 2: 3 Cards (wider)
        Row(
          children: [
            Expanded(
              child: _buildQuickCard(
                icon: Icons.person_rounded,
                iconColor: AppColors.yemenRed,
                title: 'الملف الشخصي',
                onTap: onProfileTap,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildQuickCard(
                icon: Icons.notifications_active_outlined,
                iconColor: AppColors.yemenRed,
                title: 'الإشعارات',
                onTap: onNotificationsTap,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildQuickCard(
                icon: Icons.headset_mic_outlined,
                iconColor: const Color(0xFF374151),
                title: 'الدعم والمساعدة',
                onTap: onSupportTap,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildQuickCard({
    required IconData icon,
    required Color iconColor,
    required String title,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 4),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFF1F3F5), width: 1.2),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.025),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 28, color: iconColor),
            const SizedBox(height: 8),
            Text(
              title,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: AppColors.yemenBlack,
                height: 1.25,
              ),
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}
