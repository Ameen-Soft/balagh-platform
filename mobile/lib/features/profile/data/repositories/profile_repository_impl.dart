import 'package:mobile/features/auth/domain/entities/user_entity.dart';
import '../../domain/repositories/profile_repository.dart';
import '../datasources/profile_remote_data_source.dart';

class ProfileRepositoryImpl implements ProfileRepository {
  final ProfileRemoteDataSource remoteDataSource;

  ProfileRepositoryImpl({required this.remoteDataSource});

  @override
  Future<UserEntity> getProfile() async {
    final model = await remoteDataSource.getProfile();
    return model.toEntity();
  }

  @override
  Future<UserEntity> updateProfile({
    required String name,
    String? phone,
    String? nationalId,
  }) async {
    final model = await remoteDataSource.updateProfile(
      name: name,
      phone: phone,
      nationalId: nationalId,
    );
    return model.toEntity();
  }
}
