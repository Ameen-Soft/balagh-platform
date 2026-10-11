import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/api_exceptions.dart';
import 'package:mobile/features/auth/application/providers.dart';
import '../domain/repositories/profile_repository.dart';
import 'profile_providers.dart';
import 'profile_state.dart';

class ProfileNotifier extends Notifier<ProfileState> {
  late final ProfileRepository _repository;

  @override
  ProfileState build() {
    _repository = ref.watch(profileRepositoryProvider);
    // Initialize with current authenticated user from auth state
    final authUser = ref.watch(authNotifierProvider).user;
    return ProfileState(
      status: authUser != null ? ProfileStatus.loaded : ProfileStatus.initial,
      user: authUser,
    );
  }

  Future<void> loadProfile({bool refresh = false}) async {
    if (state.isLoading && !refresh) return;

    if (!refresh && state.user == null) {
      state = state.copyWith(status: ProfileStatus.loading, clearErrors: true);
    }

    try {
      final user = await _repository.getProfile();
      state = state.copyWith(
        status: ProfileStatus.loaded,
        user: user,
        clearErrors: true,
      );
      // Synchronize with global auth state
      ref.read(authNotifierProvider.notifier).updateCurrentUser(user);
    } catch (e) {
      // If we already have a user in state, don't clear it on refresh error
      final errorMsg = e is ApiException
          ? e.message
          : 'حدث خطأ في الاتصال أثناء جلب بيانات الملف الشخصي.';

      state = state.copyWith(
        status: state.user != null ? ProfileStatus.loaded : ProfileStatus.error,
        errorMessage: errorMsg,
      );
    }
  }

  Future<bool> updateProfile({
    required String name,
    String? phone,
    String? nationalId,
  }) async {
    if (state.isSaving) return false;

    state = state.copyWith(isSaving: true, clearErrors: true);

    try {
      final updatedUser = await _repository.updateProfile(
        name: name,
        phone: phone,
        nationalId: nationalId,
      );

      state = state.copyWith(
        status: ProfileStatus.loaded,
        user: updatedUser,
        isSaving: false,
        clearErrors: true,
      );

      // Synchronize with global auth state so Home and other pages update immediately
      ref.read(authNotifierProvider.notifier).updateCurrentUser(updatedUser);
      return true;
    } on ValidationException catch (e) {
      state = state.copyWith(
        isSaving: false,
        errorMessage: e.firstErrorMessage,
        validationErrors: e.errors,
      );
      return false;
    } on ApiException catch (e) {
      state = state.copyWith(
        isSaving: false,
        errorMessage: e.message,
      );
      return false;
    } catch (e) {
      state = state.copyWith(
        isSaving: false,
        errorMessage: 'حدث خطأ غير متوقع أثناء تحديث الملف الشخصي.',
      );
      return false;
    }
  }
}
