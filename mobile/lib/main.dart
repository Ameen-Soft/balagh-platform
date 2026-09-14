import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/core/router/app_router.dart';
import 'package:mobile/core/theme/app_theme.dart';
import 'package:mobile/features/onboarding/data/datasources/onboarding_local_data_source.dart';
import 'package:mobile/features/onboarding/data/repositories/onboarding_repository_impl.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize SharedPreferences and Clean Architecture layers
  final prefs = await SharedPreferences.getInstance();
  final localDataSource = OnboardingLocalDataSourceImpl(prefs);
  final onboardingRepository = OnboardingRepositoryImpl(localDataSource);

  // Check if onboarding was previously completed
  final isCompleted = await onboardingRepository.isOnboardingCompleted();

  // Create GoRouter with the initial location based on onboarding state
  final router = createAppRouter(
    isOnboardingCompleted: isCompleted,
    onboardingRepository: onboardingRepository,
  );

  runApp(BaderApp(router: router));
}

class BaderApp extends StatelessWidget {
  final GoRouter router;

  const BaderApp({super.key, required this.router});

  @override
  Widget build(BuildContext context) {
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
