import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/network/api_client.dart';
import 'package:mobile/core/network/api_endpoints.dart';
import 'package:mobile/core/storage/secure_storage_service.dart';
import 'package:mobile/features/complaints/data/datasources/complaint_remote_data_source.dart';
import 'package:mobile/features/complaints/data/models/complaint_model.dart';
import 'package:mobile/features/complaints/data/models/paginated_complaints_model.dart';
import 'package:mobile/features/complaints/data/models/pagination_meta_model.dart';
import 'package:mobile/features/complaints/data/repositories/complaint_repository_impl.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_like_result.dart';

class FakeSecureStorageService implements ISecureStorageService {
  String? token = 'fake_test_token';
  String? userData;

  @override
  Future<void> saveToken(String token) async => this.token = token;

  @override
  Future<String?> getToken() async => token;

  @override
  Future<void> deleteToken() async => token = null;

  @override
  Future<bool> hasToken() async => token != null;

  @override
  Future<void> saveUserData(String userDataJson) async => userData = userDataJson;

  @override
  Future<String?> getUserData() async => userData;

  @override
  Future<void> deleteUserData() async => userData = null;

  @override
  Future<void> clearAll() async {
    token = null;
    userData = null;
  }
}

class FakeApiClient extends ApiClient {
  FakeApiClient() : super(secureStorage: FakeSecureStorageService());

  String? lastGetPath;
  Map<String, dynamic>? lastGetQueryParams;

  String? lastPostPath;
  dynamic lastPostData;

  String? lastDeletePath;

  dynamic nextGetResponse;
  dynamic nextPostResponse;
  dynamic nextDeleteResponse;

  bool shouldThrow = false;
  String errorMessage = 'فشل الاتصال بالشبكة';

  @override
  Future<Response<T>> get<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    lastGetPath = path;
    lastGetQueryParams = queryParameters;

    if (shouldThrow) {
      throw Exception(errorMessage);
    }

    return Response<T>(
      requestOptions: RequestOptions(path: path),
      data: nextGetResponse as T?,
      statusCode: 200,
    );
  }

  @override
  Future<Response<T>> post<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    lastPostPath = path;
    lastPostData = data;

    if (shouldThrow) {
      throw Exception(errorMessage);
    }

    return Response<T>(
      requestOptions: RequestOptions(path: path),
      data: nextPostResponse as T?,
      statusCode: 200,
    );
  }

  @override
  Future<Response<T>> delete<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    lastDeletePath = path;

    if (shouldThrow) {
      throw Exception(errorMessage);
    }

    return Response<T>(
      requestOptions: RequestOptions(path: path),
      data: nextDeleteResponse as T?,
      statusCode: 200,
    );
  }
}

class MockComplaintRemoteDataSource implements ComplaintRemoteDataSource {
  bool shouldThrow = false;

  @override
  Future<PaginatedComplaintsModel> getPublicComplaints({
    int page = 1,
    String? sortBy,
    int? categoryId,
    String? status,
    String? search,
  }) async {
    if (shouldThrow) throw Exception('DataSource error');
    return PaginatedComplaintsModel(
      items: const [
        ComplaintModel(
          id: 101,
          title: 'بلاغ مجتمعي تجريبي',
          description: 'وصف للبلاغ',
          status: 'new',
          priority: 'medium',
          latitude: 15.35,
          longitude: 44.20,
          likesCount: 12,
          isLikedByMe: true,
          isPublic: true,
        ),
      ],
      meta: const PaginationMetaModel(
        currentPage: 1,
        lastPage: 3,
        perPage: 15,
        total: 45,
        hasMore: true,
      ),
    );
  }

