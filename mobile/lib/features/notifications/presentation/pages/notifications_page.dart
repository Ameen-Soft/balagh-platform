import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/constants/app_colors.dart';
import '../../../../core/widgets/app_empty_state.dart';
import '../../../../core/widgets/app_error_state.dart';
import '../../application/notification_providers.dart';
import '../../application/notifications_notifier.dart';
import '../../application/notifications_state.dart';
import '../widgets/notification_card.dart';

class NotificationsPage extends ConsumerStatefulWidget {
  const NotificationsPage({super.key});

  @override
  ConsumerState<NotificationsPage> createState() => _NotificationsPageState();
}

class _NotificationsPageState extends ConsumerState<NotificationsPage> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.removeListener(_onScroll);
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      ref.read(notificationsNotifierProvider.notifier).loadMore();
    }
  }

  void _showMarkAllAsReadConfirmation(BuildContext context) {
    showDialog(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'تأشير الكل كمقروء',
          textAlign: TextAlign.right,
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        content: const Text(
          'هل تريد تأشير جميع الإشعارات كمقروءة؟',
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
              ref.read(notificationsNotifierProvider.notifier).markAllAsRead();
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('تم تأشير جميع الإشعارات كمقروءة بنجاح'),
                  backgroundColor: AppColors.deepBlack,
                  behavior: SnackBarBehavior.floating,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.yemenRed,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
            child: const Text('تأكيد'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(notificationsNotifierProvider);
    final notifier = ref.read(notificationsNotifierProvider.notifier);

    ref.listen<NotificationsState>(notificationsNotifierProvider,
        (previous, next) {
      if (next.hasError &&
          next.notifications.isNotEmpty &&
          previous?.hasError != true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              next.errorMessage ??
                  'تعذر تحديث الإشعارات، تم الاحتفاظ بالبيانات السابقة.',
            ),
            backgroundColor: AppColors.deepBlack,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        );
      }
    });

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      body: Column(
        children: [
          // 1. Dark Header with Overlapping Filter Capsules
          Stack(
            clipBehavior: Clip.none,
            children: [
              // Curved Dark Banner
              Container(
                width: double.infinity,
                padding: EdgeInsets.only(
                  top: MediaQuery.of(context).padding.top + 10,
                  bottom: 42,
                  left: 16,
                  right: 16,
                ),
                decoration: const BoxDecoration(
                  color: AppColors.yemenBlack,
                  borderRadius: BorderRadius.only(
                    bottomLeft: Radius.circular(28),
                    bottomRight: Radius.circular(28),
                  ),
                  image: DecorationImage(
                    image: AssetImage('assets/images/profilepageImage .png'),
                    fit: BoxFit.cover,
                    opacity: 0.85,
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Back / Navigation Button
                    IconButton(
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
                    const Text(
                      'مركز الإشعارات',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w800,
                        fontSize: 19,
                      ),
                    ),
                    // Mark All as Read Icon Button
                    if (state.unreadCount > 0)
                      IconButton(
                        tooltip: 'تأشير الكل كمقروء',
                        onPressed: () =>
                            _showMarkAllAsReadConfirmation(context),
                        icon: const Icon(
                          Icons.done_all_rounded,
                          color: Colors.white,
                          size: 22,
                        ),
                      )
                    else
                      const SizedBox(width: 48),
                  ],
                ),
              ),

              // Overlapping Segmented Filter Pills
              Positioned(
                bottom: -22,
                left: 16,
                right: 16,
                child: Row(
                  children: [
                    Expanded(
                      child: _buildFilterPill(
                        label: 'جميع الإشعارات',
                        icon: Icons.notifications_active_outlined,
                        isSelected: !state.filterUnreadOnly,
                        badgeCount:
                            state.unreadCount > 0 ? state.unreadCount : null,
                        showDot: false,
                        onTap: () => notifier.setFilterUnreadOnly(false),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _buildFilterPill(
                        label: 'غير المقروءة',
                        icon: Icons.visibility_outlined,
                        isSelected: state.filterUnreadOnly,
                        badgeCount: null,
                        showDot: state.unreadCount > 0,
                        onTap: () => notifier.setFilterUnreadOnly(true),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 30),

          // 2. Notifications List Content
          Expanded(
            child: RefreshIndicator(
              color: AppColors.yemenRed,
              onRefresh: () => notifier.refresh(),
              child: _buildContent(state, notifier),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterPill({
    required String label,
    required IconData icon,
    required bool isSelected,
    int? badgeCount,
    bool showDot = false,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 220),
        height: 46,
        padding: const EdgeInsets.symmetric(horizontal: 14),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.yemenBlack : Colors.white,
          borderRadius: BorderRadius.circular(24),
          border: Border.all(
            color: isSelected
                ? Colors.white.withValues(alpha: 0.15)
                : AppColors.borderSubtle,
            width: 1.2,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: isSelected ? 0.14 : 0.05),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 18,
              color: isSelected ? Colors.white : AppColors.yemenBlack,
            ),
            const SizedBox(width: 8),
            Flexible(
              child: Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: isSelected ? Colors.white : AppColors.yemenBlack,
                  fontWeight: FontWeight.w800,
                  fontSize: 13,
                ),
              ),
            ),
            if (badgeCount != null) ...[
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.yemenRed,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  badgeCount.toString(),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ] else if (showDot) ...[
              const SizedBox(width: 6),
              Container(
                width: 7,
                height: 7,
                decoration: const BoxDecoration(
                  color: AppColors.yemenRed,
                  shape: BoxShape.circle,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildContent(
      NotificationsState state, NotificationsNotifier notifier) {
    if (state.isLoading && state.notifications.isEmpty) {
      return const Center(
        child: CircularProgressIndicator(
          color: AppColors.yemenRed,
        ),
      );
    }

    if (state.hasError && state.notifications.isEmpty) {
      final isNetwork = state.errorMessage?.contains('اتصال') == true ||
          state.errorMessage?.contains('الإنترنت') == true ||
          state.errorMessage?.contains('شبكة') == true;
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.15),
          AppErrorState(
            message: state.errorMessage,
            isNetworkError: isNetwork,
            onRetry: () => notifier.refresh(),
          ),
        ],
      );
    }

    if (state.notifications.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.15),
          AppEmptyState(
            icon: Icons.notifications_none_rounded,
            title: state.filterUnreadOnly
                ? 'لا توجد إشعارات غير مقروءة'
                : 'لا توجد إشعارات حالياً',
            description: state.filterUnreadOnly
                ? 'لقد اطلعت على كافة الإشعارات والتحديثات الواردة.'
                : 'ستصلك هنا إشعارات فورية عن حالة بلاغاتك ومستجدات المنصة.',
          ),
        ],
      );
    }

    return ListView.builder(
      controller: _scrollController,
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 6, 16, 24),
      itemCount: state.notifications.length + (state.isLoadingMore ? 1 : 0),
      itemBuilder: (context, index) {
        if (index == state.notifications.length) {
          return const Padding(
            padding: EdgeInsets.symmetric(vertical: 20.0),
            child: Center(
              child: SizedBox(
                width: 24,
                height: 24,
                child: CircularProgressIndicator(
                  strokeWidth: 2.5,
                  color: AppColors.yemenRed,
                ),
              ),
            ),
          );
        }

        final item = state.notifications[index];
        return NotificationCard(
          key: ValueKey('notification_${item.id}'),
          notification: item,
          onMarkAsRead: () => notifier.markAsRead(item.id),
        );
      },
    );
  }
}