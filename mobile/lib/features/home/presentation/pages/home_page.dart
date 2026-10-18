import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../auth/application/providers.dart';
import '../../../auth/domain/entities/user_entity.dart';
import '../../../complaints/application/complaints_providers.dart';
import '../../../field_work/application/providers/field_work_providers.dart';
import '../../../field_work/presentation/widgets/task_card.dart';
import '../../../notifications/application/notification_providers.dart';
import '../widgets/home_bottom_bar.dart';
import '../widgets/home_carousel_slider.dart';
import '../widgets/home_drawer.dart';
import '../widgets/home_public_complaints_section.dart';
import '../widgets/home_quick_access.dart';
import '../widgets/home_stats_row.dart';
import '../widgets/home_support_modal.dart';

class HomePage extends ConsumerStatefulWidget {
  const HomePage({super.key});

  @override
  ConsumerState<HomePage> createState() => _HomePageState();
}

class _HomePageState extends ConsumerState<HomePage> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = ref.read(authNotifierProvider).user;
      if (user?.isFieldWorker == true) {
        ref.read(fieldWorkNotifierProvider.notifier).loadAssignments();
      }
      ref.read(communityComplaintsNotifierProvider.notifier).loadInitial();
      ref.read(notificationsNotifierProvider.notifier).refresh();
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
    final authState = ref.watch(authNotifierProvider);
    final user = authState.user;
    final isFieldWorker = user?.isFieldWorker == true;

    // Real complaints of user for status calculations
    final myComplaintsAsync = ref.watch(myComplaintsProvider);
    final myComplaints = myComplaintsAsync.value ?? const [];

    final newCount = myComplaints
        .where((c) => c.status == 'new' || c.status == 'submitted')
        .length;
    final underReviewCount =
        myComplaints.where((c) => c.status == 'under_review').length;
    final inProgressCount = myComplaints
        .where((c) => c.status == 'in_progress' || c.status == 'assigned')
        .length;
    final resolvedCount = myComplaints
        .where((c) => c.status == 'resolved' || c.status == 'closed')
        .length;

    // Real community/public complaints
    final communityState = ref.watch(communityComplaintsNotifierProvider);

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: const Color(0xFFF8F9FA),
      drawer: HomeDrawer(
        user: user,
        onLogout: () {
          Navigator.of(context).pop();
          _showLogoutDialog(context, ref);
        },
      ),
      bottomNavigationBar: HomeBottomBar(
        onHomeTap: () {},
        onMapTap: () => context.push('/map'),
        onCreateComplaintTap: () => context.push('/create-complaint'),
        onComplaintsTap: () => context.push('/my-complaints'),
        onMoreTap: () => _scaffoldKey.currentState?.openDrawer(),
      ),
      body: SafeArea(
        child: RefreshIndicator(
          color: AppColors.yemenRed,
          onRefresh: () async {
            if (isFieldWorker) {
              await ref
                  .read(fieldWorkNotifierProvider.notifier)
                  .loadAssignments();
            }
            ref.invalidate(myComplaintsProvider);
            ref.invalidate(recentComplaintsProvider);
            await ref
                .read(communityComplaintsNotifierProvider.notifier)
                .loadInitial();
            ref.read(notificationsNotifierProvider.notifier).refresh();
          },
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 12.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // 1. Top Bar matching the mockup
                _buildTopBar(context, ref, user),

                const SizedBox(height: 16),

                // 2. Carousel with 4 auto-sliding cards (every 5 seconds)
                HomeCarouselSlider(
                  items: [
                    // Card 1
                    HomeCarouselItem(
                      imagePath: 'assets/images/HomeCard1.jpg',
                      title: 'معاً .. نحو بيئة أفضل',
                      description:
                          'شارك في تحسين خدمات مدينتك من خلال الإبلاغ عن المشاكل والأعطال.',
                      buttonText: 'تقديم بلاغ',
                      onTap: () => context.push('/create-complaint'),
                    ),
                    // Card 2
                    HomeCarouselItem(
                      imagePath: 'assets/images/HomeCard2.jpg',
                      title: 'شاهد البلاغات على الخريطة',
                      description:
                          'استعرض جميع البلاغات العامة والمشاريع التنموية في موقعها على الخريطة.',
                      buttonText: 'عرض الخريطة',
                      onTap: () => context.push('/map'),
                    ),
                    // Card 3
                    HomeCarouselItem(
                      imagePath: 'assets/images/HomeCard3.jpg',
                      title: 'المشاريع التنموية',
                      description:
                          'تعرف على المشاريع الجارية والمستقبلية في جميع مناطق الجمهورية.',
                      buttonText: 'استعرض المشاريع',
                      onTap: () => context.push('/projects'),
                    ),
                    // Card 4
                    HomeCarouselItem(
                      imagePath: 'assets/images/HomeCard4.jpg',
                      title: 'الدعم والمساعدة',
                      description:
                          'تواصل معنا في حال وجود أي استفسار أو تحتاج مساعدة.',
                      buttonText: 'تواصل معنا',
                      onTap: () => HomeSupportModal.show(context),
                    ),
                  ],
                ),

                const SizedBox(height: 20),

                // 3. Status Counters Row (4 cards matching the mockup)
                HomeStatsRow(
                  newCount: newCount,
                  underReviewCount: underReviewCount,
                  inProgressCount: inProgressCount,
                  resolvedCount: resolvedCount,
                  onStatTap: (status) => context.push('/my-complaints'),
                ),

                const SizedBox(height: 24),

                // 4. Quick Access (2 rows: 4 cards then 3 cards)
                HomeQuickAccess(
                  onPublicComplaintsTap: () =>
                      context.push('/community-complaints'),
                  onMyComplaintsTap: () => context.push('/my-complaints'),
                  onProjectsTap: () => context.push('/projects'),
                  onMapTap: () => context.push('/map'),
                  onProfileTap: () => context.push('/profile'),
                  onNotificationsTap: () => context.push('/notifications'),
                  onSupportTap: () => HomeSupportModal.show(context),
                ),

                const SizedBox(height: 24),

                // 5. Field Worker Specialized Section (if applicable)
                if (isFieldWorker) ...[
                  _buildFieldWorkerSection(context, ref),
                  const SizedBox(height: 24),
                ],

                // 6. Latest Public Complaints Section
                HomePublicComplaintsSection(
                  complaints: communityState.complaints,
                  isLoading: communityState.isLoadingInitial,
                  errorMessage: communityState.errorMessage,
                  onViewAllTap: () => context.push('/community-complaints'),
                  onComplaintTap: (complaint) => context.push(
                    '/complaints/${complaint.id}',
                    extra: complaint,
                  ),
                  onRetry: () => ref
                      .read(communityComplaintsNotifierProvider.notifier)
                      .loadInitial(),
                ),

                const SizedBox(height: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }

  /// Top Bar: Hamburger menu on the physical left, Notifications & User Avatar on the physical right
  Widget _buildTopBar(BuildContext context, WidgetRef ref, UserEntity? user) {
    final unreadCount = ref.watch(unreadNotificationsCountProvider);
    final hasUnread = unreadCount > 0;

    return Directionality(
      textDirection: TextDirection.ltr,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          // Left: Hamburger Menu Icon
          IconButton(
            icon: const Icon(
              Icons.menu_rounded,
              color: AppColors.yemenBlack,
              size: 28,
            ),
            tooltip: 'القائمة الرئيسية',
            onPressed: () => _scaffoldKey.currentState?.openDrawer(),
          ),

          // Right: Notification Bell & User Avatar
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Notification Bell with Red Badge
              Stack(
                clipBehavior: Clip.none,
                children: [
                  IconButton(
                    icon: const Icon(
                      Icons.notifications_none_rounded,
                      color: AppColors.yemenBlack,
                      size: 26,
                    ),
                    tooltip: 'الإشعارات',
                    onPressed: () => context.push('/notifications'),
                  ),
                  if (hasUnread)
                    Positioned(
                      top: 8,
                      right: 8,
                      child: Container(
                        width: 9,
                        height: 9,
                        decoration: const BoxDecoration(
                          color: AppColors.yemenRed,
                          shape: BoxShape.circle,
                        ),
                      ),
                    ),
                ],
              ),
              const SizedBox(width: 4),

              // User Profile Avatar (First letter of name or network image)
              GestureDetector(
                onTap: () => context.push('/profile'),
                child: _buildUserAvatar(user),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildUserAvatar(UserEntity? user) {
    final name = user?.name.trim() ?? '';
    final initial = name.isNotEmpty ? name.characters.first : 'م';

    return Container(
      width: 38,
      height: 38,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        border: Border.all(color: const Color(0xFFE5E7EB), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipOval(
        child: _buildAvatarInitial(initial),
      ),
    );
  }

  Widget _buildAvatarInitial(String initial) {
    return Container(
      color: AppColors.yemenBlack,
      alignment: Alignment.center,
      child: Text(
        initial,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 17,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }

  Widget _buildFieldWorkerSection(BuildContext context, WidgetRef ref) {
    final fieldWorkState = ref.watch(fieldWorkNotifierProvider);
    final activeTasks = fieldWorkState.assignments
        .where((a) => !a.isCompleted)
        .toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'مهام الصيانة الميدانية',
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
                    color: AppColors.yemenGold,
                    borderRadius: BorderRadius.circular(2),
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
                  color: AppColors.yemenGold,
                  fontWeight: FontWeight.bold,
                  fontSize: 13,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        if (fieldWorkState.isLoading && activeTasks.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 20),
            child: Center(
              child: CircularProgressIndicator(color: AppColors.yemenGold),
            ),
          )
        else if (activeTasks.isEmpty)
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFF1F3F5)),
            ),
            child: const Row(
              children: [
                Icon(
                  Icons.check_circle_outline_rounded,
                  color: AppColors.yemenEmerald,
                  size: 24,
                ),
                SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'لا توجد مهام ميدانية جارية حالياً.',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ),
              ],
            ),
          )
        else
          Column(
            children: activeTasks.take(2).map((assignment) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 10.0),
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
}
