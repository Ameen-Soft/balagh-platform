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
            child: const Text('إلغاء',
                style: TextStyle(color: AppColors.textSecondary)),
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
                  borderRadius: BorderRadius.circular(10)),
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
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded,
              color: AppColors.yemenBlack, size: 20),
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
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
        actions: [
          if (user != null)
            IconButton(
              tooltip: 'تعديل الملف الشخصي',
              icon: const Icon(Icons.edit_outlined, color: AppColors.yemenBlack),
              onPressed: () => context.push('/profile/edit'),
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: RefreshIndicator(
          color: AppColors.yemenRed,
          onRefresh: () async {
            await ref
                .read(profileNotifierProvider.notifier)
                .loadProfile(refresh: true);
          },
          child: _buildContent(state, user),
        ),
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
              const Icon(Icons.error_outline_rounded,
                  size: 48, color: AppColors.yemenRed),
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
      return const Center(
        child: Text('لا توجد بيانات مستخدم متوفرة حالياً.'),
      );
    }

    final isFieldWorker = user.isFieldWorker;
    final roleColor = isFieldWorker ? AppColors.yemenGold : AppColors.yemenRed;

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 1. Header Profile Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [
                  AppColors.yemenBlackLight,
                  AppColors.yemenBlack,
                ],
                begin: Alignment.topRight,
                end: Alignment.bottomLeft,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: AppColors.yemenBlack.withValues(alpha: 0.15),
                  blurRadius: 12,
                  offset: const Offset(0, 5),
                ),
              ],
            ),
            child: Column(
              children: [
                CircleAvatar(
                  radius: 36,
                  backgroundColor: Colors.white.withValues(alpha: 0.15),
                  child: Icon(
                    isFieldWorker
                        ? Icons.engineering_rounded
                        : Icons.person_rounded,
                    color: Colors.white,
                    size: 40,
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  user.name,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 6),
                Text(
                  user.email,
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.8),
                    fontSize: 13,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 12),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                  decoration: BoxDecoration(
                    color: roleColor,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    user.primaryRoleName,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          // 2. Personal Information Card
          _buildCard(
            title: 'المعلومات الشخصية',
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
                value: (user.nationalId != null && user.nationalId!.isNotEmpty)
                    ? user.nationalId!
                    : 'غير مسجل',
              ),
            ],
          ),

          const SizedBox(height: 16),

          // 3. System & Role Details Card
          _buildCard(
            title: 'بيانات الصلاحيات والنظام',
            children: [
              ProfileInfoItem(
                icon: Icons.verified_user_outlined,
                label: 'نوع الحساب / الدور',
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
                  label: 'تاريخ التسجيل بالمنصة',
                  value: AppDateFormatter.formatDate(user.createdAt),
                  isReadOnly: true,
                ),
              ],
            ],
          ),

          const SizedBox(height: 20),

          // 4. Action Buttons
          ElevatedButton.icon(
            onPressed: () => context.push('/profile/edit'),
            icon: const Icon(Icons.edit_note_rounded, size: 20),
            label: const Text(
              'تعديل بيانات الملف الشخصي',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.deepBlack,
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
            icon: const Icon(Icons.logout_rounded,
                size: 20, color: AppColors.yemenRed),
            label: const Text(
              'تسجيل الخروج من المنصة',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 14,
                color: AppColors.yemenRed,
              ),
            ),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: AppColors.yemenRed, width: 1.2),
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
          ),

          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildCard({
    required String title,
    required List<Widget> children,
  }) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w700,
              color: AppColors.yemenBlack,
            ),
          ),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }
}
