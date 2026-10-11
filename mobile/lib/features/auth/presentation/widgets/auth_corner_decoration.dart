import 'dart:ui' as ui;

import 'package:flutter/material.dart';

import '../../../../core/constants/app_colors.dart';

/// Decorative corner ribbon in Yemen national flag colors (Red, White, Black)
/// positioned at the bottom-left corner of auth screens matching the design mockup.
///
/// Logic:
/// - Three continuous, overlapping sweeping ribbons following the national flag order.
/// - Added S-curve Bézier paths to simulate a realistic waving fabric effect.
/// - Integrated linear gradients and layered shadows for a 3D professional look.
/// - The corner tip itself remains white/transparent.
class AuthCornerDecoration extends StatelessWidget {
  final double width;
  final double height;

  const AuthCornerDecoration({
    super.key,
    this.width = 130.0,
    this.height = 100.0,
  });

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: ClipRect(
        child: SizedBox(
          width: width,
          height: height,
          child: CustomPaint(
            size: Size(width, height),
            painter: _YemenCornerWavePainter(),
          ),
        ),
      ),
    );
  }
}

class _YemenCornerWavePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;

    // -------------------------------------------------------------
    // 3D Gradients to simulate fabric folds and lighting
    // -------------------------------------------------------------

    final blackGradient = ui.Gradient.linear(
      Offset(0, h * 0.5),
      Offset(w, h),
      [
        AppColors.yemenBlack,
        const Color(0xFF2A2A2A), // Highlight on the fold
        AppColors.yemenBlack,
      ],
      [0.0, 0.5, 1.0],
    );

    final whiteGradient = ui.Gradient.linear(
      Offset(0, h * 0.3),
      Offset(w, h),
      [
        Colors.white,
        const Color(0xFFE8E8E8), // Shadow in the fabric fold
        Colors.white,
      ],
      [0.0, 0.5, 1.0],
    );

    final redGradient = ui.Gradient.linear(Offset(0, h * 0.1), Offset(w, h), [
      AppColors.yemenRed,
      const Color(0xFFA00D1C), // Deeper red for depth
    ]);

    // -------------------------------------------------------------
    // Path Definitions (Overlapping to prevent 1px rendering gaps)
    // Drawn from Back (Black) to Front (Red)
    // -------------------------------------------------------------

    // 1. Black ribbon (Bottom stripe)
    final blackPaint = Paint()
      ..shader = blackGradient
      ..style = PaintingStyle.fill
      ..isAntiAlias = true;

    final blackPath = Path()
      // Starts slightly higher to hide seamlessly under the white layer
      ..moveTo(0, h * 0.53)
      // S-Curve for the waving effect
      ..cubicTo(w * 0.35, h * 0.33, w * 0.55, h * 1.05, w * 0.77, h)
      ..lineTo(w * 0.55, h)
      ..cubicTo(w * 0.45, h * 1.15, w * 0.35, h * 0.55, 0, h * 0.75)
      ..close();

    // Base shadow under the whole flag
    canvas.drawShadow(
      blackPath,
      Colors.black.withValues(alpha: 0.15),
      6.0,
      false,
    );
    canvas.drawPath(blackPath, blackPaint);

    // 2. White ribbon (Middle stripe)
    final whitePaint = Paint()
      ..shader = whiteGradient
      ..style = PaintingStyle.fill
      ..isAntiAlias = true;

    final whitePath = Path()
      // Starts slightly higher to hide seamlessly under the red layer
      ..moveTo(0, h * 0.33)
      ..cubicTo(w * 0.35, h * 0.13, w * 0.55, h * 0.85, w * 0.97, h)
      ..lineTo(w * 0.75, h)
      // Matches the true top edge of the Black path exactly
      ..cubicTo(w * 0.55, h * 1.05, w * 0.35, h * 0.35, 0, h * 0.55)
      ..close();

    // Shadow cast BY the white layer ONTO the black layer (Fabric layering)
    canvas.drawShadow(
      whitePath,
      Colors.black.withValues(alpha: 0.08),
      3.0,
      false,
    );
    canvas.drawPath(whitePath, whitePaint);

    // Subtle edge outline for the white ribbon to pop against white backgrounds
    final whiteBorderPaint = Paint()
      ..color = const Color(0x15000000)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 0.5
      ..isAntiAlias = true;
    canvas.drawPath(whitePath, whiteBorderPaint);

    // 3. Red ribbon (Top stripe)
    final redPaint = Paint()
      ..shader = redGradient
      ..style = PaintingStyle.fill
      ..isAntiAlias = true;

    final redPath = Path()
      ..moveTo(0, h * 0.15)
      // Majestic S-Curve sweeping down
      ..cubicTo(w * 0.35, -h * 0.05, w * 0.55, h * 0.65, w * 1.05, h * 0.85)
      ..lineTo(w * 1.05, h)
      ..lineTo(w * 0.95, h)
      // Matches the true top edge of the White path exactly
      ..cubicTo(w * 0.55, h * 0.85, w * 0.35, h * 0.15, 0, h * 0.35)
      ..close();

    // Shadow cast BY the red layer ONTO the white layer
    canvas.drawShadow(
      redPath,
      Colors.black.withValues(alpha: 0.12),
      4.0,
      false,
    );
    canvas.drawPath(redPath, redPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
