import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../data/datasources/onboarding_local_data_source.dart';
import '../data/repositories/onboarding_repository_impl.dart';
import '../domain/repositories/onboarding_repository.dart';

final sharedPreferencesProvider = Provider<SharedPreferences>((ref) {
  throw UnimplementedError('SharedPreferences must be overridden in ProviderScope');
});

final onboardingLocalDataSourceProvider =
    Provider<OnboardingLocalDataSource>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return OnboardingLocalDataSourceImpl(prefs);
});

final onboardingRepositoryProvider = Provider<OnboardingRepository>((ref) {
  final localDataSource = ref.watch(onboardingLocalDataSourceProvider);
  return OnboardingRepositoryImpl(localDataSource);
});
