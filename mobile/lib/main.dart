import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'core/router/app_router.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/application/providers.dart';
import 'features/onboarding/application/onboarding_provider.dart';
import 'features/onboarding/data/datasources/onboarding_local_data_source.dart';
import 'features/onboarding/data/repositories/onboarding_repository_impl.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize SharedPreferences
  final prefs = await SharedPreferences.getInstance();
  final localDataSource = OnboardingLocalDataSourceImpl(prefs);
  final onboardingRepository = OnboardingRepositoryImpl(localDataSource);

  // Check initial onboarding status
  final isCompleted = await onboardingRepository.isOnboardingCompleted();

  runApp(
    ProviderScope(
      overrides: [
        sharedPreferencesProvider.overrideWithValue(prefs),
      ],
      child: BaderApp(isOnboardingCompleted: isCompleted),
    ),
  );
}

class BaderApp extends ConsumerStatefulWidget {
  final bool isOnboardingCompleted;

  const BaderApp({
    super.key,
    required this.isOnboardingCompleted,
  });

  @override
  ConsumerState<BaderApp> createState() => _BaderAppState();
}

class _BaderAppState extends ConsumerState<BaderApp> {
  @override
  void initState() {
    super.initState();
    // Initialize Onboarding state and run auth session check
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref
          .read(onboardingStatusProvider.notifier)
          .setCompleted(widget.isOnboardingCompleted);

      // Perform non-blocking session check to restore token
      ref.read(authNotifierProvider.notifier).checkAuth();
    });
  }

  @override
  Widget build(BuildContext context) {
    final router = ref.watch(appRouterProvider);

    return MaterialApp.router(
      title: 'بادر',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      routerConfig: router,
      // Ensure RTL text direction for Arabic UI
      builder: (context, child) {
        return Directionality(
          textDirection: TextDirection.rtl,
          child: child ?? const SizedBox.shrink(),
        );
      },
    );
  }
}
