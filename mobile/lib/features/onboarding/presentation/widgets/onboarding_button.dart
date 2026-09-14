import 'package:flutter/material.dart';
import 'package:mobile/core/constants/app_colors.dart';

/// Navigation action button (circular next arrow, expanding to "ابدأ الآن" on final page)
class OnboardingButton extends StatelessWidget {
  final bool isLastPage;
  final VoidCallback onPressed;

  const OnboardingButton({
    super.key,
    required this.isLastPage,
    required this.onPressed,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      curve: Curves.easeInOut,
      height: 52,
      width: isLastPage ? 138 : 52,
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        color: AppColors.yemenRed,
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: AppColors.yemenRed.withOpacity(0.35),
            offset: const Offset(0, 6),
            blurRadius: 14,
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(26),
          onTap: onPressed,
          splashColor: Colors.white.withOpacity(0.25),
          highlightColor: Colors.white.withOpacity(0.1),
          child: Center(
            child: AnimatedSwitcher(
              duration: const Duration(milliseconds: 250),
              transitionBuilder: (child, animation) {
                return FadeTransition(opacity: animation, child: child);
              },
              child: isLastPage
                  ? const Padding(
                      key: ValueKey('last_page_content'),
                      padding: EdgeInsets.symmetric(horizontal: 16.0),
                      child: FittedBox(
                        fit: BoxFit.scaleDown,
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              'ابدأ الآن',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            SizedBox(width: 8),
                            Icon(
                              Icons.arrow_forward_rounded,
                              color: Colors.white,
                              size: 20,
                            ),
                          ],
                        ),
                      ),
                    )
                  : const Icon(
                      key: ValueKey('next_page_icon'),
                      Icons.arrow_forward_rounded,
                      color: Colors.white,
                      size: 24,
                    ),
            ),
          ),
        ),
      ),
    );
  }
}
