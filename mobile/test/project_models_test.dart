import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/projects/data/models/project_contribution_model.dart';
import 'package:mobile/features/projects/data/models/project_model.dart';
import 'package:mobile/features/projects/data/models/project_phase_model.dart';

void main() {
  group('Projects Feature Models & Entities Tests', () {
    test('ProjectPhaseModel.fromJson and toEntity handle complete backend payload', () {
      final json = {
        'id': 1,
        'project_id': 10,
        'name': 'المرحلة الأولى: المسح الهندسي والتخطيط',
        'description': 'إجراء المسوحات الطبوغرافية للموقع وتحديد المسار',
        'completion_percentage': 75,
        'start_date': '2026-09-01',
        'end_date': '2026-09-30',
        'status': 'in_progress',
      };

      final model = ProjectPhaseModel.fromJson(json);

      expect(model.id, 1);
      expect(model.projectId, 10);
      expect(model.name, 'المرحلة الأولى: المسح الهندسي والتخطيط');
      expect(model.description, 'إجراء المسوحات الطبوغرافية للموقع وتحديد المسار');
      expect(model.completionPercentage, 75);
      expect(model.status, 'in_progress');
      expect(model.startDate, DateTime(2026, 9, 1));
      expect(model.endDate, DateTime(2026, 9, 30));

      final entity = model.toEntity();
      expect(entity.id, 1);
      expect(entity.projectId, 10);
      expect(entity.isInProgress, true);
      expect(entity.isCompleted, false);

      final serialized = model.toJson();
      expect(serialized['id'], 1);
      expect(serialized['project_id'], 10);
      expect(serialized['completion_percentage'], 75);
      expect(serialized['start_date'], '2026-09-01');
      expect(serialized['status'], 'in_progress');
    });

    test('ProjectContributionModel.fromJson and toEntity handle complete backend payload', () {
      final json = {
        'id': 5,
        'project_id': 10,
        'amount': 50000.0,
        'contributor': {
          'id': 7,
          'name': 'فاعل خير',
        },
        'created_at': '2026-09-15T02:00:00.000000Z',
      };

      final model = ProjectContributionModel.fromJson(json);

      expect(model.id, 5);
      expect(model.projectId, 10);
      expect(model.amount, 50000.0);
      expect(model.contributorName, 'فاعل خير');
      expect(model.createdAt, isNotNull);

      final entity = model.toEntity();
      expect(entity.id, 5);
      expect(entity.projectId, 10);
      expect(entity.amount, 50000.0);
      expect(entity.contributorName, 'فاعل خير');

      final serialized = model.toJson();
      expect(serialized['id'], 5);
      expect(serialized['amount'], 50000.0);
      expect(serialized['contributor']['name'], 'فاعل خير');
    });

    test('ProjectModel.fromJson and toEntity handle complete backend ProjectResource payload', () {
      final json = {
        'id': 10,
        'title': 'مشروع ترميم عقبة بيت بوس وتوسعة الطريق',
        'description': 'مشروع متكامل لإعادة سفلتة وتوسيع الطريق الحيوي لربط الأحياء الجنوبية',
        'status': 'in_progress',
        'target_amount': 2500000.0,
        'current_amount': 1875000.0,
        'progress_percentage': 75.0,
        'latitude': 15.312345,
        'longitude': 44.204567,
        'department': {
          'id': 1,
          'name': 'إدارة المشاريع الإستراتيجية',
          'ministry': {
            'id': 1,
            'name': 'وزارة الأشغال العامة والطرق',
          },
        },
        'created_by': {
          'id': 2,
          'name': 'المهندس المشرف',
        },
        'phases': [
          {
            'id': 1,
            'project_id': 10,
            'name': 'المرحلة 1: إزالة الأسفلت التالف',
            'completion_percentage': 100,
            'status': 'completed',
          },
          {
            'id': 2,
            'project_id': 10,
            'name': 'المرحلة 2: الرصف والإنارة',
            'completion_percentage': 50,
            'status': 'in_progress',
          }
        ],
        'contributions': [
          {
            'id': 1,
            'project_id': 10,
            'amount': 100000.0,
            'contributor': {
              'id': 5,
              'name': 'مجموعة هائل سعيد',
            },
            'created_at': '2026-09-10T10:00:00.000000Z',
          }
        ],
        'created_at': '2026-09-01T08:00:00.000000Z',
        'updated_at': '2026-09-15T02:00:00.000000Z',
      };

      final model = ProjectModel.fromJson(json);

      expect(model.id, 10);
      expect(model.title, 'مشروع ترميم عقبة بيت بوس وتوسعة الطريق');
      expect(model.status, 'in_progress');
      expect(model.targetAmount, 2500000.0);
      expect(model.currentAmount, 1875000.0);
      expect(model.progressPercentage, 75.0);
      expect(model.latitude, 15.312345);
      expect(model.longitude, 44.204567);
      expect(model.departmentName, 'إدارة المشاريع الإستراتيجية');
      expect(model.ministryName, 'وزارة الأشغال العامة والطرق');
      expect(model.createdByName, 'المهندس المشرف');
      expect(model.phases.length, 2);
      expect(model.contributions.length, 1);

      final entity = model.toEntity();
      expect(entity.id, 10);
      expect(entity.projectNumber, 'PRJ-000010');
      expect(entity.statusArabic, 'قيد التنفيذ');
      expect(entity.isFullyFunded, false);
      expect(entity.phases.first.isCompleted, true);
      expect(entity.phases.last.isInProgress, true);
      expect(entity.contributions.first.amount, 100000.0);

      final serialized = model.toJson();
      expect(serialized['id'], 10);
      expect(serialized['title'], 'مشروع ترميم عقبة بيت بوس وتوسعة الطريق');
      expect(serialized['target_amount'], 2500000.0);
      expect(serialized['phases'], isA<List>());
      expect((serialized['phases'] as List).length, 2);
    });

    test('ProjectModel handles nullable and missing values gracefully', () {
      final json = {
        'id': '20',
        'title': 'مشروع جديد مبسط',
        'description': 'بدون تفاصيل إضافية',
        'target_amount': '100000',
        'current_amount': null,
        'progress_percentage': null,
      };

      final model = ProjectModel.fromJson(json);

      expect(model.id, 20);
      expect(model.title, 'مشروع جديد مبسط');
      expect(model.targetAmount, 100000.0);
      expect(model.currentAmount, 0.0);
      expect(model.progressPercentage, 0.0);
      expect(model.latitude, isNull);
      expect(model.longitude, isNull);
      expect(model.phases, isEmpty);
      expect(model.contributions, isEmpty);
    });
  });
}
