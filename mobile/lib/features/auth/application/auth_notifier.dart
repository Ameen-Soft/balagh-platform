import 'dart:async';
import 'dart:io';
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

  /// Check session on startup to restore auth state without flicker or logout on offline
  Future<void> checkAuth() async {
    state = AuthState.restoring();
    try {
      final hasToken = await _repository.hasSavedToken();
      if (!hasToken) {
        state = AuthState.unauthenticated();
        return;
      }

      // 1. Immediately restore locally saved user if available
      final cachedUser = await _repository.getCachedUser();
      if (cachedUser != null) {
        state = AuthState.authenticated(cachedUser, isOffline: false);
      }

      // 2. Validate session and refresh latest data with the server
      try {
        final freshUser = await _repository.getCurrentUser();
        state = AuthState.authenticated(freshUser, isOffline: false);
      } on UnauthorizedException {
        // Explicit 401 from server: session expired or token revoked!
        await _repository.logout();
        state = AuthState.unauthenticated();
      } on ForbiddenException catch (e) {
        // Explicit 403 from server: account deactivated / inactive!
        await _repository.logout();
        state = AuthState.error(e.message);
      } catch (e) {
        // Network disconnected, SocketException, TimeoutException, Server 5xx, etc.
        // DO NOT LOG OUT! DO NOT CLEAR SAVED SESSION!
        if (cachedUser != null) {
          state = AuthState.serverUnavailable(
            user: cachedUser,
            message: 'تعذر الاتصال بالخادم.',
          );
        } else {
          // Fallback user if token exists but cached user payload was missing
          const fallbackUser = UserEntity(
            id: 0,
            name: 'مستخدم مسجل',
            email: '',
            isEmailVerified: true,
          );
          state = AuthState.serverUnavailable(
            user: fallbackUser,
            message: 'تعذر الاتصال بالخادم.',
          );
        }
      }
    } catch (_) {
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

  /// Update the current authenticated user in memory and update cached storage
  void updateCurrentUser(UserEntity updatedUser) {
    if (state.isAuthenticated) {
      state = AuthState.authenticated(updatedUser, isOffline: state.isOffline);
      _repository.saveCachedUser(updatedUser);
    }
  }

  /// Refresh current user profile from server
  Future<void> refreshCurrentUser() async {
    try {
      final user = await _repository.getCurrentUser();
      state = AuthState.authenticated(user, isOffline: false);
    } on UnauthorizedException {
      await _repository.logout();
      state = AuthState.unauthenticated();
    } on ForbiddenException catch (e) {
      await _repository.logout();
      state = AuthState.error(e.message);
    } catch (_) {
      // Keep existing user state if refresh fails due to network
    }
  }

  /// Retry session verification when internet connectivity is restored
  Future<void> retrySessionVerification() async {
    if (!state.isAuthenticated) return;
    try {
      final freshUser = await _repository.getCurrentUser();
      state = AuthState.authenticated(freshUser, isOffline: false);
    } on UnauthorizedException {
      await _repository.logout();
      state = AuthState.unauthenticated();
    } on ForbiddenException catch (e) {
      await _repository.logout();
      state = AuthState.error(e.message);
    } catch (_) {
      // Still offline, remain in current authenticated state
    }
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

