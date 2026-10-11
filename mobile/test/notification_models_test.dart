import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/notifications/data/models/notification_model.dart';
import 'package:mobile/features/notifications/domain/entities/notification_entity.dart';

void main() {
  group('Notifications Feature Models & Entities Tests', () {
    test('NotificationModel.fromJson and toEntity handle complete backend NotificationResource payload', () {
      final json = {
        'id': 12,
        'title': 'تحديث بخصوص بلاغك',
        'body': 'تم قبول التكليف الميداني لبلاغ هبوط شارع بغداد من قبل الباحث سالم',
        'type': 'complaint_status_updated',
        'data': {
          'complaint_id': 101,
          'status': 'assigned',
          'priority': 'urgent',
        },
        'is_read': false,
        'created_at': '2026-09-15T03:05:00.000000Z',
      };

      final model = NotificationModel.fromJson(json);

      expect(model.id, 12);
      expect(model.title, 'تحديث بخصوص بلاغك');
      expect(model.body, 'تم قبول التكليف الميداني لبلاغ هبوط شارع بغداد من قبل الباحث سالم');
      expect(model.type, 'complaint_status_updated');
      expect(model.data, isNotNull);
      expect(model.data!['complaint_id'], 101);
      expect(model.isRead, false);
      expect(model.createdAt, isNotNull);

      final entity = model.toEntity();
      expect(entity.id, 12);
      expect(entity.title, 'تحديث بخصوص بلاغك');
      expect(entity.isRead, false);
      expect(entity.complaintId, 101);

      final serialized = model.toJson();
      expect(serialized['id'], 12);
      expect(serialized['title'], 'تحديث بخصوص بلاغك');
      expect(serialized['is_read'], false);
      expect(serialized['type'], 'complaint_status_updated');
      expect(serialized['data']['complaint_id'], 101);
    });

    test('NotificationModel handles numeric is_read and null data gracefully', () {
      final json = {
        'id': '15',
        'title': 'إشعار عام مقروء',
        'body': 'نظام بلّغ يرحب بكم',
        'type': 'general',
        'data': null,
        'is_read': 1,
        'created_at': null,
      };

      final model = NotificationModel.fromJson(json);

      expect(model.id, 15);
      expect(model.title, 'إشعار عام مقروء');
      expect(model.type, 'general');
      expect(model.data, isNull);
      expect(model.isRead, true);
      expect(model.createdAt, isNull);

      final entity = model.toEntity();
      expect(entity.id, 15);
      expect(entity.isRead, true);
      expect(entity.complaintId, isNull);
      expect(entity.projectId, isNull);
      expect(entity.assignmentId, isNull);
    });

    test('NotificationEntity copyWith, projectId and assignmentId work properly', () {
      final entity = NotificationEntity(
        id: 1,
        title: 'مهمة جديدة ومساهمة',
        body: 'تفاصيل الإشعار',
        type: 'task_assigned',
        isRead: false,
        data: {
          'complaint_id': 20,
          'project_id': 35,
          'assignment_id': 42,
        },
      );

      expect(entity.complaintId, 20);
      expect(entity.projectId, 35);
      expect(entity.assignmentId, 42);

      final updated = entity.copyWith(isRead: true);
      expect(updated.isRead, true);
      expect(updated.id, 1);
      expect(updated.title, 'مهمة جديدة ومساهمة');
      expect(updated.complaintId, 20);
    });
  });
}

