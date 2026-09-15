import 'package:mobile/core/network/api_client.dart';
import 'package:mobile/core/network/api_endpoints.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import '../models/notification_model.dart';

abstract class NotificationRemoteDataSource {
  Future<List<NotificationModel>> getNotifications({
    int page = 1,
    int perPage = 15,
    bool unreadOnly = false,
  });

  Future<NotificationModel> markAsRead(int id);

  Future<int> markAllAsRead();
}

class NotificationRemoteDataSourceImpl implements NotificationRemoteDataSource {
  final ApiClient apiClient;

  NotificationRemoteDataSourceImpl({required this.apiClient});

  @override
  Future<List<NotificationModel>> getNotifications({
    int page = 1,
    int perPage = 15,
    bool unreadOnly = false,
  }) async {
    final Map<String, dynamic> queryParams = {
      'page': page,
      'per_page': perPage,
      if (unreadOnly) 'unread_only': true,
    };

    final response = await apiClient.get(
      ApiEndpoints.notifications,
      queryParameters: queryParams,
    );

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is List) {
      return (data['data'] as List)
          .whereType<Map<String, dynamic>>()
          .map((item) => NotificationModel.fromJson(item))
          .toList();
    }

    return [];
  }

  @override
  Future<NotificationModel> markAsRead(int id) async {
    final response = await apiClient.patch(ApiEndpoints.markNotificationAsRead(id));
    final data = response.data;

    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return NotificationModel.fromJson(data['data'] as Map<String, dynamic>);
    }

    throw const ServerException('فشل تأشير الإشعار كمقروء.');
  }

  @override
  Future<int> markAllAsRead() async {
    final response = await apiClient.post(ApiEndpoints.markAllNotificationsAsRead);
    final data = response.data;

    if (data is Map<String, dynamic> && data['data'] is Map<String, dynamic>) {
      return data['data']['updated_count'] is int
          ? data['data']['updated_count'] as int
          : 0;
    }

    return 0;
  }
}
