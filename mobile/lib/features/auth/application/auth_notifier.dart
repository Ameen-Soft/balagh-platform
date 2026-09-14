import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import '../domain/repositories/auth_repository.dart';
import 'auth_state.dart';
import 'providers.dart';

class AuthNotifier extends Notifier<AuthState> {
  @override
  AuthState build() {
    return AuthState.initial();
  }

  AuthRepository get _repository => ref.read(authRepositoryProvider);

  /// Check session on startup to restore auth state without flicker
  Future<void> checkAuth() async {
    try {
      final hasToken = await _repository.hasSavedToken();
      if (!hasToken) {
        state = AuthState.unauthenticated();
        return;
      }

      final user = await _repository.getCurrentUser();
      state = AuthState.authenticated(user);
    } catch (e) {
      await _repository.logout();
      state = AuthState.unauthenticated();
    }
  }

  /// Authenticate user with email and password
  Future<bool> login({
    required String email,
    required String password,
    String? deviceName,
  }) async {
    state = AuthState.loading();
    try {
      final user = await _repository.login(
        email: email,
        password: password,
        deviceName: deviceName,
      );
      state = AuthState.authenticated(user);
      return true;
    } on ValidationException catch (e) {
      state = AuthState.error(
        e.firstErrorMessage,
        validationErrors: e.errors,
      );
      return false;
    } on ApiException catch (e) {
      state = AuthState.error(e.message);
      return false;
    } catch (e) {
      state = AuthState.error('حدث خطأ غير متوقع أثناء تسجيل الدخول.');
      return false;
    }
  }

  /// Register new citizen account
  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
    String? nationalId,
  }) async {
    state = AuthState.loading();
    try {
      final user = await _repository.register(
        name: name,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
        phone: phone,
        nationalId: nationalId,
      );
      state = AuthState.authenticated(user);
      return true;
    } on ValidationException catch (e) {
      state = AuthState.error(
        e.firstErrorMessage,
        validationErrors: e.errors,
      );
      return false;
    } on ApiException catch (e) {
      state = AuthState.error(e.message);
      return false;
    } catch (e) {
      state = AuthState.error('حدث خطأ غير متوقع أثناء إنشاء الحساب.');
      return false;
    }
  }

  /// Logout current user and clear stored token
  Future<void> logout() async {
    state = AuthState.loading(user: state.user);
    try {
      await _repository.logout();
    } finally {
      state = AuthState.unauthenticated();
    }
  }

  /// Clear any error message
  void clearError() {
    if (state.isError) {
      state = AuthState.unauthenticated();
    }
  }
}
