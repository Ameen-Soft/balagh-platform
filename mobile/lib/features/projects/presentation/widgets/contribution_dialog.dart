import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import '../../../../core/constants/app_colors.dart';
import '../../domain/entities/project_entity.dart';

class ContributionDialog extends StatefulWidget {
  final ProjectEntity project;
  final Future<bool> Function(double amount) onConfirmContribution;

  const ContributionDialog({
    super.key,
    required this.project,
    required this.onConfirmContribution,
  });

  static Future<bool?> show(
    BuildContext context, {
    required ProjectEntity project,
    required Future<bool> Function(double amount) onConfirmContribution,
  }) {
    return showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => ContributionDialog(
        project: project,
        onConfirmContribution: onConfirmContribution,
      ),
    );
  }

  @override
  State<ContributionDialog> createState() => _ContributionDialogState();
}

class _ContributionDialogState extends State<ContributionDialog> {
  final TextEditingController _amountController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _isReviewStep = false;
  bool _isSubmitting = false;
  String? _errorMessage;

  final List<double> _presetAmounts = [500, 1000, 2500, 5000];

  @override
  void dispose() {
    _amountController.dispose();
    super.dispose();
  }

  double? get _parsedAmount {
    final text = _amountController.text.trim().replaceAll(',', '');
    return double.tryParse(text);
  }

  void _proceedToReview() {
    setState(() {
      _errorMessage = null;
    });

    if (!_formKey.currentState!.validate()) return;

    final amount = _parsedAmount;
    if (amount == null || amount < 1) {
      setState(() {
        _errorMessage = 'الحد الأدنى للمساهمة هو 1 ريال.';
      });
      return;
    }

    setState(() {
      _isReviewStep = true;
    });
  }

