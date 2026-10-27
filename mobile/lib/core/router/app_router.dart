import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/features/auth/application/auth_state.dart';
import 'package:mobile/features/auth/application/providers.dart';
import 'package:mobile/features/auth/presentation/pages/login_page.dart';
import 'package:mobile/features/auth/presentation/pages/register_page.dart';
import 'package:mobile/features/auth/presentation/pages/splash_page.dart';
import 'package:mobile/features/auth/presentation/pages/email_verification_page.dart';
import 'package:mobile/features/auth/presentation/pages/forgot_password_page.dart';
import 'package:mobile/features/auth/presentation/pages/reset_password_page.dart';
import 'package:mobile/features/complaints/domain/entities/complaint_entity.dart';
import 'package:mobile/features/complaints/presentation/pages/complaint_details_page.dart';
import 'package:mobile/features/complaints/presentation/pages/community_complaints_page.dart';
import 'package:mobile/features/complaints/presentation/pages/create_complaint_page.dart';
import 'package:mobile/features/complaints/presentation/pages/my_complaints_page.dart';
import 'package:mobile/features/field_work/domain/entities/field_assignment_entity.dart';
import 'package:mobile/features/field_work/presentation/pages/tasks_list_page.dart';
import 'package:mobile/features/field_work/presentation/pages/task_details_page.dart';
import 'package:mobile/features/field_work/presentation/pages/task_execution_page.dart';
import 'package:mobile/features/home/presentation/pages/home_page.dart';
import 'package:mobile/features/onboarding/application/onboarding_provider.dart';
import 'package:mobile/features/onboarding/presentation/pages/onboarding_page.dart';
import 'package:mobile/features/projects/domain/entities/project_entity.dart';
import 'package:mobile/features/projects/presentation/pages/project_details_page.dart';
import 'package:mobile/features/projects/presentation/pages/projects_list_page.dart';
import 'package:mobile/features/map/presentation/pages/map_page.dart';
import 'package:mobile/features/notifications/presentation/pages/notifications_page.dart';
import 'package:mobile/features/profile/presentation/pages/profile_page.dart';
import 'package:mobile/features/profile/presentation/pages/edit_profile_page.dart';

class OnboardingStatusNotifier extends Notifier<bool> {
  @override
  bool build() => false;

  void setCompleted(bool value) => state = value;
}

final onboardingStatusProvider =
    NotifierProvider<OnboardingStatusNotifier, bool>(
      OnboardingStatusNotifier.new,
    );

class SplashFinishedNotifier extends Notifier<bool> {
  @override
  bool build() => false;

  void setFinished(bool value) => state = value;
}

