import 'package:flutter/material.dart';
import '../../../../core/constants/app_colors.dart';

class HomeSupportModal extends StatelessWidget {
  const HomeSupportModal({super.key});

  static void show(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => const HomeSupportModal(),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Drag handle
            Center(
              child: Container(
                width: 42,
                height: 4,
                decoration: BoxDecoration(
                  color: const Color(0xFFE5E7EB),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.yemenRed.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.support_agent_rounded,
                    color: AppColors.yemenRed,
                    size: 26,
                  ),
                ),
                const SizedBox(width: 12),
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'الدعم والمساعدة',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        color: AppColors.yemenBlack,
                      ),
                    ),
                    Text(
                      'نحن هنا لمساعدتك على مدار الساعة',
                      style: TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 20),

            // Channels List
            _buildContactChannel(
              icon: Icons.phone_in_talk_rounded,
              title: 'الرقم الموحد المجاني',
              subtitle: '8000000 - خدمة المواطنين',
              actionLabel: 'اتصال',
              onTap: () => Navigator.of(context).pop(),
            ),
            const SizedBox(height: 10),

            _buildContactChannel(
              icon: Icons.mail_outline_rounded,
              title: 'البريد الإلكتروني',
              subtitle: 'support@balagh.gov.ye',
              actionLabel: 'إرسال',
              onTap: () => Navigator.of(context).pop(),
            ),
            const SizedBox(height: 10),

            _buildContactChannel(
              icon: Icons.chat_bubble_outline_rounded,
              title: 'المحادثة الفورية',
              subtitle: 'تواصل مع فريق الدعم المباشر',
              actionLabel: 'بدء',
              onTap: () => Navigator.of(context).pop(),
            ),
            const SizedBox(height: 10),

            _buildContactChannel(
              icon: Icons.help_outline_rounded,
              title: 'الأسئلة الشائعة',
              subtitle: 'إجابات على أكثر الأسئلة تكراراً',
              actionLabel: 'عرض',
              onTap: () => Navigator.of(context).pop(),
            ),
            const SizedBox(height: 20),

            ElevatedButton(
              onPressed: () => Navigator.of(context).pop(),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.backgroundSubtle,
                foregroundColor: AppColors.yemenBlack,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                  side: const BorderSide(color: Color(0xFFE5E7EB)),
                ),
                padding: const EdgeInsets.symmetric(vertical: 13),
              ),
              child: const Text(
                'إغلاق',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContactChannel({
    required IconData icon,
    required String title,
    required String subtitle,
    required String actionLabel,
    required VoidCallback onTap,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: const Color(0xFFF9FAFB),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFF1F3F5)),
      ),
      child: Row(
        children: [
          Icon(icon, color: AppColors.yemenRed, size: 22),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: AppColors.yemenBlack,
                  ),
                ),
                Text(
                  subtitle,
                  style: const TextStyle(
                    fontSize: 11,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          OutlinedButton(
            onPressed: onTap,
            style: OutlinedButton.styleFrom(
              foregroundColor: AppColors.yemenRed,
              side: const BorderSide(color: AppColors.yemenRed),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              minimumSize: Size.zero,
            ),
            child: Text(
              actionLabel,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
    );
  }
}