  Future<void> _submitContribution() async {
    final amount = _parsedAmount;
    if (amount == null || _isSubmitting) return;

    setState(() {
      _isSubmitting = true;
      _errorMessage = null;
    });

    try {
      final success = await widget.onConfirmContribution(amount);
      if (mounted) {
        if (success) {
          Navigator.of(context).pop(true);
        } else {
          setState(() {
            _isSubmitting = false;
            _errorMessage = 'تعذر تسجيل المساهمة، يرجى المحاولة لاحقاً.';
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
          _errorMessage = e.toString().replaceFirst('Exception: ', '');
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final currencyFormatter = NumberFormat('#,##0.##', 'ar');

    return Padding(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom,
      ),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: SafeArea(
          top: false,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Drag handle
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.borderSubtle,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    _isReviewStep ? 'مراجعة وتأكيد المساهمة' : 'تسجيل مساهمة مجتمعية',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                      color: AppColors.yemenBlack,
                    ),
                  ),
                  IconButton(
                    onPressed: _isSubmitting
                        ? null
                        : () {
                            if (_isReviewStep) {
                              setState(() => _isReviewStep = false);
                            } else {
                              Navigator.of(context).pop(false);
                            }
                          },
                    icon: Icon(
                      _isReviewStep ? Icons.arrow_back_rounded : Icons.close_rounded,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),

              // Educational Simulation Disclaimer Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: AppColors.yemenGoldLight,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                      color: AppColors.yemenGold.withValues(alpha: 0.3)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.info_outline_rounded,
                        color: AppColors.yemenGold, size: 18),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        'هذه مساهمة محاكاة أكاديمية تهدف لتفعيل التمويل التشاركي، دون خصم مالي حقيقي.',
                        style: TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w600,
                          color: AppColors.yemenBlack,
                          height: 1.3,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              if (_errorMessage != null) ...[
                Container(
                  padding: const EdgeInsets.all(10),
                  margin: const EdgeInsets.only(bottom: 12),
                  decoration: BoxDecoration(
                    color: AppColors.yemenRedLight,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                        color: AppColors.yemenRed.withValues(alpha: 0.3)),
                  ),
                  child: Text(
                    _errorMessage!,
                    style: const TextStyle(
                      fontSize: 12,
                      color: AppColors.yemenRed,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],

              if (!_isReviewStep) ...[
                // STEP 1: Enter Amount
                Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.project.title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textSecondary,
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Amount Input
                      const Text(
                        'المبلغ المراد المساهمة به (ريال يمني)',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: AppColors.yemenBlack,
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextFormField(
                        controller: _amountController,
                        keyboardType:
                            const TextInputType.numberWithOptions(decimal: true),
                        inputFormatters: [
                          FilteringTextInputFormatter.allow(
                              RegExp(r'^\d+\.?\d{0,2}')),
                        ],
                        textAlign: TextAlign.start,
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w800,
                          color: AppColors.yemenBlack,
                        ),
                        decoration: InputDecoration(
                          hintText: '0.00',
                          suffixText: 'ريال',
                          suffixStyle: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textSecondary,
                          ),
                          filled: true,
                          fillColor: AppColors.backgroundSubtle,
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 14),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide:
                                const BorderSide(color: AppColors.borderSubtle),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: const BorderSide(
                                color: AppColors.yemenRed, width: 1.5),
                          ),
                        ),
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'يرجى إدخال مبلغ المساهمة';
                          }
                          final parsed = double.tryParse(value);
                          if (parsed == null || parsed <= 0) {
                            return 'يرجى إدخال مبلغ صالح أكبر من الصفر';
                          }
                          if (parsed < 1) {
                            return 'الحد الأدنى هو 1 ريال';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 12),

                      // Quick Selection Chips
                      SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: _presetAmounts.map((amount) {
                            return Padding(
                              padding: const EdgeInsets.only(left: 8),
                              child: ActionChip(
                                label: Text(
                                  '+ ${currencyFormatter.format(amount)} ريال',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: AppColors.yemenBlack,
                                  ),
                                ),
                                backgroundColor: AppColors.backgroundSubtle,
                                side: const BorderSide(
                                    color: AppColors.borderSubtle),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                onPressed: () {
                                  setState(() {
                                    _amountController.text =
                                        amount.toStringAsFixed(0);
                                  });
                                },
                              ),
                            );
                          }).toList(),
                        ),
                      ),
                      const SizedBox(height: 20),

                      // Next Button
                      ElevatedButton(
                        onPressed: _proceedToReview,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.yemenRed,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          elevation: 0,
                        ),
                        child: const Text(
                          'متابعة إلى التأكيد',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ] else ...[
                // STEP 2: Review & Confirm
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.backgroundSubtle,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppColors.borderSubtle),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'المشروع المستهدف:',
                            style: TextStyle(
                                fontSize: 13, color: AppColors.textSecondary),
                          ),
                          Flexible(
                            child: Text(
                              widget.project.title,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: AppColors.yemenBlack,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const Divider(height: 20, color: AppColors.borderSubtle),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'قيمة المساهمة:',
                            style: TextStyle(
                                fontSize: 13, color: AppColors.textSecondary),
                          ),
                          Text(
                            '${currencyFormatter.format(_parsedAmount ?? 0)} ريال يمني',
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w900,
                              color: AppColors.yemenEmerald,
                            ),
                          ),
                        ],
                      ),
                      const Divider(height: 20, color: AppColors.borderSubtle),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: const [
                          Text(
                            'طريقة المساهمة:',
                            style: TextStyle(
                                fontSize: 13, color: AppColors.textSecondary),
                          ),
                          Text(
                            'محاكاة دعم مجتمعي (Simulated)',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: AppColors.yemenBlack,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Submit Button
                ElevatedButton(
                  onPressed: _isSubmitting ? null : _submitContribution,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.yemenEmerald,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 0,
                  ),
                  child: _isSubmitting
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor:
                                AlwaysStoppedAnimation<Color>(Colors.white),
                          ),
                        )
                      : const Text(
                          'تأكيد وتسجيل المساهمة الآن',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
