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

    // If successfully submitted, render the Success Confirmation screen
    if (state.isSuccess) {
      return _buildSuccessScreen(context, state, notifier);
    }

    return Scaffold(
      backgroundColor: AppColors.backgroundSubtle,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded,
              color: AppColors.yemenBlack, size: 20),
          onPressed: () {
            if (state.currentStep > 0) {
              notifier.prevStep();
            } else {
              context.pop();
            }
          },
        ),
        title: const Text(
          'تقديم بلاغ جديد',
          style: TextStyle(
            color: AppColors.yemenBlack,
            fontWeight: FontWeight.w800,
            fontSize: 18,
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
              style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
            ),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Progress Indicator Bar
            _buildStepperHeader(state.currentStep),

            // Error Banner if submission failed
            if (state.errorMessage != null)
              Container(
                margin: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: AppColors.yemenRedLight,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.yemenRedTint),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline_rounded,
                        color: AppColors.yemenRed, size: 18),
                    const SizedBox(width: 10),
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

            // Step Content
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(20),
                child: _buildCurrentStep(state, notifier),
              ),
            ),

            // Bottom Navigation Actions
            _buildBottomBar(state, notifier),
          ],
        ),
      ),
    );
  }

  Widget _buildStepperHeader(int currentStep) {
    final steps = [
      'التصنيف',
      'التفاصيل',
      'التوثيق',
      'المراجعة',
    ];

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20),
      child: Column(
        children: [
          Row(
            children: List.generate(steps.length * 2 - 1, (index) {
              if (index.isOdd) {
                final stepIndex = index ~/ 2;
                final isPassed = currentStep > stepIndex;
                return Expanded(
                  child: Container(
                    height: 2.5,
                    color: isPassed
                        ? AppColors.yemenRed
                        : AppColors.dotInactive,
                  ),
                );
              }

              final stepIndex = index ~/ 2;
              final isActive = currentStep == stepIndex;
              final isDone = currentStep > stepIndex;

              return Column(
                children: [
                  Container(
                    width: 28,
                    height: 28,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: isDone
                          ? AppColors.yemenRed
                          : (isActive ? AppColors.yemenBlack : Colors.white),
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: isDone || isActive
                            ? Colors.transparent
                            : AppColors.borderSubtle,
                        width: 1.5,
                      ),
                    ),
                    child: isDone
                        ? const Icon(Icons.check_rounded,
                            size: 16, color: Colors.white)
                        : Text(
                            '${stepIndex + 1}',
                            style: TextStyle(
                              color: isActive
                                  ? Colors.white
                                  : AppColors.textSecondary,
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
                          ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    steps[stepIndex],
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight:
                          isActive ? FontWeight.w800 : FontWeight.w500,
                      color: isActive
                          ? AppColors.yemenBlack
                          : AppColors.textSecondary,
                    ),
                  ),
                ],
              );
            }),
          ),
        ],
      ),
    );
  }

  Widget _buildCurrentStep(
    dynamic state,
    dynamic notifier,
  ) {
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
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text(
          'تفاصيل موضوع البلاغ',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w800,
            color: AppColors.yemenBlack,
          ),
        ),
        const SizedBox(height: 4),
        const Text(
          'يرجى صياغة عنوان موجز ووصف دقيق لطبيعة المشكلة لتسهيل التقييم',
          style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
        ),
        const SizedBox(height: 20),

        // Title Input
        const Text(
          'عنوان البلاغ *',
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
          decoration: InputDecoration(
            hintText: 'مثال: هبوط إسفلتي مفاجئ يقطع حركة السير',
            hintStyle:
                const TextStyle(color: AppColors.textMuted, fontSize: 13),
            filled: true,
            fillColor: Colors.white,
            counterText: '',
            prefixIcon: const Icon(Icons.title_rounded,
                color: AppColors.textSecondary, size: 20),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: AppColors.borderSubtle),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: AppColors.borderSubtle),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide:
                  const BorderSide(color: AppColors.yemenRed, width: 1.5),
            ),
          ),
          onChanged: (val) => notifier.setTitle(val),
        ),

        const SizedBox(height: 20),

        // Description Input
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'وصف المشكلة *',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: AppColors.yemenBlack,
              ),
            ),
            Text(
              'الحد الأدنى: 10 أحرف (${_descController.text.trim().length})',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: _descController.text.trim().length >= 10
                    ? AppColors.yemenEmerald
                    : AppColors.textMuted,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        TextField(
          controller: _descController,
          maxLines: 5,
          maxLength: 1000,
          decoration: InputDecoration(
            hintText:
                'اشرح بالتفصيل موقع المشكلة، أثرها على المارة، متى ظهرت، وأي تفاصيل أخرى تساعد الفرق الميدانية...',
            hintStyle:
                const TextStyle(color: AppColors.textMuted, fontSize: 13),
            filled: true,
            fillColor: Colors.white,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: AppColors.borderSubtle),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: AppColors.borderSubtle),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide:
                  const BorderSide(color: AppColors.yemenRed, width: 1.5),
            ),
          ),
          onChanged: (val) {
            setState(() {});
            notifier.setDescription(val);
          },
        ),

        const SizedBox(height: 20),

        // Priority Selection
        const Text(
          'درجة الاستعجال / الأولوية',
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w700,
            color: AppColors.yemenBlack,
          ),
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            _buildPriorityChip('عادي', 'medium', state.priority, notifier),
            const SizedBox(width: 8),
            _buildPriorityChip('عاجل', 'high', state.priority, notifier),
            const SizedBox(width: 8),
            _buildPriorityChip('طارئ جداً', 'urgent', state.priority, notifier),
            const SizedBox(width: 8),
            _buildPriorityChip('منخفض', 'low', state.priority, notifier),
          ],
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
      child: InkWell(
        onTap: () => notifier.setPriority(value),
        borderRadius: BorderRadius.circular(10),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.yemenRed : Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: isSelected ? AppColors.yemenRed : AppColors.borderSubtle,
            ),
          ),
          child: Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 12,
              fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
              color: isSelected ? Colors.white : AppColors.textPrimary,
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
        SizedBox(height: 20),
        LocationCaptureCard(),
      ],
    );
  }

  Widget _buildBottomBar(dynamic state, dynamic notifier) {
    bool canProceed = false;
    String actionLabel = 'التالي';

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
        actionLabel = 'إرسال البلاغ رسمياً';
        break;
    }

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          if (state.currentStep > 0) ...[
            OutlinedButton(
              onPressed: state.isSubmitting ? null : () => notifier.prevStep(),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.yemenBlack,
                side: const BorderSide(color: AppColors.borderSubtle),
                padding:
                    const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: const Text('السابق'),
            ),
            const SizedBox(width: 12),
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
                disabledBackgroundColor:
                    AppColors.yemenRed.withValues(alpha: 0.35),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                elevation: 0,
              ),
              child: state.isSubmitting
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          actionLabel,
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        if (state.currentStep < 3) ...[
                          const SizedBox(width: 8),
                          const Icon(Icons.arrow_forward_rounded, size: 18),
                        ],
                      ],
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
          padding: const EdgeInsets.all(28.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Spacer(),
              // Success Icon
              Center(
                child: Container(
                  width: 90,
                  height: 90,
                  decoration: BoxDecoration(
                    color: AppColors.yemenEmeraldLight,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.yemenEmerald.withValues(alpha: 0.15),
                        blurRadius: 20,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.check_circle_rounded,
                    size: 54,
                    color: AppColors.yemenEmerald,
                  ),
                ),
              ),
              const SizedBox(height: 24),
              const Text(
                'تم تسجيل بلاغك بنجاح!',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w900,
                  color: AppColors.yemenBlack,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'تم توجيه البلاغ بصورة آلية إلى القسم المختص بالوزارة لمراجعته وإسناده لفرق المعاينة الميدانية.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  color: AppColors.textSecondary,
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 28),

              // Ticket Number Card
              if (complaint != null)
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: AppColors.backgroundSubtle,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.borderSubtle),
                  ),
                  child: Column(
                    children: [
                      const Text(
                        'رقم مرجع البلاغ الرسمي',
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textSecondary,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        complaint.complaintNumber,
                        style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w900,
                          color: AppColors.yemenRed,
                          letterSpacing: 1.2,
                        ),
                      ),
                    ],
                  ),
                ),

              const Spacer(),

              // Action Buttons
              ElevatedButton.icon(
                onPressed: () {
                  notifier.reset();
                  context.go('/my-complaints');
                },
                icon: const Icon(Icons.list_alt_rounded, size: 20),
                label: const Text('متابعة بلاغاتي'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.yemenBlack,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                  elevation: 0,
                ),
              ),
              const SizedBox(height: 12),
              OutlinedButton(
                onPressed: () {
                  notifier.reset();
                  context.go('/home');
                },
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.yemenBlack,
                  side: const BorderSide(color: AppColors.borderSubtle),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                child: const Text('العودة للشاشة الرئيسية'),
              ),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }
}
