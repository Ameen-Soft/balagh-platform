import '../entities/notification_entity.dart';

abstract class NotificationRepository {
  Future<List<NotificationEntity>> getNotifications({
    int page = 1,
    int perPage = 15,
    bool unreadOnly = false,
  });

  Future<NotificationEntity> markAsRead(int id);

  Future<int> markAllAsRead();
}
