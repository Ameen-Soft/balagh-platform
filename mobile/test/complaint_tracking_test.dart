import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:mobile/core/network/api_endpoints.dart';
import 'package:mobile/core/network/media_url_resolver.dart';
import 'package:mobile/core/utils/app_date_formatter.dart';
import 'package:mobile/features/complaints/application/complaints_providers.dart';
import 'package:mobile/features/complaints/data/models/paginated_complaints_model.dart';
import 'package:mobile/features/complaints/data/models/pagination_meta_model.dart';
import 'package:mobile/features/complaints/domain/entities/category_entity.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_entity.dart';
import 'package:mobile/features/complaints/domain/entities/ministry_entity.dart';
import 'package:mobile/features/complaints/domain/entities/paginated_complaints_result.dart';
import 'package:mobile/features/complaints/domain/entities/timeline_entity.dart';
import 'package:mobile/features/complaints/domain/repositories/complaint_repository.dart';
import 'package:mobile/features/complaints/presentation/widgets/complaint_card.dart';
import 'package:mobile/features/complaints/presentation/widgets/status_badge.dart';
import 'package:mobile/features/complaints/presentation/widgets/timeline_widget.dart';

class MockComplaintRepository implements ComplaintRepository {
  List<ComplaintEntity> complaintsToReturn = [];
  bool shouldThrow = false;
  int getPaginatedCalls = 0;
  int? lastRequestedPage;
  String? lastRequestedStatus;

  @override
  Future<List<ComplaintEntity>> getComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  }) async {
    if (shouldThrow) throw Exception('Network error');
    return complaintsToReturn;
  }

  @override
  Future<PaginatedComplaintsResult> getPaginatedComplaints({
    int page = 1,
    String? status,
    int? categoryId,
    int? departmentId,
    String? search,
  }) async {
    getPaginatedCalls++;
    lastRequestedPage = page;
    lastRequestedStatus = status;

    if (shouldThrow) throw Exception('Failed to fetch paginated complaints');

    final hasMore = page < 2;
    return PaginatedComplaintsResult(
      complaints: complaintsToReturn,
      currentPage: page,
      lastPage: 2,
      total: complaintsToReturn.length * 2,
      hasMore: hasMore,
    );
  }

  @override
  Future<ComplaintEntity> getComplaintDetails(int id) async {
    if (shouldThrow) throw Exception('Failed to fetch complaint details');
    return complaintsToReturn.firstWhere(
      (c) => c.id == id,
      orElse: () => ComplaintEntity(
        id: id,
        title: 'بلاغ تفصيلي #$id',
        description: 'تفاصيل البلاغ',
        status: 'new',
        priority: 'medium',
        latitude: 15.35,
        longitude: 44.20,
      ),
    );
  }

  @override
  Future<ComplaintEntity> createComplaint({
    required String title,
    required String description,
    required int categoryId,
    required double latitude,
    required double longitude,
    String? priority,
    List<String>? attachmentPaths,
  }) async {
    throw UnimplementedError();
  }

  @override
  Future<List<MinistryEntity>> getMinistries() async => [];

  @override
  Future<List<CategoryEntity>> getCategories({
    int? ministryId,
    int? departmentId,
    int? parentId,
    int? level,
  }) async => [];
}

