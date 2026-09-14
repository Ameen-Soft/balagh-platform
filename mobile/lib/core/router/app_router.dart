import 'package:go_router/go_router.dart';
import 'package:mobile/features/auth/presentation/pages/login_page.dart';
import 'package:mobile/features/onboarding/domain/repositories/onboarding_repository.dart';
import 'package:mobile/features/onboarding/presentation/pages/onboarding_page.dart';

/// App Router configuration using GoRouter
GoRouter createAppRouter({
  required bool isOnboardingCompleted,
  required OnboardingRepository onboardingRepository,
}) {
  return GoRouter(
    initialLocation: isOnboardingCompleted ? '/login' : '/onboarding',
    routes: [
      GoRoute(
        path: '/onboarding',
        name: 'onboarding',
        builder: (context, state) => OnboardingPage(
          repository: onboardingRepository,
        ),
      ),
      GoRoute(
        path: '/login',
        name: 'login',
        builder: (context, state) => LoginPage(
          repository: onboardingRepository,
        ),
      ),
    ],
  );
}
