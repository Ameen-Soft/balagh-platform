import '../domain/entities/notification_entity.dart';

enum NotificationsStatus { initial, loading, loaded, error }

class NotificationsState {
  final NotificationsStatus status;
  final List<NotificationEntity> notifications;
  final int currentPage;
  final bool hasReachedMax;
  final bool isLoadingMore;
  final bool filterUnreadOnly;
  final String? errorMessage;

  const NotificationsState({
    this.status = NotificationsStatus.initial,
    this.notifications = const [],
    this.currentPage = 1,
    this.hasReachedMax = false,
    this.isLoadingMore = false,
    this.filterUnreadOnly = false,
    this.errorMessage,
  });

  bool get isLoading => status == NotificationsStatus.loading;
  bool get isLoaded => status == NotificationsStatus.loaded;
  bool get hasError => status == NotificationsStatus.error;

  /// Counts unread notifications currently loaded
  int get unreadCount => notifications.where((n) => !n.isRead).length;

  NotificationsState copyWith({
    NotificationsStatus? status,
    List<NotificationEntity>? notifications,
    int? currentPage,
    bool? hasReachedMax,
    bool? isLoadingMore,
    bool? filterUnreadOnly,
    String? errorMessage,
  }) {
    return NotificationsState(
      status: status ?? this.status,
      notifications: notifications ?? this.notifications,
      currentPage: currentPage ?? this.currentPage,
      hasReachedMax: hasReachedMax ?? this.hasReachedMax,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
      filterUnreadOnly: filterUnreadOnly ?? this.filterUnreadOnly,
      errorMessage: errorMessage,
    );
  }
}
