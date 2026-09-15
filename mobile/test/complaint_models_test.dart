import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/complaints/data/models/attachment_model.dart';
import 'package:mobile/features/complaints/data/models/category_model.dart';
import 'package:mobile/features/complaints/data/models/complaint_model.dart';
import 'package:mobile/features/complaints/data/models/field_assignment_model.dart';
import 'package:mobile/features/complaints/data/models/ministry_model.dart';
import 'package:mobile/features/complaints/data/models/timeline_model.dart';

void main() {
  group('Complaint Feature Models & Entities Tests', () {
    test('AttachmentModel.fromJson parses backend ComplaintAttachmentResource payload', () {
      final json = {
        'id': 10,
        'file_path': 'complaints/evidence_10.jpg',
        'file_url': 'http://127.0.0.1:8000/storage/complaints/evidence_10.jpg',
        'file_type': 'image/jpeg',
        'captured_latitude': 15.369445,
        'captured_longitude': 44.191006,
        'type': 'before',
        'uploader': {
          'id': 1,
          'name': 'علي محمد',
        },
        'created_at': '2026-09-15T02:00:00.000000Z',
      };

      final model = AttachmentModel.fromJson(json);

      expect(model.id, 10);
      expect(model.filePath, 'complaints/evidence_10.jpg');
      expect(model.fileUrl, 'http://127.0.0.1:8000/storage/complaints/evidence_10.jpg');
      expect(model.fileType, 'image/jpeg');
      expect(model.capturedLatitude, 15.369445);
      expect(model.capturedLongitude, 44.191006);
      expect(model.type, 'before');
      expect(model.uploaderName, 'علي محمد');
      expect(model.createdAt, isNotNull);

      final entity = model.toEntity();
      expect(entity.id, 10);
      expect(entity.isBefore, true);
      expect(entity.isAfter, false);
      expect(entity.uploaderName, 'علي محمد');

      final serialized = model.toJson();
      expect(serialized['id'], 10);
      expect(serialized['type'], 'before');
    });

    test('TimelineModel.fromJson parses backend ComplaintTimelineResource payload', () {
      final json = {
        'id': 25,
        'event_type': 'status_changed',
        'description': 'تم تغيير حالة البلاغ إلى قيد التنفيذ',
        'old_value': 'assigned',
        'new_value': 'in_progress',
        'performer': {
          'id': 3,
          'name': 'المهندس خالد',
        },
        'created_at': '2026-09-15T02:30:00.000000Z',
      };

      final model = TimelineModel.fromJson(json);

      expect(model.id, 25);
      expect(model.eventType, 'status_changed');
      expect(model.description, 'تم تغيير حالة البلاغ إلى قيد التنفيذ');
      expect(model.oldValue, 'assigned');
      expect(model.newValue, 'in_progress');
      expect(model.performerName, 'المهندس خالد');

      final entity = model.toEntity();
      expect(entity.id, 25);
      expect(entity.performerName, 'المهندس خالد');

      final serialized = model.toJson();
      expect(serialized['event_type'], 'status_changed');
    });

    test('CategoryModel.fromJson parses backend CategoryResource with cascading hierarchy', () {
      final json = {
        'id': 3,
        'parent_id': 1,
        'department_id': 2,
        'name': 'حفر الشوارع الرئيسية',
        'description': 'بلاغات الحفر في الطرق الإسفلتية',
        'level': 2,
        'department': {
          'id': 2,
          'name': 'إدارة صيانة الطرق',
        },
        'children': [
          {
            'id': 7,
            'parent_id': 3,
            'department_id': 2,
            'name': 'حفر عميقة تتجاوز 10 سم',
            'level': 3,
            'children': [],
          }
        ],
      };

      final model = CategoryModel.fromJson(json);

      expect(model.id, 3);
      expect(model.parentId, 1);
      expect(model.departmentId, 2);
      expect(model.name, 'حفر الشوارع الرئيسية');
      expect(model.level, 2);
      expect(model.departmentName, 'إدارة صيانة الطرق');
      expect(model.children.length, 1);
      expect(model.children.first.id, 7);
      expect(model.children.first.name, 'حفر عميقة تتجاوز 10 سم');

      final entity = model.toEntity();
      expect(entity.id, 3);
      expect(entity.isRoot, false);
      expect(entity.hasChildren, true);
      expect(entity.children.first.level, 3);
    });

    test('MinistryModel.fromJson parses backend MinistryResource with departments', () {
      final json = {
        'id': 1,
        'name': 'وزارة الأشغال العامة والطرق',
        'code': 'MPW',
        'logo': 'logos/mpw.png',
        'contact_email': 'info@mpw.gov.ye',
        'is_active': true,
        'departments': [
          {
            'id': 1,
            'ministry_id': 1,
            'name': 'قطاع الطرق والجسور',
            'description': 'الإشراف على صيانة الطرق',
            'is_active': true,
          }
        ],
        'created_at': '2026-09-15T01:00:00.000000Z',
      };

      final model = MinistryModel.fromJson(json);

      expect(model.id, 1);
      expect(model.name, 'وزارة الأشغال العامة والطرق');
      expect(model.code, 'MPW');
      expect(model.isActive, true);
      expect(model.departments.length, 1);
      expect(model.departments.first.name, 'قطاع الطرق والجسور');

      final entity = model.toEntity();
      expect(entity.id, 1);
      expect(entity.departments.first.ministryId, 1);
    });

    test('FieldAssignmentModel.fromJson parses backend FieldAssignmentResource payload', () {
      final json = {
        'id': 4,
        'complaint_id': 100,
        'worker': {
          'id': 8,
          'name': 'سالم الميداني',
        },
        'assigned_by': {
          'id': 2,
          'name': 'مشرف الوزارة',
        },
        'status': 'accepted',
        'started_at': '2026-09-15T03:00:00.000000Z',
        'completed_at': null,
        'notes': 'الموظف في الطريق للمعاينة',
        'created_at': '2026-09-15T02:00:00.000000Z',
      };

      final model = FieldAssignmentModel.fromJson(json);

      expect(model.id, 4);
      expect(model.complaintId, 100);
      expect(model.workerId, 8);
      expect(model.workerName, 'سالم الميداني');
      expect(model.assignedByName, 'مشرف الوزارة');
      expect(model.status, 'accepted');
      expect(model.notes, 'الموظف في الطريق للمعاينة');

      final entity = model.toEntity();
      expect(entity.isAccepted, true);
      expect(entity.isPending, false);
      expect(entity.isInProgress, false);
    });

    test('ComplaintModel.fromJson parses complete backend ComplaintResource payload', () {
      final json = {
        'id': 101,
        'title': 'هبوط أرضي في تقاطع شارع بغداد',
        'description': 'هبوط إسفلتي مفاجئ يهدد سلامة المركبات والمارة بشكل عاجل',
        'status': 'reopened',
        'priority': 'urgent',
        'latitude': 15.369445,
        'longitude': 44.191006,
        'duplicate_of_id': null,
        'citizen': {
          'id': 5,
          'name': 'ياسر العولقي',
          'phone': '777112233',
        },
        'category': {
          'id': 2,
          'name': 'هبوطات وتصدعات الطرق',
          'level': 1,
        },
        'department': {
          'id': 1,
          'name': 'قطاع صيانة الطرق',
          'ministry': {
            'id': 1,
            'name': 'وزارة الأشغال العامة',
          },
        },
        'attachments': [
          {
            'id': 1,
            'file_path': 'complaints/hole.jpg',
            'type': 'before',
          }
        ],
        'timeline': [
          {
            'id': 1,
            'event_type': 'reopened',
            'description': 'أعيد فتح البلاغ لاستمرار المشكلة',
          }
        ],
        'field_assignments': [
          {
            'id': 1,
            'complaint_id': 101,
            'status': 'pending',
          }
        ],
        'created_at': '2026-09-15T01:00:00.000000Z',
        'updated_at': '2026-09-15T03:00:00.000000Z',
      };

      final model = ComplaintModel.fromJson(json);

      expect(model.id, 101);
      expect(model.title, 'هبوط أرضي في تقاطع شارع بغداد');
      expect(model.status, 'reopened');
      expect(model.priority, 'urgent');
      expect(model.latitude, 15.369445);
      expect(model.longitude, 44.191006);
      expect(model.citizenName, 'ياسر العولقي');
      expect(model.citizenPhone, '777112233');
      expect(model.category?.name, 'هبوطات وتصدعات الطرق');
      expect(model.departmentName, 'قطاع صيانة الطرق');
      expect(model.ministryName, 'وزارة الأشغال العامة');
      expect(model.attachments.length, 1);
      expect(model.timeline.length, 1);
      expect(model.fieldAssignments.length, 1);

      final entity = model.toEntity();
      expect(entity.id, 101);
      expect(entity.complaintNumber, 'CMP-000101');
      expect(entity.statusArabic, 'أُعيد فتحها');
      expect(entity.priorityArabic, 'طارئ');
      expect(entity.attachments.first.isBefore, true);
    });
  });
}