void main() {
  setUpAll(() async {
    await initializeDateFormatting('ar', null);
  });

  group('MediaUrlResolver Tests', () {
    test('resolves null and empty strings gracefully', () {
      expect(MediaUrlResolver.resolve(null), isNull);
      expect(MediaUrlResolver.resolve(''), isNull);
      expect(MediaUrlResolver.resolve('   '), isNull);
    });

    test('prepends server IP and port to relative storage paths', () {
      final resolved = MediaUrlResolver.resolve('/storage/complaints/pic.jpg');
      expect(
        resolved,
        'http://${ApiEndpoints.serverIp}:${ApiEndpoints.serverPort}/storage/complaints/pic.jpg',
      );

      final resolvedNoSlash = MediaUrlResolver.resolve(
        'storage/complaints/pic2.jpg',
      );
      expect(
        resolvedNoSlash,
        'http://${ApiEndpoints.serverIp}:${ApiEndpoints.serverPort}/storage/complaints/pic2.jpg',
      );
    });

    test('replaces localhost:8000 and 127.0.0.1:8000 with real server IP on mobile', () {
      final resolvedLocalhost = MediaUrlResolver.resolve(
        'http://localhost:8000/storage/complaints/img.png',
      );
      expect(
        resolvedLocalhost,
        'http://${ApiEndpoints.serverIp}:${ApiEndpoints.serverPort}/storage/complaints/img.png',
      );

      final resolvedLoopback = MediaUrlResolver.resolve(
        'http://127.0.0.1:8000/storage/complaints/img.png',
      );
      expect(
        resolvedLoopback,
        'http://${ApiEndpoints.serverIp}:${ApiEndpoints.serverPort}/storage/complaints/img.png',
      );
    });
  });

  group('AppDateFormatter Tests', () {
    test('formatDate formats correctly and handles null', () {
      expect(AppDateFormatter.formatDate(null), '');
      final d = DateTime(2026, 9, 16);
      expect(AppDateFormatter.formatDate(d), '2026/09/16');
    });

    test('formatDateTime formats date and time cleanly', () {
      expect(AppDateFormatter.formatDateTime(null), '');
      final dt = DateTime(2026, 9, 16, 14, 30);
      expect(AppDateFormatter.formatDateTime(dt), contains('2026/09/16'));
      expect(AppDateFormatter.formatDateTime(dt), contains('30'));
    });
  });

  group('Pagination Meta & PaginatedComplaintsModel Tests', () {
    test(
      'PaginationMetaModel correctly parses Laravel meta pagination JSON',
      () {
        final json = {
          'current_page': 2,
          'last_page': 5,
          'per_page': 15,
          'total': 75,
          'has_more': true,
        };

        final meta = PaginationMetaModel.fromJson(json);
        expect(meta.currentPage, 2);
        expect(meta.lastPage, 5);
        expect(meta.perPage, 15);
        expect(meta.total, 75);
        expect(meta.hasMore, true);
      },
    );

    test('PaginatedComplaintsModel parses data items and meta', () {
      final json = {
        'data': [
          {
            'id': 1,
            'title': 'حفرة في الشارع العام',
            'description': 'حفرة عميقة',
            'status': 'new',
            'priority': 'high',
            'latitude': 15.35,
            'longitude': 44.20,
          },
        ],
        'meta': {
          'current_page': 1,
          'last_page': 1,
          'per_page': 15,
          'total': 1,
          'has_more': false,
        },
      };

      final model = PaginatedComplaintsModel.fromJson(json);
      expect(model.items.length, 1);
      expect(model.items.first.title, 'حفرة في الشارع العام');
      expect(model.meta.hasMore, false);
      expect(model.meta.total, 1);
    });
  });

  group('StatusBadge Widget Tests - Strictly 8 Authorized Statuses', () {
    final statuses = [
      {'status': 'new', 'expectedText': 'جديد وارد'},
      {'status': 'under_review', 'expectedText': 'قيد المراجعة'},
      {'status': 'assigned', 'expectedText': 'مسند للميدان'},
      {'status': 'in_progress', 'expectedText': 'قيد التنفيذ'},
      {'status': 'resolved', 'expectedText': 'تم الإنجاز'},
      {'status': 'closed', 'expectedText': 'مغلق'},
      {'status': 'rejected', 'expectedText': 'مرفوض'},
      {'status': 'reopened', 'expectedText': 'أُعيد فتحها'},
    ];

    for (final s in statuses) {
      testWidgets('renders correct Arabic label for status "${s['status']}"', (
        tester,
      ) async {
        await tester.pumpWidget(
          MaterialApp(
            home: Scaffold(body: StatusBadge(status: s['status']!)),
          ),
        );

        expect(find.text(s['expectedText']!), findsOneWidget);
      });
    }
  });

  group('ComplaintCard Widget Tests', () {
    testWidgets(
      'displays title, complaint number, priority, and responds to tap',
      (tester) async {
        bool tapped = false;
        final testComplaint = ComplaintEntity(
          id: 42,
          title: 'تسرب مياه في الحي',
          description: 'وصف التسرب بالتفصيل',
          status: 'in_progress',
          priority: 'urgent',
          latitude: 15.369445,
          longitude: 44.191006,
          ministryName: 'وزارة المياه والبيئة',
          category: const CategoryEntity(
            id: 5,
            name: 'كسر في أنبوب رئيسي',
            level: 2,
          ),
          createdAt: DateTime(2026, 9, 15, 10, 30),
        );

        await tester.pumpWidget(
          MaterialApp(
            home: Scaffold(
              body: ComplaintCard(
                complaint: testComplaint,
                onTap: () => tapped = true,
              ),
            ),
          ),
        );

        expect(find.text('CMP-000042'), findsOneWidget);
        expect(find.text('تسرب مياه في الحي'), findsOneWidget);
        expect(find.text('قيد التنفيذ'), findsOneWidget);
        expect(find.text('طارئ'), findsOneWidget);
        expect(
          find.text('وزارة المياه والبيئة • كسر في أنبوب رئيسي'),
          findsOneWidget,
        );

        await tester.tap(find.byType(ComplaintCard));
        expect(tapped, isTrue);
      },
    );
  });

  group('TimelineWidget Tests - 10 Authorized Event Types', () {
    final eventTypes = [
      {'type': 'created', 'expected': 'تم تسجيل البلاغ'},
      {'type': 'verified', 'expected': 'تم التحقق من البلاغ'},
      {'type': 'assigned', 'expected': 'إسناد البلاغ للميدان'},
      {'type': 'transferred', 'expected': 'تحويل البلاغ بين الجهات'},
      {'type': 'status_changed', 'expected': 'تحديث حالة البلاغ'},
      {'type': 'evidence_uploaded', 'expected': 'رفع أدلة ميدانية'},
      {'type': 'resolved', 'expected': 'تم إنجاز المعالجة'},
      {'type': 'closed', 'expected': 'تم إغلاق البلاغ'},
      {'type': 'reopened', 'expected': 'أُعيد فتح البلاغ'},
      {'type': 'rejected', 'expected': 'تم رفض البلاغ'},
    ];

    testWidgets('renders all 10 event types accurately', (tester) async {
      final events = eventTypes.map((e) {
        return TimelineEntity(
          id: 1,
          eventType: e['type']!,
          description: 'تفاصيل الحدث ${e['expected']}',
          performerName: 'المهندس أحمد',
          createdAt: DateTime(2026, 9, 15, 12, 0),
        );
      }).toList();

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: SingleChildScrollView(child: TimelineWidget(events: events)),
          ),
        ),
      );

      for (final e in eventTypes) {
        expect(find.text(e['expected']!), findsOneWidget);
      }
      expect(find.text('بواسطة: المهندس أحمد'), findsNWidgets(10));
    });
  });

  group('MyComplaintsNotifier State Management Tests', () {
    late MockComplaintRepository mockRepo;
    late ProviderContainer container;

    final dummyComplaint = ComplaintEntity(
      id: 1,
      title: 'بلاغ تجريبي',
      description: 'وصف',
      status: 'new',
      priority: 'medium',
      latitude: 15.0,
      longitude: 44.0,
    );

    setUp(() {
      mockRepo = MockComplaintRepository();
      mockRepo.complaintsToReturn = [dummyComplaint];

      container = ProviderContainer(
        overrides: [complaintRepositoryProvider.overrideWithValue(mockRepo)],
      );
    });

    tearDown(() {
      container.dispose();
    });

    test('loadInitial loads page 1 and populates state', () async {
      final notifier = container.read(myComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      final state = container.read(myComplaintsNotifierProvider);
      expect(state.isLoadingInitial, false);
      expect(state.complaints.length, 1);
      expect(state.currentPage, 1);
      expect(state.hasMore, true);
      expect(mockRepo.getPaginatedCalls, greaterThanOrEqualTo(1));
    });

    test('loadMore appends complaints from next page', () async {
      final notifier = container.read(myComplaintsNotifierProvider.notifier);
      await notifier.loadInitial();

      mockRepo.complaintsToReturn = [
        dummyComplaint,
        ComplaintEntity(
          id: 2,
          title: 'بلاغ ثانٍ',
          description: 'وصف',
          status: 'resolved',
          priority: 'low',
          latitude: 15.1,
          longitude: 44.1,
        ),
      ];

      await notifier.loadMore();

      final state = container.read(myComplaintsNotifierProvider);
      expect(state.complaints.length, 3);
      expect(state.currentPage, 2);
      expect(mockRepo.lastRequestedPage, 2);
    });

    test(
      'refresh reloads fresh page 1 while preserving state on error',
      () async {
        final notifier = container.read(myComplaintsNotifierProvider.notifier);
        await notifier.loadInitial();

        mockRepo.shouldThrow = true;
        await notifier.refresh();

        final state = container.read(myComplaintsNotifierProvider);
        expect(state.isRefreshing, false);
        expect(state.complaints.length, 1); // Existing data not wiped
      },
    );

    test('filterByStatus updates filter and reloads', () async {
      final notifier = container.read(myComplaintsNotifierProvider.notifier);
      await notifier.filterByStatus('in_progress');

      final state = container.read(myComplaintsNotifierProvider);
      expect(state.selectedStatus, 'in_progress');
      expect(mockRepo.lastRequestedStatus, 'in_progress');
    });

    test(
      'recentComplaintsProvider remains independent when myComplaintsNotifierProvider is filtered',
      () async {
        // Fetch recent complaints
        final recent = await container.read(recentComplaintsProvider.future);
        expect(recent.length, 1);

        // Filter myComplaintsNotifierProvider by 'in_progress'
        final notifier = container.read(myComplaintsNotifierProvider.notifier);
        mockRepo.complaintsToReturn = []; // Simulate no in_progress complaints in DB
        await notifier.filterByStatus('in_progress');

        final myComplaintsState = container.read(myComplaintsNotifierProvider);
        expect(myComplaintsState.selectedStatus, 'in_progress');
        expect(myComplaintsState.complaints, isEmpty);

        // recentComplaintsProvider must still hold its independent unfiltered complaints
        final recentAfterFilter =
            container.read(recentComplaintsProvider).value;
        expect(recentAfterFilter, isNotNull);
        expect(recentAfterFilter!.length, 1);
        expect(
          recentAfterFilter.first.title,
          dummyComplaint.title,
        );
      },
    );
  });
}
