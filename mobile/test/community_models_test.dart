import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/complaints/data/models/complaint_model.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_entity.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_like_result.dart';

void main() {
  group('Community Models & Entities Serialization Tests', () {
    test('ComplaintModel parses likes_count, is_liked_by_me, and is_public correctly', () {
      final json = {
        'id': 501,
        'title': 'حفرة عميقة في شارع حدة',
        'description': 'حفرة تعيق حركة السيارات وتتطلب تدخلاً سريعاً',
        'status': 'in_progress',
        'priority': 'high',
        'latitude': 15.3344,
        'longitude': 44.1876,
        'likes_count': 14,
        'is_liked_by_me': true,
        'is_public': true,
        'category': {
          'id': 2,
          'name': 'هبوطات وتصدعات الطرق',
        },
      };

      final model = ComplaintModel.fromJson(json);

      expect(model.id, 501);
      expect(model.likesCount, 14);
      expect(model.isLikedByMe, true);
      expect(model.isPublic, true);

      final entity = model.toEntity();
      expect(entity.id, 501);
      expect(entity.likesCount, 14);
      expect(entity.isLikedByMe, true);
      expect(entity.isPublic, true);
    });

    test('ComplaintModel fallback to safe defaults when community fields are absent or string-encoded', () {
      final json = {
        'id': '502',
        'title': 'بلاغ بدون بيانات تفاعل',
        'description': 'وصف تجريبي للبلاغ',
        'status': 'new',
        'priority': 'medium',
        'latitude': '15.3500',
        'longitude': '44.2000',
        'likes_count': '7',
        'is_liked_by_me': '1',
        'is_public': 1,
      };

      final model = ComplaintModel.fromJson(json);

      expect(model.id, 502);
      expect(model.likesCount, 7);
      expect(model.isLikedByMe, true);
      expect(model.isPublic, true);

      final minimalJson = {
        'id': 503,
        'title': 'بلاغ بالحد الأدنى',
        'description': 'وصف البلاغ',
      };

      final defaultModel = ComplaintModel.fromJson(minimalJson);
      expect(defaultModel.likesCount, 0);
      expect(defaultModel.isLikedByMe, false);
      expect(defaultModel.isPublic, false);
    });

    test('ComplaintModel toJson properly includes community fields', () {
      const model = ComplaintModel(
        id: 777,
        title: 'أعمدة إنارة معطلة',
        description: 'إنارة الشارع مقطوعة بالكامل',
        status: 'new',
        priority: 'medium',
        latitude: 15.35,
        longitude: 44.21,
        likesCount: 25,
        isLikedByMe: true,
        isPublic: true,
      );

      final json = model.toJson();

      expect(json['id'], 777);
      expect(json['likes_count'], 25);
      expect(json['is_liked_by_me'], true);
      expect(json['is_public'], true);
    });

    test('ComplaintEntity copyWith supports updating likesCount and isLikedByMe optimistically', () {
      const original = ComplaintEntity(
        id: 10,
        title: 'تكدس مياه الأمطار',
        description: 'تجمع مياه في الجولة',
        status: 'new',
        priority: 'high',
        latitude: 15.36,
        longitude: 44.19,
        likesCount: 3,
        isLikedByMe: false,
        isPublic: true,
      );

      final liked = original.copyWith(
        isLikedByMe: true,
        likesCount: original.likesCount + 1,
      );

      expect(liked.likesCount, 4);
      expect(liked.isLikedByMe, true);
      expect(liked.isPublic, true);
      expect(liked.title, original.title);

      final unliked = liked.copyWith(
        isLikedByMe: false,
        likesCount: liked.likesCount - 1,
      );

      expect(unliked.likesCount, 3);
      expect(unliked.isLikedByMe, false);
    });

    test('ComplaintLikeResult parses backend JSON correctly', () {
      final likeJson = {
        'liked': true,
        'likes_count': 18,
        'already_liked': false,
      };

      final likeResult = ComplaintLikeResult.fromJson(likeJson);
      expect(likeResult.isLiked, true);
      expect(likeResult.likesCount, 18);

      final unlikeJson = {
        'liked': false,
        'likes_count': 17,
        'unliked': true,
      };

      final unlikeResult = ComplaintLikeResult.fromJson(unlikeJson);
      expect(unlikeResult.isLiked, false);
      expect(unlikeResult.likesCount, 17);
    });
  });
}
