import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_entity.dart';
import 'package:mobile/features/complaints/presentation/widgets/community_complaint_card.dart';

void main() {
  const baseComplaint = ComplaintEntity(
    id: 101,
    title: 'انقطاع المياه عن حي الوحدة',
    description: 'تسرب في خط المياه الرئيسي يؤدي إلى انقطاع الخدمة بالكامل منذ يومين.',
    status: 'in_progress',
    priority: 'high',
    latitude: 15.35,
    longitude: 44.20,
    likesCount: 5,
    isLikedByMe: false,
  );

  group('CommunityComplaintCard UI Tests', () {
    testWidgets('displays favorite_border_rounded when isLikedByMe is false', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(
            body: CommunityComplaintCard(
              complaint: baseComplaint,
            ),
          ),
        ),
      );

      // Verify border heart icon is shown
      expect(find.byIcon(Icons.favorite_border_rounded), findsOneWidget);
      expect(find.byIcon(Icons.favorite_rounded), findsNothing);

      // Verify likes count is displayed
      expect(find.text('5'), findsOneWidget);
    });

    testWidgets('displays favorite_rounded when isLikedByMe is true', (tester) async {
      final likedComplaint = baseComplaint.copyWith(
        isLikedByMe: true,
        likesCount: 6,
      );

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: CommunityComplaintCard(
              complaint: likedComplaint,
            ),
          ),
        ),
      );

      // Verify filled heart icon is shown
      expect(find.byIcon(Icons.favorite_rounded), findsOneWidget);
      expect(find.byIcon(Icons.favorite_border_rounded), findsNothing);

      // Verify likes count is displayed
      expect(find.text('6'), findsOneWidget);
    });

    testWidgets('does NOT render chat_bubble_outline_rounded icon anywhere', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(
            body: CommunityComplaintCard(
              complaint: baseComplaint,
            ),
          ),
        ),
      );

      // Verify chat_bubble icon is completely removed
      expect(find.byIcon(Icons.chat_bubble_outline_rounded), findsNothing);
      expect(find.byIcon(Icons.chat_bubble_rounded), findsNothing);
      expect(find.byIcon(Icons.chat_bubble), findsNothing);
    });

    testWidgets('tapping like icon invokes onLikePressed callback', (tester) async {
      bool likePressed = false;

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: CommunityComplaintCard(
              complaint: baseComplaint,
              onLikePressed: () {
                likePressed = true;
              },
            ),
          ),
        ),
      );

      await tester.tap(find.byIcon(Icons.favorite_border_rounded));
      await tester.pump();

      expect(likePressed, true);
    });

    testWidgets('tapping card invokes onTap callback', (tester) async {
      bool cardTapped = false;

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: CommunityComplaintCard(
              complaint: baseComplaint,
              onTap: () {
                cardTapped = true;
              },
            ),
          ),
        ),
      );

      await tester.tap(find.text('انقطاع المياه عن حي الوحدة'));
      await tester.pump();

      expect(cardTapped, true);
    });
  });
}