  @override
  Future<List<ComplaintModel>> getMapComplaints({
    double? minLat,
    double? minLng,
    double? maxLat,
    double? maxLng,
  }) async {
    if (shouldThrow) throw Exception('Map DataSource error');
    return const [
      ComplaintModel(
        id: 102,
        title: 'نقطة على الخريطة',
        description: 'وصف نقطة الخريطة',
        status: 'in_progress',
        priority: 'high',
        latitude: 15.36,
        longitude: 44.21,
        likesCount: 5,
        isLikedByMe: false,
        isPublic: true,
      ),
    ];
  }

  @override
  Future<ComplaintLikeResult> likeComplaint(int complaintId) async {
    if (shouldThrow) throw Exception('Like DataSource error');
    return const ComplaintLikeResult(isLiked: true, likesCount: 6);
  }

  @override
  Future<ComplaintLikeResult> unlikeComplaint(int complaintId) async {
    if (shouldThrow) throw Exception('Unlike DataSource error');
    return const ComplaintLikeResult(isLiked: false, likesCount: 5);
  }

  @override
  dynamic noSuchMethod(Invocation invocation) => super.noSuchMethod(invocation);
}

void main() {
  group('Community Remote Data Source Tests', () {
    late FakeApiClient fakeApiClient;
    late ComplaintRemoteDataSourceImpl dataSource;

    setUp(() {
      fakeApiClient = FakeApiClient();
      dataSource = ComplaintRemoteDataSourceImpl(apiClient: fakeApiClient);
    });

    test('getPublicComplaints sends correct path and query parameters', () async {
      fakeApiClient.nextGetResponse = {
        'data': [
          {
            'id': 1,
            'title': 'حفرة في طريق المطار',
            'description': 'حفرة خطيرة',
            'status': 'new',
            'priority': 'medium',
            'latitude': 15.35,
            'longitude': 44.20,
            'likes_count': 9,
            'is_liked_by_me': false,
            'is_public': true,
          }
        ],
        'meta': {
          'current_page': 1,
          'last_page': 2,
          'per_page': 15,
          'total': 25,
        }
      };

      final result = await dataSource.getPublicComplaints(
        page: 2,
        sortBy: 'popular',
        categoryId: 3,
        status: 'in_progress',
        search: 'شارع حدة',
      );

      expect(fakeApiClient.lastGetPath, ApiEndpoints.publicComplaints);
      expect(fakeApiClient.lastGetQueryParams?['page'], 2);
      expect(fakeApiClient.lastGetQueryParams?['sort_by'], 'popular');
      expect(fakeApiClient.lastGetQueryParams?['category_id'], 3);
      expect(fakeApiClient.lastGetQueryParams?['status'], 'in_progress');
      expect(fakeApiClient.lastGetQueryParams?['search'], 'شارع حدة');

      expect(result.items.length, 1);
      expect(result.items.first.id, 1);
      expect(result.items.first.likesCount, 9);
      expect(result.items.first.isLikedByMe, false);
      expect(result.meta.currentPage, 1);
      expect(result.meta.total, 25);
    });

    test('getMapComplaints sends correct bbox query parameter', () async {
      fakeApiClient.nextGetResponse = {
        'data': [
          {
            'id': 20,
            'title': 'عمود كهربائي مائل',
            'description': 'في تقاطع الستين',
            'status': 'new',
            'priority': 'urgent',
            'latitude': 15.345,
            'longitude': 44.195,
            'likes_count': 18,
            'is_liked_by_me': true,
            'is_public': true,
          }
        ]
      };

      final result = await dataSource.getMapComplaints(
        minLat: 15.30,
        minLng: 44.10,
        maxLat: 15.40,
        maxLng: 44.30,
      );

      expect(fakeApiClient.lastGetPath, ApiEndpoints.mapComplaints);
      expect(fakeApiClient.lastGetQueryParams?['bbox'], '15.3,44.1,15.4,44.3');
      expect(result.length, 1);
      expect(result.first.id, 20);
      expect(result.first.likesCount, 18);
      expect(result.first.isLikedByMe, true);
    });

    test('likeComplaint sends POST to /complaints/{id}/like and returns parsed result', () async {
      fakeApiClient.nextPostResponse = {
        'success': true,
        'data': {
          'liked': true,
          'likes_count': 10,
        }
      };

      final result = await dataSource.likeComplaint(42);

      expect(fakeApiClient.lastPostPath, ApiEndpoints.likeComplaint(42));
      expect(result.isLiked, true);
      expect(result.likesCount, 10);
    });

    test('unlikeComplaint sends DELETE to /complaints/{id}/like and returns parsed result', () async {
      fakeApiClient.nextDeleteResponse = {
        'success': true,
        'data': {
          'liked': false,
          'likes_count': 9,
        }
      };

      final result = await dataSource.unlikeComplaint(42);

      expect(fakeApiClient.lastDeletePath, ApiEndpoints.unlikeComplaint(42));
      expect(result.isLiked, false);
      expect(result.likesCount, 9);
    });

    test('createComplaint includes is_public boolean in payload', () async {
      fakeApiClient.nextPostResponse = {
        'success': true,
        'data': {
          'id': 99,
          'title': 'بلاغ عام جديد',
          'description': 'وصف البلاغ العام',
          'status': 'new',
          'priority': 'low',
          'latitude': 15.32,
          'longitude': 44.22,
          'likes_count': 0,
          'is_liked_by_me': false,
          'is_public': true,
        }
      };

      final created = await dataSource.createComplaint(
        title: 'بلاغ عام جديد',
        description: 'وصف البلاغ العام',
        categoryId: 1,
        latitude: 15.32,
        longitude: 44.22,
        isPublic: true,
      );

      expect(fakeApiClient.lastPostPath, ApiEndpoints.complaints);
      final payload = fakeApiClient.lastPostData as Map<String, dynamic>;
      expect(payload['is_public'], true);
      expect(created.isPublic, true);
    });
  });

  group('ComplaintRepositoryImpl Community Tests', () {
    late MockComplaintRemoteDataSource mockDataSource;
    late ComplaintRepositoryImpl repository;

    setUp(() {
      mockDataSource = MockComplaintRemoteDataSource();
      repository = ComplaintRepositoryImpl(remoteDataSource: mockDataSource);
    });

    test('getPublicComplaints maps models to entities correctly', () async {
      final result = await repository.getPublicComplaints(
        page: 1,
        sortBy: 'popular',
      );

      expect(result.complaints.length, 1);
      final complaint = result.complaints.first;
      expect(complaint.id, 101);
      expect(complaint.likesCount, 12);
      expect(complaint.isLikedByMe, true);
      expect(complaint.isPublic, true);
      expect(result.currentPage, 1);
      expect(result.lastPage, 3);
      expect(result.total, 45);
      expect(result.hasMore, true);
    });

    test('getMapComplaints maps models to entities correctly', () async {
      final complaints = await repository.getMapComplaints(
        minLat: 15.0,
        minLng: 44.0,
        maxLat: 16.0,
        maxLng: 45.0,
      );

      expect(complaints.length, 1);
      expect(complaints.first.id, 102);
      expect(complaints.first.likesCount, 5);
      expect(complaints.first.isLikedByMe, false);
      expect(complaints.first.isPublic, true);
    });

    test('likeComplaint and unlikeComplaint delegate to data source', () async {
      final likeResult = await repository.likeComplaint(101);
      expect(likeResult.isLiked, true);
      expect(likeResult.likesCount, 6);

      final unlikeResult = await repository.unlikeComplaint(101);
      expect(unlikeResult.isLiked, false);
      expect(unlikeResult.likesCount, 5);
    });

    test('repository throws when data source throws', () async {
      mockDataSource.shouldThrow = true;

      expect(
        () => repository.getPublicComplaints(),
        throwsA(isA<Exception>()),
      );
      expect(
        () => repository.likeComplaint(101),
        throwsA(isA<Exception>()),
      );
    });
  });
}
