import '../../domain/entities/notification_entity.dart';
import '../../domain/repositories/notification_repository.dart';
import '../datasources/notification_remote_data_source.dart';

class NotificationRepositoryImpl implements NotificationRepository {
  final NotificationRemoteDataSource remoteDataSource;

  NotificationRepositoryImpl({required this.remoteDataSource});

  @override
  Future<List<NotificationEntity>> getNotifications({
    int page = 1,
    int perPage = 15,
    bool unreadOnly = false,
  }) async {
    final models = await remoteDataSource.getNotifications(
      page: page,
      perPage: perPage,
      unreadOnly: unreadOnly,
    );
    return models.map((m) => m.toEntity()).toList();
  }

  @override
  Future<NotificationEntity> markAsRead(int id) async {
    final model = await remoteDataSource.markAsRead(id);
    return model.toEntity();
  }

  @override
  Future<int> markAllAsRead() async {
    return await remoteDataSource.markAllAsRead();
  }
}
