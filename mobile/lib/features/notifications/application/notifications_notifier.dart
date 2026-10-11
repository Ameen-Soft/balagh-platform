import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../domain/repositories/notification_repository.dart';
import 'notification_providers.dart';
import 'notifications_state.dart';

class NotificationsNotifier extends Notifier<NotificationsState> {
  late final NotificationRepository _repository;

  @override
  NotificationsState build() {
    _repository = ref.watch(notificationRepositoryProvider);
    Future.microtask(() => loadNotifications());
    return const NotificationsState();
  }

  Future<void> loadNotifications({bool refresh = false}) async {
    if (state.isLoading && !refresh) return;

    state = state.copyWith(
      status: NotificationsStatus.loading,
      currentPage: 1,
      hasReachedMax: false,
      errorMessage: null,
    );

    try {
      final items = await _repository.getNotifications(
        page: 1,
        perPage: 15,
        unreadOnly: state.filterUnreadOnly,
      );

      state = state.copyWith(
        status: NotificationsStatus.loaded,
        notifications: items,
        currentPage: 1,
        hasReachedMax: items.length < 15,
        errorMessage: null,
      );
    } catch (e) {
      state = state.copyWith(
        status: NotificationsStatus.error,
        errorMessage: e.toString().replaceFirst('Exception: ', ''),
      );
    }
  }

  Future<void> refresh() async {
    await loadNotifications(refresh: true);
  }

  Future<void> loadMore() async {
    if (state.hasReachedMax || state.isLoadingMore || state.isLoading) return;

    state = state.copyWith(isLoadingMore: true);
    final nextPage = state.currentPage + 1;

    try {
      final newItems = await _repository.getNotifications(
        page: nextPage,
        perPage: 15,
        unreadOnly: state.filterUnreadOnly,
      );

      state = state.copyWith(
        notifications: [...state.notifications, ...newItems],
        currentPage: nextPage,
        hasReachedMax: newItems.length < 15,
        isLoadingMore: false,
      );
    } catch (_) {
      state = state.copyWith(isLoadingMore: false);
    }
  }

  Future<void> setFilterUnreadOnly(bool unreadOnly) async {
    if (state.filterUnreadOnly == unreadOnly) return;
    state = state.copyWith(filterUnreadOnly: unreadOnly);
    await loadNotifications(refresh: true);
  }

  Future<void> markAsRead(int notificationId) async {
    final originalNotifications = state.notifications;
    
    // Optimistic update
    final updatedList = originalNotifications.map((item) {
      if (item.id == notificationId) {
        return item.copyWith(isRead: true);
      }
      return item;
    }).toList();

    state = state.copyWith(notifications: updatedList);

    try {
      await _repository.markAsRead(notificationId);
    } catch (e) {
      // Revert if API call fails
      state = state.copyWith(notifications: originalNotifications);
    }
  }

  Future<void> markAllAsRead() async {
    final originalNotifications = state.notifications;

    // Optimistic update
    final updatedList = originalNotifications.map((item) {
      return item.copyWith(isRead: true);
    }).toList();

    state = state.copyWith(notifications: updatedList);

    try {
      await _repository.markAllAsRead();
    } catch (e) {
      // Revert if API call fails
      state = state.copyWith(notifications: originalNotifications);
    }
  }
}
