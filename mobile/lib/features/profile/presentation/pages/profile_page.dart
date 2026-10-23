import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/constants/app_colors.dart';
import '../../../../core/utils/app_date_formatter.dart';
import '../../../auth/application/providers.dart';
import '../../../auth/domain/entities/user_entity.dart';
import '../../application/profile_providers.dart';
import '../../application/profile_state.dart';
import '../widgets/profile_info_item.dart';

class ProfilePage extends ConsumerStatefulWidget {
  const ProfilePage({super.key});

  @override
  ConsumerState<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends ConsumerState<ProfilePage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(profileNotifierProvider.notifier).loadProfile();
    });
  }

  void _showLogoutDialog(BuildContext context, WidgetRef ref) {
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

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(profileNotifierProvider);
    final authUser = ref.watch(authNotifierProvider).user;
    final user = state.user ?? authUser;

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      // تمديد الواجهة خلف الـ AppBar لرسم الخلفية الداكنة في الأعلى بسلاسة
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        // زر العودة (Arrow forward context pop in RTL)
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back_ios_new_rounded,
            color: Colors.white,
            size: 20,
          ),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
        ),
        title: const Text(
          'الملف الشخصي',
          style: TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'الإعدادات',
            icon: const Icon(Icons.settings_outlined, color: Colors.white),
            onPressed: () => context.push('/profile/edit'),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        color: AppColors.yemenRed,
        onRefresh: () async {
          await ref
              .read(profileNotifierProvider.notifier)
              .loadProfile(refresh: true);
        },
        child: _buildContent(state, user),
      ),
    );
  }

  Widget _buildContent(ProfileState state, UserEntity? user) {
    if (state.isLoading && user == null) {
      return const Center(
        child: CircularProgressIndicator(color: AppColors.yemenRed),
      );
    }

    if (state.hasError && user == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.error_outline_rounded,
                size: 48,
                color: AppColors.yemenRed,
              ),
              const SizedBox(height: 16),
              Text(
                state.errorMessage ?? 'تعذر تحميل بيانات الملف الشخصي.',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 14,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: () => ref
                    .read(profileNotifierProvider.notifier)
                    .loadProfile(refresh: true),
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: const Text('إعادة المحاولة'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.deepBlack,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (user == null) {
      return const Center(child: Text('لا توجد بيانات مستخدم متوفرة حالياً.'));
    }

    final isFieldWorker = user.isFieldWorker;
    final roleColor = isFieldWorker ? AppColors.yemenGold : AppColors.yemenRed;

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 1. الجزء العلوي (Header)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.fromLTRB(16, 120, 16, 32),
            decoration: const BoxDecoration(
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(32),
                bottomRight: Radius.circular(32),
              ),
              image: DecorationImage(
                image: AssetImage('assets/images/profilepageImage .png'),
                fit: BoxFit.cover,
              ),
            ),
            child: Column(
              children: [
                // الصورة الشخصية / Avatar
                Container(
                  padding: const EdgeInsets.all(3),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: Colors.white.withValues(alpha: 0.2),
                      width: 1.5,
                    ),
                  ),
                  child: CircleAvatar(
                    radius: 46,
                    backgroundColor: Colors.white.withValues(alpha: 0.15),
                    child: Icon(
                      isFieldWorker
                          ? Icons.engineering_rounded
                          : Icons.person_rounded,
                      color: Colors.white,
                      size: 52,
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                // الاسم
                Text(
                  user.name,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.w800,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 6),
                // البريد الإلكتروني
                Text(
                  user.email,
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.7),
                    fontSize: 13,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 14),
                // دور المستخدم
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: AppColors.yemenRed, // مظهر أحمر العلم اليمني كلياً
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(
                        Icons.person_outline_rounded,
                        color: Colors.white,
                        size: 14,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        user.primaryRoleName,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // 2. الجزء السفلي (المعلومات والبطاقات)
          Padding(
            padding: const EdgeInsets.all(20.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // بطاقة المعلومات الشخصية
                _buildCard(
                  title: 'المعلومات الشخصية',
                  titleIcon: Icons.person_outline_rounded,
                  children: [
                    ProfileInfoItem(
                      icon: Icons.person_outline_rounded,
                      label: 'الاسم الكامل',
                      value: user.name,
                    ),
                    const Divider(height: 1, color: AppColors.borderSubtle),
                    ProfileInfoItem(
                      icon: Icons.email_outlined,
                      label: 'البريد الإلكتروني',
                      value: user.email,
                      isReadOnly: true,
                    ),
                    const Divider(height: 1, color: AppColors.borderSubtle),
                    ProfileInfoItem(
                      icon: Icons.phone_outlined,
                      label: 'رقم الهاتف',
                      value: (user.phone != null && user.phone!.isNotEmpty)
                          ? user.phone!
                          : 'غير مسجل',
                    ),
                    const Divider(height: 1, color: AppColors.borderSubtle),
                    ProfileInfoItem(
                      icon: Icons.badge_outlined,
                      label: 'رقم الهوية الوطنية',
                      value:
                          (user.nationalId != null &&
                              user.nationalId!.isNotEmpty)
                          ? user.nationalId!
                          : 'غير مسجل',
                    ),
                  ],
                ),

                const SizedBox(height: 16),

                // بطاقة البيانات الوظيفية
                _buildCard(
                  title: 'البيانات الوظيفية',
                  titleIcon: Icons.work_outline_rounded,
                  children: [
                    ProfileInfoItem(
                      icon: Icons.verified_user_outlined,
                      label: 'نوع الحساب',
                      value: user.primaryRoleName,
                      isReadOnly: true,
                      iconColor: roleColor,
                    ),
                    if (user.department != null) ...[
                      const Divider(height: 1, color: AppColors.borderSubtle),
                      ProfileInfoItem(
                        icon: Icons.apartment_rounded,
                        label: 'الجهة التابعة',
                        value: user.department!.name,
                        isReadOnly: true,
                      ),
                    ],
                    if (user.createdAt != null) ...[
                      const Divider(height: 1, color: AppColors.borderSubtle),
                      ProfileInfoItem(
                        icon: Icons.calendar_today_outlined,
                        label: 'تاريخ التسجيل',
                        value: AppDateFormatter.formatDate(user.createdAt),
                        isReadOnly: true,
                      ),
                    ],
                  ],
                ),

                const SizedBox(height: 24),

                // 3. الأزرار (تعديل - تسجيل خروج)
                ElevatedButton.icon(
                  onPressed: () => context.push('/profile/edit'),
                  icon: const Icon(Icons.edit_note_rounded, size: 20),
                  label: const Text(
                    'تعديل البيانات الشخصية',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.yemenBlack,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    elevation: 0,
                  ),
                ),

                const SizedBox(height: 12),

                OutlinedButton.icon(
                  onPressed: () => _showLogoutDialog(context, ref),
                  icon: const Icon(
                    Icons.logout_rounded,
                    size: 18,
                    color: AppColors.yemenRed,
                  ),
                  label: const Text(
                    'تسجيل الخروج من المنصة',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 14,
                      color: AppColors.yemenRed,
                    ),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(
                      color: AppColors.yemenRed,
                      width: 1.2,
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                ),

                const SizedBox(height: 24),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCard({
    required String title,
    required IconData titleIcon,
    required List<Widget> children,
  }) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.borderSubtle.withValues(alpha: 0.5)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.015),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // رأس البطاقة (Title & Icon on the far right RTL)
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w800,
                  color: AppColors.yemenBlack,
                ),
              ),
              const SizedBox(width: 8),
              Icon(titleIcon, color: AppColors.yemenRed, size: 20),
            ],
          ),
          const SizedBox(height: 16),
          ...children,
        ],
      ),
    );
  }
}
