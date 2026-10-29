import '../domain/entities/user_entity.dart';

enum AuthStatus {
  initial,
  restoring,
  loading,
  authenticated,
  unauthenticated,
  error,
  serverUnavailable,
}

class AuthState {
  final AuthStatus status;
  final UserEntity? user;
  final String? errorMessage;
  final Map<String, List<String>>? validationErrors;
  final bool isOffline;

  const AuthState({
    required this.status,
    this.user,
    this.errorMessage,
    this.validationErrors,
    this.isOffline = false,
  });

  factory AuthState.initial() => const AuthState(status: AuthStatus.initial);

  factory AuthState.restoring() => const AuthState(status: AuthStatus.restoring);

  factory AuthState.loading({UserEntity? user}) => AuthState(
        status: AuthStatus.loading,
        user: user,
      );

  factory AuthState.authenticated(UserEntity user, {bool isOffline = false}) =>
      AuthState(
        status: AuthStatus.authenticated,
        user: user,
        isOffline: isOffline,
      );

  factory AuthState.serverUnavailable({UserEntity? user, String? message}) =>
      AuthState(
        status: AuthStatus.serverUnavailable,
        user: user,
        errorMessage: message ?? 'تعذر الاتصال بالخادم.',
        isOffline: true,
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
  bool get isRestoring => status == AuthStatus.restoring;
  bool get isLoading => status == AuthStatus.loading;
  bool get isAuthenticated =>
      status == AuthStatus.authenticated ||
      (status == AuthStatus.serverUnavailable && user != null);
  bool get isUnauthenticated => status == AuthStatus.unauthenticated;
  bool get isError => status == AuthStatus.error;
  bool get isServerUnavailable => status == AuthStatus.serverUnavailable;

  AuthState copyWith({
    AuthStatus? status,
    UserEntity? user,
    String? errorMessage,
    Map<String, List<String>>? validationErrors,
    bool? isOffline,
  }) {
    return AuthState(
      status: status ?? this.status,
      user: user ?? this.user,
      errorMessage: errorMessage ?? this.errorMessage,
      validationErrors: validationErrors ?? this.validationErrors,
      isOffline: isOffline ?? this.isOffline,
    );
  }
}
