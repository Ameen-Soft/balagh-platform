import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/core/constants/app_colors.dart';
import 'package:mobile/features/auth/application/providers.dart';
import 'package:mobile/features/auth/domain/entities/user_entity.dart';
import 'package:mobile/features/complaints/application/complaints_providers.dart';
import 'package:mobile/features/complaints/presentation/widgets/complaint_card.dart';
import 'package:mobile/features/field_work/application/providers/field_work_providers.dart';
import 'package:mobile/features/field_work/presentation/widgets/task_card.dart';

class HomePage extends ConsumerStatefulWidget {
  const HomePage({super.key});

  @override
  ConsumerState<HomePage> createState() => _HomePageState();
}

class _HomePageState extends ConsumerState<HomePage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = ref.read(authNotifierProvider).user;
      if (user?.isFieldWorker == true) {
        ref.read(fieldWorkNotifierProvider.notifier).loadAssignments();
      }
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
          'هل أنت متأكد من رغبتك في تسجيل الخروج من منصة بلاغ؟',
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
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;
    final isFieldWorker = user?.isFieldWorker == true;

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.deepBlack,
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Text(
                'بادر',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w900,
                  fontSize: 14,
                ),
              ),
            ),
            const SizedBox(width: 8),
            Text(
              isFieldWorker ? 'بوابة العمل الميداني' : 'الرئيسية',
              style: const TextStyle(
                color: AppColors.yemenBlack,
                fontWeight: FontWeight.w800,
                fontSize: 18,
              ),
            ),
          ],
        ),
        actions: [
          if (isFieldWorker)
            IconButton(
              tooltip: 'المهام الميدانية',
              icon: const Icon(Icons.assignment_outlined,
                  color: AppColors.yemenBlack),
              onPressed: () => context.push('/tasks'),
            ),
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
            if (isFieldWorker) {
              await ref
                  .read(fieldWorkNotifierProvider.notifier)
                  .loadAssignments();
            } else {
              ref.invalidate(recentComplaintsProvider);
              try {
                await ref.read(recentComplaintsProvider.future);
              } catch (_) {}
            }
          },
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(20.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // 1. Welcome Card (Personalized for Field Worker vs Citizen)
                _buildWelcomeCard(user, isFieldWorker),

                const SizedBox(height: 20),

                // 2. Field Worker Specialized Section
                if (isFieldWorker) ...[
                  _buildFieldWorkerHub(context, ref),
                  const SizedBox(height: 24),
                ],

                // 3. Citizen Quick Services
                if (!isFieldWorker) ...[
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
                          onTap: () => context.push('/projects'),
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

                  // Recent Complaints Section for Citizens
                  _buildRecentComplaintsSection(context, ref),
                ],

                const SizedBox(height: 16),

                // Account Details Card
                _buildAccountInfoCard(user),

                const SizedBox(height: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildWelcomeCard(UserEntity? user, bool isFieldWorker) {
    return Container(
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
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: isFieldWorker ? AppColors.yemenGold : AppColors.yemenRed,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      isFieldWorker
                          ? Icons.engineering_rounded
                          : Icons.person_rounded,
                      size: 14,
                      color: Colors.white,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      isFieldWorker ? 'موظف صيانة ميدانية' : (user?.primaryRoleName ?? 'مواطن'),
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
              CircleAvatar(
                radius: 22,
                backgroundColor: Colors.white.withValues(alpha: 0.15),
                child: Icon(
                  isFieldWorker
                      ? Icons.handyman_rounded
                      : Icons.person_rounded,
                  color: Colors.white,
                  size: 24,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            'أهلاً بك، ${user?.name ?? "مستخدم بادر"}',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            user?.department != null
                ? 'الجهة: ${user!.department!.name}'
                : (user?.email ?? ''),
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.8),
              fontSize: 13,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFieldWorkerHub(BuildContext context, WidgetRef ref) {
    final fieldWorkState = ref.watch(fieldWorkNotifierProvider);
    final assignments = fieldWorkState.assignments;

    final pendingCount = assignments.where((a) => a.isPending).length;
    final inProgressCount = assignments.where((a) => a.isInProgress).length;
    final completedCount = assignments.where((a) => a.isCompleted).length;
    final activeTasks = assignments.where((a) => !a.isCompleted).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Section Header
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Row(
              children: [
                Icon(Icons.dashboard_customize_rounded,
                    size: 20, color: AppColors.deepBlack),
                SizedBox(width: 8),
                Text(
                  'لوحة مهام الصيانة الميدانية',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: AppColors.deepBlack,
                  ),
                ),
              ],
            ),
            TextButton.icon(
              onPressed: () => context.push('/tasks'),
              icon: const Icon(Icons.arrow_forward_rounded, size: 14),
              label: const Text(
                'سجل المهام',
                style: TextStyle(
                  color: AppColors.yemenRed,
                  fontWeight: FontWeight.bold,
                  fontSize: 13,
                ),
              ),
            ),
          ],
        ),

        const SizedBox(height: 12),

        // 4 KPI Metric Chips in a 2x2 grid
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                title: 'إجمالي المهام',
                count: assignments.length.toString(),
                icon: Icons.assignment_rounded,
                color: AppColors.deepBlack,
                onTap: () {
                  ref.read(fieldWorkNotifierProvider.notifier).setFilter('all');
                  context.push('/tasks');
                },
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildMetricCard(
                title: 'بانتظار القبول',
                count: pendingCount.toString(),
                icon: Icons.hourglass_top_rounded,
                color: AppColors.yemenGold,
                onTap: () {
                  ref.read(fieldWorkNotifierProvider.notifier).setFilter('pending');
                  context.push('/tasks');
                },
              ),
            ),
          ],
        ),

        const SizedBox(height: 12),

        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                title: 'قيد التنفيذ',
                count: inProgressCount.toString(),
                icon: Icons.engineering_rounded,
                color: AppColors.yemenRed,
                onTap: () {
                  ref
                      .read(fieldWorkNotifierProvider.notifier)
                      .setFilter('in_progress');
                  context.push('/tasks');
                },
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildMetricCard(
                title: 'أعمال منجزة',
                count: completedCount.toString(),
                icon: Icons.verified_rounded,
                color: AppColors.yemenEmerald,
                onTap: () {
                  ref
                      .read(fieldWorkNotifierProvider.notifier)
                      .setFilter('completed');
                  context.push('/tasks');
                },
              ),
            ),
          ],
        ),

        const SizedBox(height: 24),

        // Active Tasks Sub-header
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'المهام الميدانية الجارية',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w700,
                color: AppColors.yemenBlack,
              ),
            ),
            Text(
              '${activeTasks.length} مهام نشطة',
              style: const TextStyle(
                fontSize: 12,
                color: AppColors.textMuted,
              ),
            ),
          ],
        ),

        const SizedBox(height: 10),

        if (fieldWorkState.isLoading && assignments.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(24.0),
              child: CircularProgressIndicator(color: AppColors.yemenRed),
            ),
          )
        else if (activeTasks.isEmpty)
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.borderSubtle),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.yemenEmerald.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.check_circle_outline_rounded,
                    color: AppColors.yemenEmerald,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 14),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'لا توجد مهام معلقة حالياً',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'تم إنجاز كافة المهام الميدانية المسندة، أو بانتظار تكليفات جديدة من الإدارة.',
                        style: TextStyle(
                          fontSize: 11,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          )
        else
          Column(
            children: activeTasks.take(3).map((assignment) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 12.0),
                child: TaskCard(
                  assignment: assignment,
                  onTap: () {
                    ref
                        .read(fieldWorkNotifierProvider.notifier)
                        .selectAssignment(assignment.id);
                    context.push('/tasks/${assignment.id}', extra: assignment);
                  },
                ),
              );
            }).toList(),
          ),
      ],
    );
  }

  Widget _buildMetricCard({
    required String title,
    required String count,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.borderSubtle),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    count,
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                      color: color,
                    ),
                  ),
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 11,
                      color: AppColors.textSecondary,
                      fontWeight: FontWeight.w500,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAccountInfoCard(UserEntity? user) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'بيانات الحساب',
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w700,
              color: AppColors.yemenBlack,
            ),
          ),
          const Divider(height: 20),
          _buildInfoRow(
            icon: Icons.phone_outlined,
            label: 'رقم الهاتف',
            value: (user?.phone != null && user!.phone!.isNotEmpty)
                ? user.phone!
                : 'غير مسجل',
          ),
          const SizedBox(height: 10),
          _buildInfoRow(
            icon: Icons.badge_outlined,
            label: 'رقم الهوية الوطنية',
            value: (user?.nationalId != null && user!.nationalId!.isNotEmpty)
                ? user.nationalId!
                : 'غير مسجل',
          ),
          if (user?.department != null) ...[
            const SizedBox(height: 10),
            _buildInfoRow(
              icon: Icons.business_outlined,
              label: 'الجهة / القسم',
              value: user!.department!.name,
            ),
          ],
        ],
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
        Icon(icon, size: 18, color: AppColors.textSecondary),
        const SizedBox(width: 10),
        Text(
          label,
          style: const TextStyle(
            fontSize: 13,
            color: AppColors.textSecondary,
          ),
        ),
        const Spacer(),
        Text(
          value,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: AppColors.yemenBlack,
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
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 28),
            ),
            const SizedBox(height: 12),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: AppColors.yemenBlack,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
