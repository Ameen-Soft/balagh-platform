import 'package:mobile/features/onboarding/data/datasources/onboarding_local_data_source.dart';
import 'package:mobile/features/onboarding/domain/repositories/onboarding_repository.dart';

/// Implementation of [OnboardingRepository] connecting to [OnboardingLocalDataSource]
class OnboardingRepositoryImpl implements OnboardingRepository {
  final OnboardingLocalDataSource _localDataSource;

  OnboardingRepositoryImpl(this._localDataSource);

  @override
  Future<bool> isOnboardingCompleted() async {
    return _localDataSource.isOnboardingCompleted();
  }

  @override
  Future<void> completeOnboarding() async {
    await _localDataSource.setOnboardingCompleted(true);
  }

  @override
  Future<void> resetOnboarding() async {
    await _localDataSource.clear();
  }
}
