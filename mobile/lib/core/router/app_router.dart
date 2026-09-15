import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/features/auth/application/auth_state.dart';
import 'package:mobile/features/auth/application/providers.dart';
import 'package:mobile/features/auth/presentation/pages/login_page.dart';
import 'package:mobile/features/auth/presentation/pages/register_page.dart';
import 'package:mobile/features/auth/presentation/pages/splash_page.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_entity.dart';
import 'package:mobile/features/complaints/presentation/pages/complaint_details_page.dart';
import 'package:mobile/features/complaints/presentation/pages/create_complaint_page.dart';
import 'package:mobile/features/complaints/presentation/pages/my_complaints_page.dart';
import 'package:mobile/features/home/presentation/pages/home_page.dart';
import 'package:mobile/features/onboarding/application/onboarding_provider.dart';
import 'package:mobile/features/onboarding/presentation/pages/onboarding_page.dart';

class OnboardingStatusNotifier extends Notifier<bool> {
  @override
  bool build() => false;

  void setCompleted(bool value) => state = value;
}

final onboardingStatusProvider =
    NotifierProvider<OnboardingStatusNotifier, bool>(
  OnboardingStatusNotifier.new,
);

/// Listenable to trigger GoRouter re-evaluations when AuthState or OnboardingStatus changes
class RouterListenable extends ChangeNotifier {
  final Ref _ref;

  RouterListenable(this._ref) {
    _ref.listen<AuthState>(
      authNotifierProvider,
      (previous, next) => notifyListeners(),
    );
    _ref.listen<bool>(
      onboardingStatusProvider,
      (previous, next) => notifyListeners(),
    );
  }
}

final routerListenableProvider = Provider<RouterListenable>((ref) {
  return RouterListenable(ref);
});

final appRouterProvider = Provider<GoRouter>((ref) {
  final listenable = ref.watch(routerListenableProvider);
  final onboardingRepository = ref.watch(onboardingRepositoryProvider);

  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: listenable,
    routes: [
      GoRoute(
        path: '/splash',
        name: 'splash',
        builder: (context, state) => const SplashPage(),
      ),
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
        builder: (context, state) => const LoginPage(),
      ),
      GoRoute(
        path: '/register',
        name: 'register',
        builder: (context, state) => const RegisterPage(),
      ),
      GoRoute(
        path: '/home',
        name: 'home',
        builder: (context, state) => const HomePage(),
      ),
      GoRoute(
        path: '/create-complaint',
        name: 'create-complaint',
        builder: (context, state) => const CreateComplaintPage(),
      ),
      GoRoute(
        path: '/my-complaints',
        name: 'my-complaints',
        builder: (context, state) => const MyComplaintsPage(),
      ),
      GoRoute(
        path: '/complaints/:id',
        name: 'complaint-details',
        builder: (context, state) {
          final idParam = state.pathParameters['id'] ?? '';
          final id = int.tryParse(idParam) ?? 0;
          final extra = state.extra;
          final initialComplaint = extra is ComplaintEntity ? extra : null;

          return ComplaintDetailsPage(
            complaintId: id,
            initialComplaint: initialComplaint,
          );
        },
      ),
    ],
    redirect: (BuildContext context, GoRouterState state) {
      final authState = ref.read(authNotifierProvider);
      final isOnboardingCompleted = ref.read(onboardingStatusProvider);

      final isSplash = state.matchedLocation == '/splash';
      final isLogin = state.matchedLocation == '/login';
      final isRegister = state.matchedLocation == '/register';
      final isOnboarding = state.matchedLocation == '/onboarding';

      // 1. If onboarding is not completed, force /onboarding
      if (!isOnboardingCompleted) {
        return isOnboarding ? null : '/onboarding';
      }

      // If on onboarding but it is completed, redirect according to auth
      if (isOnboarding) {
        if (authState.isAuthenticated) return '/home';
        if (authState.isInitial) return '/splash';
        return '/login';
      }

      // 2. While checking auth session (initial state), stay on splash to avoid flicker
      if (authState.isInitial) {
        return isSplash ? null : '/splash';
      }

      // 3. User is authenticated
      if (authState.isAuthenticated) {
        if (isLogin || isRegister || isSplash) {
          return '/home';
        }
        return null;
      }

      // 4. User is unauthenticated or has error
      if (authState.isUnauthenticated || authState.isError) {
        if (!isLogin && !isRegister) {
          return '/login';
        }
        return null;
      }

      return null;
    },
  );
});
