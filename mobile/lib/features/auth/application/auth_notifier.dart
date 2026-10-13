import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile/core/network/api_exceptions.dart';
import '../domain/entities/user_entity.dart';
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

  /// Authenticate user with Google
  Future<bool> loginWithGoogle(String idToken) async {
    state = AuthState.loading();
    try {
      final user = await _repository.loginWithGoogle(idToken);
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
      state = AuthState.error('حدث خطأ غير متوقع أثناء تسجيل الدخول باستخدام جوجل.');
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

  /// Update the current authenticated user in memory
  void updateCurrentUser(UserEntity updatedUser) {
    if (state.isAuthenticated) {
      state = AuthState.authenticated(updatedUser);
    }
  }

  /// Refresh current user profile from server
  Future<void> refreshCurrentUser() async {
    try {
      final user = await _repository.getCurrentUser();
      state = AuthState.authenticated(user);
    } catch (_) {}
  }

  /// Request resending the verification email
  Future<void> sendEmailVerification({String? email}) async {
    await _repository.sendEmailVerification(email: email);
  }

  /// Check server-side email verification status
  Future<bool> checkEmailVerificationStatus() async {
    final isVerified = await _repository.checkEmailVerificationStatus();
    if (isVerified && state.isAuthenticated && state.user != null) {
      state = AuthState.authenticated(
        state.user!.copyWith(isEmailVerified: true),
      );
    }
    return isVerified;
  }

  /// Send password reset email
  Future<void> forgotPassword(String email) async {
    await _repository.forgotPassword(email);
  }

  /// Reset password with token
  Future<void> resetPassword({
    required String email,
    required String token,
    required String password,
    required String passwordConfirmation,
  }) async {
    await _repository.resetPassword(
      email: email,
      token: token,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );
  }
}

