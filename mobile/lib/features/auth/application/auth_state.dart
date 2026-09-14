import '../domain/entities/user_entity.dart';

enum AuthStatus {
  initial,
  loading,
  authenticated,
  unauthenticated,
  error,
}

class AuthState {
  final AuthStatus status;
  final UserEntity? user;
  final String? errorMessage;
  final Map<String, List<String>>? validationErrors;

  const AuthState({
    required this.status,
    this.user,
    this.errorMessage,
    this.validationErrors,
  });

  factory AuthState.initial() => const AuthState(status: AuthStatus.initial);

  factory AuthState.loading({UserEntity? user}) => AuthState(
        status: AuthStatus.loading,
        user: user,
      );

  factory AuthState.authenticated(UserEntity user) => AuthState(
        status: AuthStatus.authenticated,
        user: user,
      );

  factory AuthState.unauthenticated() => const AuthState(
        status: AuthStatus.unauthenticated,
      );

  factory AuthState.error(
    String message, {
    Map<String, List<String>>? validationErrors,
    UserEntity? user,
  }) =>
      AuthState(
        status: AuthStatus.error,
        errorMessage: message,
        validationErrors: validationErrors,
        user: user,
      );

  bool get isInitial => status == AuthStatus.initial;
  bool get isLoading => status == AuthStatus.loading;
  bool get isAuthenticated => status == AuthStatus.authenticated;
  bool get isUnauthenticated => status == AuthStatus.unauthenticated;
  bool get isError => status == AuthStatus.error;

  AuthState copyWith({
    AuthStatus? status,
    UserEntity? user,
    String? errorMessage,
    Map<String, List<String>>? validationErrors,
  }) {
    return AuthState(
      status: status ?? this.status,
      user: user ?? this.user,
      errorMessage: errorMessage ?? this.errorMessage,
      validationErrors: validationErrors ?? this.validationErrors,
    );
  }
}