final splashFinishedProvider =
    NotifierProvider<SplashFinishedNotifier, bool>(
      SplashFinishedNotifier.new,
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
    _ref.listen<bool>(
      splashFinishedProvider,
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
        builder: (context, state) => SplashPage(
          onFinished: () {
            ref.read(splashFinishedProvider.notifier).setFinished(true);

            final isOnboardingCompleted = ref.read(onboardingStatusProvider);
            final authState = ref.read(authNotifierProvider);

            if (!isOnboardingCompleted) {
              context.go('/onboarding');
              return;
            }

            if (authState.isAuthenticated) {
              final user = authState.user;
              if (user?.isEmailVerified == false) {
                final emailParam = user?.email != null
                    ? '?email=${Uri.encodeComponent(user!.email)}'
                    : '';
                context.go('/verify-email$emailParam');
              } else {
                context.go('/home');
              }
              return;
            }

            context.go('/login');
          },
        ),
      ),
      GoRoute(
        path: '/onboarding',
        name: 'onboarding',
        builder: (context, state) =>
            OnboardingPage(repository: onboardingRepository),
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
        path: '/forgot-password',
        name: 'forgot-password',
        builder: (context, state) => const ForgotPasswordPage(),
      ),
      GoRoute(
        path: '/reset-password',
        name: 'reset-password',
        builder: (context, state) {
          final token = state.uri.queryParameters['token'] ??
              (state.extra is Map ? (state.extra as Map)['token'] as String? : null);
          final email = state.uri.queryParameters['email'] ??
              (state.extra is Map ? (state.extra as Map)['email'] as String? : null);
          return ResetPasswordPage(
            initialToken: token,
            initialEmail: email,
          );
        },
      ),
      GoRoute(
        path: '/verify-email',
        name: 'verify-email',
        builder: (context, state) {
          final email = state.uri.queryParameters['email'] ??
              (state.extra is String ? state.extra as String : null);
          final status = state.uri.queryParameters['status'];
          return EmailVerificationPage(
            email: email,
            status: status,
          );
        },
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
        path: '/community-complaints',
        name: 'community-complaints',
        builder: (context, state) => const CommunityComplaintsPage(),
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
      GoRoute(
        path: '/projects',
        name: 'projects',
        builder: (context, state) => const ProjectsListPage(),
      ),
      GoRoute(
        path: '/projects/:id',
        name: 'project-details',
        builder: (context, state) {
          final idParam = state.pathParameters['id'] ?? '';
          final id = int.tryParse(idParam) ?? 0;
          final extra = state.extra;
          final initialProject = extra is ProjectEntity ? extra : null;

          return ProjectDetailsPage(
            projectId: id,
            initialProject: initialProject,
          );
        },
      ),
      GoRoute(
        path: '/tasks',
        name: 'tasks',
        builder: (context, state) => const TasksListPage(),
      ),
      GoRoute(
        path: '/tasks/:id',
        name: 'task-details',
        builder: (context, state) {
          final idParam = state.pathParameters['id'] ?? '';
          final id = int.tryParse(idParam) ?? 0;
          final extra = state.extra;
          final initialAssignment = extra is FieldWorkAssignmentEntity
              ? extra
              : null;
          return TaskDetailsPage(
            assignmentId: id,
            initialAssignment: initialAssignment,
          );
        },
      ),
      GoRoute(
        path: '/tasks/:id/execute',
        name: 'task-execution',
        builder: (context, state) {
          final idParam = state.pathParameters['id'] ?? '';
          final id = int.tryParse(idParam) ?? 0;
          final extra = state.extra;
          final initialAssignment = extra is FieldWorkAssignmentEntity
              ? extra
              : null;
          return TaskExecutionPage(
            assignmentId: id,
            initialAssignment: initialAssignment,
          );
        },
      ),
      GoRoute(
        path: '/map',
        name: 'map',
        builder: (context, state) => const MapPage(),
      ),
      GoRoute(
        path: '/notifications',
        name: 'notifications',
        builder: (context, state) => const NotificationsPage(),
      ),
      GoRoute(
        path: '/profile',
        name: 'profile',
        builder: (context, state) => const ProfilePage(),
      ),
      GoRoute(
        path: '/profile/edit',
        name: 'edit-profile',
        builder: (context, state) => const EditProfilePage(),
      ),
    ],
    redirect: (BuildContext context, GoRouterState state) {
      final authState = ref.read(authNotifierProvider);
      final isOnboardingCompleted = ref.read(onboardingStatusProvider);

      final uri = state.uri;
      final isCustomScheme = uri.scheme == 'bader';

      // 0. Handle custom scheme deep links (bader://)
      // When Android receives bader://reset-password?token=...
      // RFC 3986 sets uri.host = 'reset-password' and uri.path = ''.
      // We normalize this to GoRouter's path '/reset-password'.
      if (isCustomScheme) {
        final isReset = uri.host == 'reset-password' ||
            uri.path == '/reset-password' ||
            uri.path.contains('reset-password');
        if (isReset && state.matchedLocation != '/reset-password') {
          final query = uri.query;
          return query.isNotEmpty ? '/reset-password?$query' : '/reset-password';
        }

        final isVerify = uri.host == 'verify-email' ||
            uri.path == '/verify-email' ||
            uri.path.contains('verify-email');
        if (isVerify && state.matchedLocation != '/verify-email') {
          final query = uri.query;
          return query.isNotEmpty ? '/verify-email?$query' : '/verify-email';
        }
      }

      final isSplash = state.matchedLocation == '/splash';
      final isSplashFinished = ref.read(splashFinishedProvider);
      final isLogin = state.matchedLocation == '/login';
      final isRegister = state.matchedLocation == '/register';
      final isForgotPassword = state.matchedLocation == '/forgot-password';
      final isResetPassword = state.matchedLocation == '/reset-password';
      final isVerifyEmail = state.matchedLocation == '/verify-email';
      final isOnboarding = state.matchedLocation == '/onboarding';

      // 0. Stay on splash until its 3-second display and animation completes
      if (isSplash && !isSplashFinished) {
        return null;
      }

      // 1. If onboarding is not completed, force /onboarding EXCEPT for password reset and verify email
      if (!isOnboardingCompleted) {
        if (isResetPassword || isVerifyEmail) {
          return null;
        }
        return isOnboarding ? null : '/onboarding';
      }

      // If on onboarding but it is completed, redirect according to auth
      if (isOnboarding) {
        if (authState.isAuthenticated) return '/home';
        if (authState.isInitial) return '/splash';
        return '/login';
      }

      // 2. While checking auth session (initial state), stay on splash to avoid flicker,
      // EXCEPT when the user is explicitly on reset-password, verify-email, forgot-password, login or register!
      if (authState.isInitial) {
        if (isResetPassword || isVerifyEmail || isForgotPassword || isLogin || isRegister) {
          return null;
        }
        return isSplash ? null : '/splash';
      }

      // 3. User is authenticated
      if (authState.isAuthenticated) {
        final user = authState.user;
        final isFieldWorker = user?.isFieldWorker == true;
        final isTasksRoute = state.matchedLocation.startsWith('/tasks');

        // Enforce email verification: unverified users must be directed to /verify-email
        final isEmailVerified = user?.isEmailVerified ?? false;
        if (!isEmailVerified) {
          if (isVerifyEmail) {
            return null;
          }
          final emailParam = user?.email != null
              ? '?email=${Uri.encodeComponent(user!.email)}'
              : '';
          return '/verify-email$emailParam';
        }

        // Citizen cannot access field worker routes
        if (!isFieldWorker && isTasksRoute) {
          return '/home';
        }

        if (isLogin || isRegister || isSplash || isVerifyEmail) {
          return '/home';
        }
        return null;
      }

      // 4. User is unauthenticated or has error
      if (authState.isUnauthenticated || authState.isError) {
        if (!isLogin &&
            !isRegister &&
            !isForgotPassword &&
            !isResetPassword &&
            !isVerifyEmail) {
          return '/login';
        }
        return null;
      }

      return null;
    },
  );
});
