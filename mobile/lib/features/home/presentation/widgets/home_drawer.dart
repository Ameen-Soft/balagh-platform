import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../auth/application/providers.dart';
import '../../../auth/domain/entities/user_entity.dart';
import 'home_support_modal.dart';

class HomeDrawer extends ConsumerWidget {
  final UserEntity? user;
  final VoidCallback? onLogout;

  const HomeDrawer({
    super.key,
    this.user,
    this.onLogout,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final effectiveUser = user ?? ref.watch(authNotifierProvider).user;
    final name = effectiveUser?.name.trim() ?? '';
    final initial = name.isNotEmpty ? name.characters.first : 'م';
    final isFieldWorker = effectiveUser?.isFieldWorker == true;

    return Drawer(
      backgroundColor: Colors.white,
      child: Column(
        children: [
          // Drawer Header with User Profile
          Container(
            padding: EdgeInsets.only(
              top: MediaQuery.of(context).padding.top + 20,
              bottom: 20,
              left: 20,
              right: 20,
            ),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  AppColors.yemenBlack,
                  AppColors.yemenBlackLight,
                ],
                begin: Alignment.topRight,
                end: Alignment.bottomLeft,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Avatar with first letter initial of user's real name
                    Container(
                      width: 54,
                      height: 54,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.white24, width: 2),
                      ),
                      child: ClipOval(
                        child: _buildInitialAvatar(initial),
                      ),
                    ),
                    // Role Badge
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: isFieldWorker
                            ? AppColors.yemenGold
                            : AppColors.yemenRed,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        isFieldWorker
                            ? 'صيانة ميدانية'
                            : (effectiveUser?.primaryRoleName ?? 'مواطن'),
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                Text(
                  effectiveUser?.name ?? 'مستخدم بادر',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 17,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  effectiveUser?.email ?? '',
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.7),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),

          // Menu Items
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(vertical: 8),
              children: [
                _buildDrawerItem(
                  icon: Icons.home_rounded,
                  title: 'الرئيسية',
                  onTap: () {
                    Navigator.of(context).pop();
                    context.go('/home');
                  },
                ),
                _buildDrawerItem(
                  icon: Icons.add_circle_outline_rounded,
                  title: 'تقديم بلاغ جديد',
                  onTap: () {
                    Navigator.of(context).pop();
                    context.push('/create-complaint');
                  },
                ),
                _buildDrawerItem(
                  icon: Icons.assignment_outlined,
                  title: 'متابعة بلاغاتي',
                  onTap: () {
                    Navigator.of(context).pop();
                    context.push('/my-complaints');
                  },
                ),
                _buildDrawerItem(
                  icon: Icons.campaign_outlined,
                  title: 'بلاغات المجتمع',
                  onTap: () {
                    Navigator.of(context).pop();
                    context.push('/community-complaints');
                  },
                ),
                _buildDrawerItem(
                  icon: Icons.account_tree_outlined,
                  title: 'المشاريع التنموية',
                  onTap: () {
                    Navigator.of(context).pop();
                    context.push('/projects');
                  },
                ),
                _buildDrawerItem(
                  icon: Icons.map_outlined,
                  title: 'الخريطة التفاعلية',
                  onTap: () {
                    Navigator.of(context).pop();
                    context.push('/map');
                  },
                ),
                if (isFieldWorker) ...[
                  const Divider(indent: 20, endIndent: 20),
                  _buildDrawerItem(
                    icon: Icons.engineering_rounded,
                    title: 'المهام الميدانية',
                    onTap: () {
                      Navigator.of(context).pop();
                      context.push('/tasks');
                    },
                  ),
                ],
                const Divider(indent: 20, endIndent: 20),
                _buildDrawerItem(
                  icon: Icons.notifications_none_rounded,
                  title: 'الإشعارات',
                  onTap: () {
                    Navigator.of(context).pop();
                    context.push('/notifications');
                  },
                ),
                _buildDrawerItem(
                  icon: Icons.person_outline_rounded,
                  title: 'الملف الشخصي',
                  onTap: () {
                    Navigator.of(context).pop();
                    context.push('/profile');
                  },
                ),
                _buildDrawerItem(
                  icon: Icons.support_agent_rounded,
                  title: 'الدعم والمساعدة',
                  onTap: () {
                    Navigator.of(context).pop();
                    HomeSupportModal.show(context);
                  },
                ),
              ],
            ),
          ),

          // Logout Button
          SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: ListTile(
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                tileColor: AppColors.yemenRedLight,
                leading: const Icon(
                  Icons.logout_rounded,
                  color: AppColors.yemenRed,
                ),
                title: const Text(
                  'تسجيل الخروج',
                  style: TextStyle(
                    color: AppColors.yemenRed,
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
                onTap: onLogout ?? () {
                  Navigator.of(context).pop();
                  _showDefaultLogoutDialog(context, ref);
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showDefaultLogoutDialog(BuildContext context, WidgetRef ref) {
    showDialog(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'تسجيل الخروج',
          textAlign: TextAlign.right,
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        content: const Text(
          'هل أنت متأكد من رغبتك في تسجيل الخروج من منصة بادر؟',
          textAlign: TextAlign.right,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(),
            child: const Text(
              'إلغاء',
              style: TextStyle(color: AppColors.textSecondary),
            ),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(dialogContext).pop();
              ref.read(authNotifierProvider.notifier).logout();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.yemenRed,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
            child: const Text('تأكيد الخروج'),
          ),
        ],
      ),
    );
  }

  Widget _buildInitialAvatar(String initial) {
    return Container(
      color: AppColors.yemenRed,
      alignment: Alignment.center,
      child: Text(
        initial,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 22,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }

  Widget _buildDrawerItem({
    required IconData icon,
    required String title,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Icon(icon, color: AppColors.yemenBlack, size: 22),
      title: Text(
        title,
        style: const TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.w700,
          color: AppColors.yemenBlack,
        ),
      ),
      onTap: onTap,
    );
  }
}
