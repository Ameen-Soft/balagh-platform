/// Abstract repository contract for onboarding feature (Domain Layer)
abstract class OnboardingRepository {
  /// Checks if the onboarding flow has already been completed
  Future<bool> isOnboardingCompleted();

  /// Marks the onboarding flow as completed
  Future<void> completeOnboarding();

  /// Resets the onboarding status (useful for development and testing)
  Future<void> resetOnboarding();
}
