import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/constants/app_colors.dart';
import '../../application/complaints_providers.dart';
import '../widgets/camera_capture_card.dart';
import '../widgets/category_selector.dart';
import '../widgets/complaint_review_card.dart';
import '../widgets/location_capture_card.dart';

class CreateComplaintPage extends ConsumerStatefulWidget {
  const CreateComplaintPage({super.key});

  @override
  ConsumerState<CreateComplaintPage> createState() =>
      _CreateComplaintPageState();
}

class _CreateComplaintPageState extends ConsumerState<CreateComplaintPage> {
  late final TextEditingController _titleController;
  late final TextEditingController _descController;

  @override
  void initState() {
    super.initState();
    final state = ref.read(createComplaintNotifierProvider);
    _titleController = TextEditingController(text: state.title);
    _descController = TextEditingController(text: state.description);
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(createComplaintNotifierProvider);
    final notifier = ref.read(createComplaintNotifierProvider.notifier);

    if (state.isSuccess) {
      return _buildSuccessScreen(context, state, notifier);
    }

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0.5,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back_ios_new_rounded,
            color: AppColors.yemenBlack,
            size: 18,
          ),
          onPressed: () {
            if (state.currentStep > 0) {
              notifier.prevStep();
            } else {
              context.pop();
            }
          },
        ),
        title: const Text(
          'بلاغ جديد',
          style: TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 16.5,
          ),
        ),
        actions: [
          TextButton(
            onPressed: () {
              notifier.reset();
              context.pop();
            },
            child: const Text(
              'إلغاء',
              style: TextStyle(
                color: AppColors.textSecondary,
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          const SizedBox(width: 6),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Minimalist Stepper Header
            _buildStepperHeader(state.currentStep),

            // Subtle Error Banner
            if (state.errorMessage != null)
              Container(
                margin: const EdgeInsets.fromLTRB(20, 12, 20, 0),
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: AppColors.yemenRedLight,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: AppColors.yemenRed.withValues(alpha: 0.2),
                    width: 0.8,
                  ),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.info_outline_rounded,
                      color: AppColors.yemenRed,
                      size: 16,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        state.errorMessage!,
                        style: const TextStyle(
                          color: AppColors.yemenRed,
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

            // Animated Step Content
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 18,
                ),
                child: AnimatedSwitcher(
                  duration: const Duration(milliseconds: 220),
                  child: KeyedSubtree(
                    key: ValueKey<int>(state.currentStep),
                    child: _buildCurrentStep(state, notifier),
                  ),
                ),
              ),
            ),

            // Sleek Bottom Action Bar
            _buildBottomBar(state, notifier),
          ],
        ),
      ),
    );
  }

  Widget _buildStepperHeader(int currentStep) {
    final steps = ['التصنيف', 'التفاصيل', 'التوثيق', 'المراجعة'];

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(
          bottom: BorderSide(
            color: AppColors.borderSubtle.withValues(alpha: 0.7),
            width: 0.8,
          ),
        ),
      ),
      padding: const EdgeInsets.fromLTRB(24, 10, 24, 14),
      child: Row(
        children: List.generate(steps.length * 2 - 1, (index) {
          if (index.isOdd) {
            final stepIndex = index ~/ 2;
            final isPassed = currentStep > stepIndex;
            return Expanded(
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 250),
                height: 1.5,
                margin: const EdgeInsets.only(bottom: 18, left: 6, right: 6),
                color: isPassed ? AppColors.yemenRed : AppColors.borderSubtle,
              ),
            );
          }

          final stepIndex = index ~/ 2;
          final isActive = currentStep == stepIndex;
          final isDone = currentStep > stepIndex;

          return Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              AnimatedContainer(
                duration: const Duration(milliseconds: 250),
                width: 24,
                height: 24,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: isDone
                      ? AppColors.yemenRed
                      : (isActive ? AppColors.yemenBlack : Colors.white),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: isDone
                        ? AppColors.yemenRed
                        : (isActive
                              ? AppColors.yemenBlack
                              : AppColors.borderSubtle),
                    width: 1.0,
                  ),
                ),
                child: isDone
                    ? const Icon(
                        Icons.check_rounded,
                        size: 14,
                        color: Colors.white,
                      )
                    : Text(
                        '${stepIndex + 1}',
                        style: TextStyle(
                          color: isActive
                              ? Colors.white
                              : AppColors.textSecondary,
                          fontWeight: FontWeight.w700,
                          fontSize: 11,
                        ),
                      ),
              ),
              const SizedBox(height: 6),
              Text(
                steps[stepIndex],
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: isActive ? FontWeight.w800 : FontWeight.w500,
                  color: isActive
                      ? AppColors.yemenBlack
                      : AppColors.textSecondary,
                ),
              ),
            ],
          );
        }),
      ),
    );
  }

  Widget _buildCurrentStep(dynamic state, dynamic notifier) {
    switch (state.currentStep) {
      case 0:
        return const CategorySelector();
      case 1:
        return _buildDetailsStep(state, notifier);
      case 2:
        return _buildEvidenceStep();
      case 3:
      default:
        return const ComplaintReviewCard();
    }
  }

  Widget _buildDetailsStep(dynamic state, dynamic notifier) {
    final descLength = _descController.text.trim().length;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Main Inputs Card
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.borderSubtle, width: 0.8),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.015),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'عنوان البلاغ',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: AppColors.yemenBlack,
                ),
              ),
              const SizedBox(height: 8),
              TextField(
                controller: _titleController,
                maxLength: 150,
                style: const TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w600,
                  color: AppColors.yemenBlack,
                ),
                decoration: InputDecoration(
                  hintText: 'مثال: هبوط إسفلتي في الشارع العام',
                  hintStyle: const TextStyle(
                    color: AppColors.textMuted,
                    fontSize: 12.5,
                    fontWeight: FontWeight.normal,
                  ),
                  filled: true,
                  fillColor: AppColors.backgroundSubtle,
                  counterText: '',
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 13,
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(
                      color: AppColors.borderSubtle,
                      width: 0.8,
                    ),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(
                      color: AppColors.borderSubtle,
                      width: 0.8,
                    ),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(
                      color: AppColors.yemenRed,
                      width: 1.0,
                    ),
                  ),
                ),
                onChanged: (val) => notifier.setTitle(val),
              ),
              const SizedBox(height: 18),

              // Description Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'وصف المشكلة',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: AppColors.yemenBlack,
                    ),
                  ),
                  Text(
                    '$descLength / 10 أحرف كحد أدنى',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: descLength >= 10
                          ? AppColors.yemenEmerald
                          : AppColors.textMuted,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              TextField(
                controller: _descController,
                maxLines: 4,
                maxLength: 1000,
                style: const TextStyle(
                  fontSize: 13.5,
                  height: 1.5,
                  color: AppColors.yemenBlack,
                ),
                decoration: InputDecoration(
                  hintText: 'اكتب وصفاً مختصراً يوضح طبيعة المشكلة وموقعها...',
                  hintStyle: const TextStyle(
                    color: AppColors.textMuted,
                    fontSize: 12.5,
                  ),
                  filled: true,
                  fillColor: AppColors.backgroundSubtle,
                  counterText: '',
                  contentPadding: const EdgeInsets.all(14),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(
                      color: AppColors.borderSubtle,
                      width: 0.8,
                    ),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(
                      color: AppColors.borderSubtle,
                      width: 0.8,
                    ),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(
                      color: AppColors.yemenRed,
                      width: 1.0,
                    ),
                  ),
                ),
                onChanged: (val) {
                  setState(() {});
                  notifier.setDescription(val);
                },
              ),
            ],
          ),
        ),

        const SizedBox(height: 14),

        // Priority Selector Card
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.borderSubtle, width: 0.8),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'درجة الأهمية',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: AppColors.yemenBlack,
                ),
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  _buildPriorityChip('منخفض', 'low', state.priority, notifier),
                  const SizedBox(width: 8),
                  _buildPriorityChip(
                    'عادي',
                    'medium',
                    state.priority,
                    notifier,
                  ),
                  const SizedBox(width: 8),
                  _buildPriorityChip('عاجل', 'high', state.priority, notifier),
                  const SizedBox(width: 8),
                  _buildPriorityChip(
                    'طارئ',
                    'urgent',
                    state.priority,
                    notifier,
                  ),
                ],
              ),
            ],
          ),
        ),

        const SizedBox(height: 14),

        // Clean Community Switch Card
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.borderSubtle, width: 0.8),
          ),
          child: Row(
            children: [
              Icon(
                Icons.public_rounded,
                size: 20,
                color: state.isPublic
                    ? AppColors.yemenRed
                    : AppColors.textSecondary,
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'عرض البلاغ في الخريطة العامة',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: AppColors.yemenBlack,
                      ),
                    ),
                    SizedBox(height: 2),
                    Text(
                      'تبقى بياناتك الشخصية سرية بالكامل',
                      style: TextStyle(
                        fontSize: 11,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
              Switch.adaptive(
                value: state.isPublic,
                activeTrackColor: AppColors.yemenRed,
                onChanged: (val) => notifier.setIsPublic(val),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildPriorityChip(
    String label,
    String value,
    String current,
    dynamic notifier,
  ) {
    final isSelected = current == value;
    return Expanded(
      child: GestureDetector(
        onTap: () => notifier.setPriority(value),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          padding: const EdgeInsets.symmetric(vertical: 9),
          decoration: BoxDecoration(
            color: isSelected
                ? AppColors.yemenBlack
                : AppColors.backgroundSubtle,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: isSelected ? AppColors.yemenBlack : AppColors.borderSubtle,
              width: 0.8,
            ),
          ),
          child: Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 12,
              fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
              color: isSelected ? Colors.white : AppColors.textSecondary,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildEvidenceStep() {
    return const Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        CameraCaptureCard(),
        SizedBox(height: 14),
        LocationCaptureCard(),
      ],
    );
  }

  Widget _buildBottomBar(dynamic state, dynamic notifier) {
    bool canProceed = false;
    String actionLabel = 'المتابعة';

    switch (state.currentStep) {
      case 0:
        canProceed = state.canProceedFromCategoryStep;
        break;
      case 1:
        canProceed = state.canProceedFromDetailsStep;
        break;
      case 2:
        canProceed = state.canProceedFromEvidenceStep;
        break;
      case 3:
        canProceed = state.isReadyToSubmit;
        actionLabel = 'اعتماد وإرسال البلاغ';
        break;
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(
          top: BorderSide(
            color: AppColors.borderSubtle.withValues(alpha: 0.7),
            width: 0.8,
          ),
        ),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
      child: Row(
        children: [
          if (state.currentStep > 0) ...[
            OutlinedButton(
              onPressed: state.isSubmitting ? null : () => notifier.prevStep(),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.yemenBlack,
                side: const BorderSide(
                  color: AppColors.borderSubtle,
                  width: 0.8,
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: 22,
                  vertical: 14,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: const Text(
                'السابق',
                style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600),
              ),
            ),
            const SizedBox(width: 10),
          ],
          Expanded(
            child: ElevatedButton(
              onPressed: !canProceed || state.isSubmitting
                  ? null
                  : () {
                      if (state.currentStep < 3) {
                        notifier.nextStep();
                      } else {
                        notifier.submitComplaint();
                      }
                    },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.yemenRed,
                disabledBackgroundColor: AppColors.yemenRed.withValues(
                  alpha: 0.3,
                ),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                elevation: 0,
              ),
              child: state.isSubmitting
                  ? const SizedBox(
                      height: 18,
                      width: 18,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : Text(
                      actionLabel,
                      style: const TextStyle(
                        fontSize: 14.5,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSuccessScreen(
    BuildContext context,
    dynamic state,
    dynamic notifier,
  ) {
    final complaint = state.createdComplaint;

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 28.0, vertical: 20.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Spacer(),

              // Smooth Animated Checkmark with Pulse Rings
              Center(
                child: TweenAnimationBuilder<double>(
                  tween: Tween(begin: 0.0, end: 1.0),
                  duration: const Duration(milliseconds: 850),
                  curve: Curves.elasticOut,
                  builder: (context, value, child) {
                    return Transform.scale(
                      scale: value,
                      child: Container(
                        width: 112,
                        height: 112,
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: AppColors.yemenEmerald.withValues(alpha: 0.07),
                        ),
                        child: Container(
                          width: 82,
                          height: 82,
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: AppColors.yemenEmerald,
                            boxShadow: [
                              BoxShadow(
                                color: AppColors.yemenEmerald.withValues(
                                  alpha: 0.28,
                                ),
                                blurRadius: 24,
                                offset: const Offset(0, 8),
                              ),
                            ],
                          ),
                          child: const Icon(
                            Icons.check_rounded,
                            size: 44,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    );
                  },
                ),
              ),

              const SizedBox(height: 28),

              // Animated Text & Receipt Appearance
              TweenAnimationBuilder<double>(
                tween: Tween(begin: 0.0, end: 1.0),
                duration: const Duration(milliseconds: 600),
                curve: Curves.easeOutCubic,
                builder: (context, opacity, child) {
                  return Opacity(
                    opacity: opacity,
                    child: Transform.translate(
                      offset: Offset(0, 15 * (1 - opacity)),
                      child: child,
                    ),
                  );
                },
                child: Column(
                  children: [
                    const Text(
                      'تم استلام بلاغك بنجاح',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 21,
                        fontWeight: FontWeight.w900,
                        color: AppColors.yemenBlack,
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'تم توجيه البلاغ إلى الجهة المختصة للمراجعة والمتابعة الميدانية',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 13,
                        color: AppColors.textSecondary,
                        height: 1.5,
                      ),
                    ),
                    const SizedBox(height: 28),

                    // Luxury Minimalist Ticket Card
                    if (complaint != null)
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(
                          vertical: 20,
                          horizontal: 20,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.backgroundSubtle,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: AppColors.borderSubtle,
                            width: 0.8,
                          ),
                        ),
                        child: Column(
                          children: [
                            const Text(
                              'الرقم المرجعي للبلاغ',
                              style: TextStyle(
                                fontSize: 12,
                                color: AppColors.textSecondary,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              complaint.complaintNumber,
                              style: const TextStyle(
                                fontSize: 22,
                                fontWeight: FontWeight.w900,
                                color: AppColors.yemenBlack,
                                letterSpacing: 1.0,
                              ),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),

              const Spacer(),

              // Clean Action Buttons
              ElevatedButton(
                onPressed: () {
                  notifier.reset();
                  context.go('/my-complaints');
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.yemenBlack,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 15),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  elevation: 0,
                ),
                child: const Text(
                  'متابعة حالة البلاغ',
                  style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700),
                ),
              ),
              const SizedBox(height: 10),
              TextButton(
                onPressed: () {
                  notifier.reset();
                  context.go('/home');
                },
                style: TextButton.styleFrom(
                  foregroundColor: AppColors.textSecondary,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
                child: const Text(
                  'العودة للرئيسية',
                  style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
