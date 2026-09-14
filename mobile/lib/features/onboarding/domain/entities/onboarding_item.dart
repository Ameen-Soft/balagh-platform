import 'package:mobile/core/constants/app_assets.dart';

/// Entity representing one onboarding slide
class OnboardingItem {
  final String title;
  final String description;
  final String image;

  const OnboardingItem({
    required this.title,
    required this.description,
    required this.image,
  });

  /// The predefined list of 3 onboarding screens matching the design exactly
  static const List<OnboardingItem> items = [
    OnboardingItem(
      title: 'صوتك يبني وطناً.',
      description:
          'منصتك الرسمية لإيصال ملاحظاتك الخدمية بشفافية وموثوقية تامة للجهات المختصة.',
      image: AppAssets.onboarding1,
    ),
    OnboardingItem(
      title: 'رصد ميداني ذكي:',
      description:
          'التقط صوراً للمشاريع أو المخالفات، وحدد الموقع جغرافياً بضغطة زر لدعم بلاغك بالأدلة القطعية',
      image: AppAssets.onboarding2,
    ),
    OnboardingItem(
      title: 'شراكة تصنع الأثر:',
      description:
          'تابع حالة بلاغاتك خطوة بخطوة، وكن جزءاً من الرقابة المجتمعية لدعم مسيرة التنمية.',
      image: AppAssets.onboarding3,
    ),
  ];
}
