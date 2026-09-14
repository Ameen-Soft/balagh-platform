import 'package:shared_preferences/shared_preferences.dart';

/// Abstract contract for local data source
abstract class OnboardingLocalDataSource {
  Future<bool> isOnboardingCompleted();
  Future<void> setOnboardingCompleted(bool completed);
  Future<void> clear();
}

/// Implementation using SharedPreferences (Data Layer)
class OnboardingLocalDataSourceImpl implements OnboardingLocalDataSource {
  static const String keyOnboardingCompleted = 'onboarding_completed';

  final SharedPreferences _prefs;

  OnboardingLocalDataSourceImpl(this._prefs);

  @override
  Future<bool> isOnboardingCompleted() async {
    return _prefs.getBool(keyOnboardingCompleted) ?? false;
  }

  @override
  Future<void> setOnboardingCompleted(bool completed) async {
    await _prefs.setBool(keyOnboardingCompleted, completed);
  }

  @override
  Future<void> clear() async {
    await _prefs.remove(keyOnboardingCompleted);
  }
}
