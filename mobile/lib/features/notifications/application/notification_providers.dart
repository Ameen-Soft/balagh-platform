import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../auth/application/providers.dart';
import '../data/datasources/notification_remote_data_source.dart';
import '../data/repositories/notification_repository_impl.dart';
import '../domain/repositories/notification_repository.dart';
import 'notifications_notifier.dart';
import 'notifications_state.dart';

/// Remote data source provider for notifications
final notificationRemoteDataSourceProvider =
    Provider<NotificationRemoteDataSource>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return NotificationRemoteDataSourceImpl(apiClient: apiClient);
});

/// Repository provider for notifications
final notificationRepositoryProvider =
    Provider<NotificationRepository>((ref) {
  final remoteDataSource = ref.watch(notificationRemoteDataSourceProvider);
  return NotificationRepositoryImpl(remoteDataSource: remoteDataSource);
});

/// Notifications list and management notifier provider
final notificationsNotifierProvider =
    NotifierProvider<NotificationsNotifier, NotificationsState>(
  NotificationsNotifier.new,
);

/// Computed provider for unread notifications count
final unreadNotificationsCountProvider = Provider<int>((ref) {
  final state = ref.watch(notificationsNotifierProvider);
  return state.unreadCount;
});
