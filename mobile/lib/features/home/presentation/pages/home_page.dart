import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/core/constants/app_colors.dart';
import 'package:mobile/features/auth/application/providers.dart';
import 'package:mobile/features/complaints/application/complaints_providers.dart';
import 'package:mobile/features/complaints/presentation/widgets/complaint_card.dart';

class HomePage extends ConsumerWidget {
  const HomePage({super.key});

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
          'هل أنت متأكد من رغبتك في تسجيل الخروج من منصة بلاغ؟',
          textAlign: TextAlign.right,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(),
            child: const Text('إلغاء', style: TextStyle(color: AppColors.textSecondary)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(dialogContext).pop();
              ref.read(authNotifierProvider.notifier).logout();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.yemenRed,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('تأكيد الخروج'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        title: const Text(
          'الرئيسية',
          style: TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 20,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'تسجيل الخروج',
            icon: const Icon(Icons.logout_rounded, color: AppColors.yemenRed),
            onPressed: () => _showLogoutDialog(context, ref),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: RefreshIndicator(
          color: AppColors.yemenRed,
          onRefresh: () async {
            ref.invalidate(recentComplaintsProvider);
            try {
              await ref.read(recentComplaintsProvider.future);
            } catch (_) {}
          },
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(20.0),
            child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Welcome Card
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
                      color: AppColors.yemenBlack.withValues(alpha: 0.18),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: AppColors.yemenRed,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            user?.primaryRoleName ?? 'مواطن',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        CircleAvatar(
                          radius: 22,
                          backgroundColor: Colors.white.withValues(alpha: 0.15),
                          child: const Icon(
                            Icons.person_rounded,
                            color: Colors.white,
                            size: 26,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'أهلاً بك، ${user?.name ?? "مستخدم بلاغ"}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      user?.email ?? '',
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.8),
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // User Info Details Card
              Container(
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
                    const Text(
                      'بيانات الحساب',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: AppColors.yemenBlack,
                      ),
                    ),
                    const Divider(height: 24),
                    _buildInfoRow(
                      icon: Icons.phone_outlined,
                      label: 'رقم الهاتف',
                      value: (user?.phone != null && user!.phone!.isNotEmpty)
                          ? user.phone!
                          : 'غير مسجل',
                    ),
                    const SizedBox(height: 12),
                    _buildInfoRow(
                      icon: Icons.badge_outlined,
                      label: 'رقم الهوية الوطنية',
                      value: (user?.nationalId != null && user!.nationalId!.isNotEmpty)
                          ? user.nationalId!
                          : 'غير مسجل',
                    ),
                    if (user?.department != null) ...[
                      const SizedBox(height: 12),
                      _buildInfoRow(
                        icon: Icons.business_outlined,
                        label: 'الجهة / القسم',
                        value: user!.department!.name,
                      ),
                    ],
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Action Cards Title
              const Text(
                'الخدمات السريعة',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: AppColors.yemenBlack,
                ),
              ),
              const SizedBox(height: 12),

              Row(
                children: [
                  Expanded(
                    child: _buildServiceCard(
                      icon: Icons.campaign_rounded,
                      title: 'تقديم بلاغ',
                      color: AppColors.yemenRed,
                      onTap: () => context.push('/create-complaint'),
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: _buildServiceCard(
                      icon: Icons.track_changes_rounded,
                      title: 'متابعة البلاغات',
                      color: AppColors.yemenEmerald,
                      onTap: () => context.push('/my-complaints'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: _buildServiceCard(
                      icon: Icons.construction_rounded,
                      title: 'المشاريع المجتمعية',
                      color: AppColors.yemenGold,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: _buildServiceCard(
                      icon: Icons.support_agent_rounded,
                      title: 'الدعم والمساعدة',
                      color: AppColors.yemenBlackMuted,
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 24),

              // Recent Complaints Section
              _buildRecentComplaintsSection(context, ref),

              const SizedBox(height: 24),
            ],
          ),
        ),
        ),
      ),
    );
  }

  Widget _buildRecentComplaintsSection(BuildContext context, WidgetRef ref) {
    final recentAsync = ref.watch(recentComplaintsProvider);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'شكاواي الأخيرة',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: AppColors.yemenBlack,
              ),
            ),
            TextButton(
              onPressed: () => context.push('/my-complaints'),
              child: const Text(
                'عرض الكل',
                style: TextStyle(
                  color: AppColors.yemenRed,
                  fontWeight: FontWeight.w700,
                  fontSize: 13,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        recentAsync.when(
          data: (complaints) {
            if (complaints.isEmpty) {
              return Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.borderSubtle),
                ),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 20,
                      backgroundColor: AppColors.yemenRedLight,
                      child: const Icon(Icons.inbox_outlined,
                          size: 20, color: AppColors.yemenRed),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Text(
                        'لا توجد بلاغات مسجلة بعد. يمكنك تقديم بلاغ جديد الآن.',
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ),
                    TextButton(
                      onPressed: () => context.push('/create-complaint'),
                      child: const Text(
                        'تقديم',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: AppColors.yemenRed,
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }

            return Column(
              children: complaints.take(2).map((item) {
                return Padding(
                  padding: const EdgeInsets.only(bottom: 12.0),
                  child: ComplaintCard(
                    complaint: item,
                    onTap: () =>
                        context.push('/complaints/${item.id}', extra: item),
                  ),
                );
              }).toList(),
            );
          },
          loading: () => const Center(
            child: Padding(
              padding: EdgeInsets.all(16.0),
              child: CircularProgressIndicator(color: AppColors.yemenRed),
            ),
          ),
          error: (err, _) => Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.borderSubtle),
            ),
            child: Row(
              children: [
                const Icon(Icons.info_outline_rounded,
                    color: AppColors.textMuted, size: 20),
                const SizedBox(width: 8),
                const Expanded(
                  child: Text(
                    'تعذر استرجاع الشكاوى الأخيرة.',
                    style: TextStyle(
                      fontSize: 12,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ),
                TextButton(
                  onPressed: () => ref.invalidate(recentComplaintsProvider),
                  child: const Text(
                    'إعادة المحاولة',
                    style: TextStyle(
                      fontSize: 12,
                      color: AppColors.yemenBlack,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppColors.textSecondary),
        const SizedBox(width: 12),
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            color: AppColors.textSecondary,
          ),
        ),
        const Spacer(),
        Text(
          value,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: AppColors.textPrimary,
          ),
        ),
      ],
    );
  }

  Widget _buildServiceCard({
    required IconData icon,
    required String title,
    required Color color,
    VoidCallback? onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.borderSubtle),
        ),
        child: Column(
          children: [
            CircleAvatar(
              radius: 24,
              backgroundColor: color.withValues(alpha: 0.1),
              child: Icon(icon, color: color, size: 26),
            ),
            const SizedBox(height: 12),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: AppColors.textPrimary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
