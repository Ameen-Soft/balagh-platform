import 'package:mobile/features/auth/domain/entities/user_entity.dart';

abstract class ProfileRepository {
  Future<UserEntity> getProfile();

  Future<UserEntity> updateProfile({
    required String name,
    String? phone,
    String? nationalId,
  });
}
