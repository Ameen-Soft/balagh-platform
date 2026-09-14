import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/onboarding/data/datasources/onboarding_local_data_source.dart';
import 'package:mobile/features/onboarding/data/repositories/onboarding_repository_impl.dart';
import 'package:mobile/features/onboarding/domain/entities/onboarding_item.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('Onboarding Unit Tests', () {
    test('OnboardingItem should contain exactly 3 items with proper Arabic texts', () {
      expect(OnboardingItem.items.length, 3);
      expect(OnboardingItem.items[0].title, 'صوتك يبني وطناً.');
      expect(OnboardingItem.items[1].title, 'رصد ميداني ذكي:');
      expect(OnboardingItem.items[2].title, 'شراكة تصنع الأثر:');
    });

    test('OnboardingRepository default state should be not completed (false)', () async {
      SharedPreferences.setMockInitialValues({});
      final prefs = await SharedPreferences.getInstance();
      final dataSource = OnboardingLocalDataSourceImpl(prefs);
      final repository = OnboardingRepositoryImpl(dataSource);

      final isCompleted = await repository.isOnboardingCompleted();
      expect(isCompleted, false);
    });

    test('completeOnboarding should set state to completed (true)', () async {
      SharedPreferences.setMockInitialValues({});
      final prefs = await SharedPreferences.getInstance();
      final dataSource = OnboardingLocalDataSourceImpl(prefs);
      final repository = OnboardingRepositoryImpl(dataSource);

      await repository.completeOnboarding();
      final isCompleted = await repository.isOnboardingCompleted();
      expect(isCompleted, true);
    });

    test('resetOnboarding should reset state back to false', () async {
      SharedPreferences.setMockInitialValues({'onboarding_completed': true});
      final prefs = await SharedPreferences.getInstance();
      final dataSource = OnboardingLocalDataSourceImpl(prefs);
      final repository = OnboardingRepositoryImpl(dataSource);

      expect(await repository.isOnboardingCompleted(), true);
      await repository.resetOnboarding();
      expect(await repository.isOnboardingCompleted(), false);
    });
  });
}
