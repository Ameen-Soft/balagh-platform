import 'package:mobile/features/auth/domain/entities/user_entity.dart';

enum ProfileStatus { initial, loading, loaded, error }

class ProfileState {
  final ProfileStatus status;
  final UserEntity? user;
  final bool isSaving;
  final String? errorMessage;
  final String? successMessage;
  final Map<String, List<String>>? validationErrors;

  const ProfileState({
    this.status = ProfileStatus.initial,
    this.user,
    this.isSaving = false,
    this.errorMessage,
    this.successMessage,
    this.validationErrors,
  });

  const ProfileState.initial() : this();

  const ProfileState.loading() : this(status: ProfileStatus.loading);

  const ProfileState.loaded(UserEntity user)
      : this(status: ProfileStatus.loaded, user: user);

  const ProfileState.error(String message)
      : this(status: ProfileStatus.error, errorMessage: message);

  bool get isInitial => status == ProfileStatus.initial;
  bool get isLoading => status == ProfileStatus.loading;
  bool get isLoaded => status == ProfileStatus.loaded;
  bool get hasError => status == ProfileStatus.error;

  ProfileState copyWith({
    ProfileStatus? status,
    UserEntity? user,
    bool? isSaving,
    String? errorMessage,
    String? successMessage,
    Map<String, List<String>>? validationErrors,
    bool clearErrors = false,
    bool clearSuccess = false,
  }) {
    return ProfileState(
      status: status ?? this.status,
      user: user ?? this.user,
      isSaving: isSaving ?? this.isSaving,
      errorMessage: clearErrors ? null : (errorMessage ?? this.errorMessage),
      successMessage:
          clearSuccess ? null : (successMessage ?? this.successMessage),
      validationErrors:
          clearErrors ? null : (validationErrors ?? this.validationErrors),
    );
  }
}
