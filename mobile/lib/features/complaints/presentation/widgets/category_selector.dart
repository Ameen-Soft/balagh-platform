import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/constants/app_colors.dart';
import '../../application/complaints_providers.dart';
import '../../domain/entities/category_entity.dart';
import '../../domain/entities/ministry_entity.dart';

class CategorySelector extends ConsumerWidget {
  const CategorySelector({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final complaintState = ref.watch(createComplaintNotifierProvider);
    final notifier = ref.read(createComplaintNotifierProvider.notifier);
    final ministriesAsync = ref.watch(ministriesProvider);

    return Container(
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
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 1. Ministry Dropdown
          _buildFieldLabel('الجهة المختصة'),
          const SizedBox(height: 8),
          ministriesAsync.when(
            data: (ministries) {
              if (ministries.isEmpty) {
                return _buildEmptyBox('لا توجد جهات متاحة حالياً.');
              }
              return _buildDropdown<MinistryEntity>(
                hintText: 'اختر الوزارة أو الجهة...',
                icon: Icons.account_balance_outlined,
                selectedValue: complaintState.selectedMinistry,
                items: ministries,
                itemLabel: (m) => m.name,
                onChanged: (selected) {
                  if (selected != null) {
                    notifier.selectMinistry(selected);
                  }
                },
              );
            },
            loading: () => _buildLoadingField('جارٍ تحميل الجهات...'),
            error: (err, _) => _buildErrorField(
              'تعذر التحميل. اضغط للمحاولة',
              () => ref.refresh(ministriesProvider),
            ),
          ),

          // 2. Main Category Dropdown
          if (complaintState.selectedMinistry != null) ...[
            const SizedBox(height: 18),
            _buildFieldLabel('التصنيف الرئيسي'),
            const SizedBox(height: 8),
            Consumer(
              builder: (context, ref, child) {
                final parentCatsAsync = ref.watch(
                  parentCategoriesProvider(complaintState.selectedMinistry!.id),
                );

                return parentCatsAsync.when(
                  data: (categories) {
                    if (categories.isEmpty) {
                      return _buildEmptyBox('لا توجد تصنيفات مسجلة.');
                    }
                    return _buildDropdown<CategoryEntity>(
                      hintText: 'اختر القطاع...',
                      icon: Icons.grid_view_rounded,
                      selectedValue: complaintState.selectedParentCategory,
                      items: categories,
                      itemLabel: (c) => c.name,
                      onChanged: (selected) {
                        if (selected != null) {
                          notifier.selectParentCategory(selected);
                        }
                      },
                    );
                  },
                  loading: () => _buildLoadingField('جارٍ تحميل التصنيفات...'),
                  error: (err, _) => _buildErrorField(
                    'تعذر التحميل. اضغط للمحاولة',
                    () => ref.refresh(
                      parentCategoriesProvider(
                        complaintState.selectedMinistry!.id,
                      ),
                    ),
                  ),
                );
              },
            ),
          ],

          // 3. Sub-Category Dropdown
          if (complaintState.selectedParentCategory != null &&
              complaintState.selectedParentCategory!.hasChildren) ...[
            const SizedBox(height: 18),
            _buildFieldLabel('نوع المشكلة'),
            const SizedBox(height: 8),
            Consumer(
              builder: (context, ref, child) {
                final subCatsAsync = ref.watch(
                  subCategoriesProvider(
                    complaintState.selectedParentCategory!.id,
                  ),
                );

                return subCatsAsync.when(
                  data: (subCategories) {
                    if (subCategories.isEmpty) {
                      return _buildEmptyBox('لا توجد تصنيفات فرعية.');
                    }
                    return _buildDropdown<CategoryEntity>(
                      hintText: 'حدد نوع المشكلة...',
                      icon: Icons.tune_rounded,
                      selectedValue: complaintState.selectedCategory,
                      items: subCategories,
                      itemLabel: (c) => c.name,
                      onChanged: (selected) {
                        if (selected != null) {
                          notifier.selectCategory(selected);
                        }
                      },
                    );
                  },
                  loading: () =>
                      _buildLoadingField('جارٍ تحميل التصنيفات الفرعية...'),
                  error: (err, _) => _buildErrorField(
                    'تعذر التحميل. اضغط للمحاولة',
                    () => ref.refresh(
                      subCategoriesProvider(
                        complaintState.selectedParentCategory!.id,
                      ),
                    ),
                  ),
                );
              },
            ),
          ],

          // Subtle Selection Indicator
          if (complaintState.selectedCategory != null) ...[
            const SizedBox(height: 18),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: AppColors.yemenEmeraldLight,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.check_circle_rounded,
                    color: AppColors.yemenEmerald,
                    size: 16,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      complaintState.selectedCategory!.name,
                      style: const TextStyle(
                        color: AppColors.yemenEmerald,
                        fontWeight: FontWeight.w700,
                        fontSize: 12.5,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildFieldLabel(String label) {
    return Text(
      label,
      style: const TextStyle(
        fontSize: 13,
        fontWeight: FontWeight.w700,
        color: AppColors.yemenBlack,
      ),
    );
  }

  Widget _buildDropdown<T>({
    required String hintText,
    required IconData icon,
    required T? selectedValue,
    required List<T> items,
    required String Function(T) itemLabel,
    required ValueChanged<T?> onChanged,
  }) {
    final hasSelection = selectedValue != null && items.contains(selectedValue);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        color: AppColors.backgroundSubtle,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: hasSelection
              ? AppColors.yemenBlack.withValues(alpha: 0.25)
              : AppColors.borderSubtle,
          width: 0.8,
        ),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<T>(
          isExpanded: true,
          value: hasSelection ? selectedValue : null,
          icon: const Icon(
            Icons.keyboard_arrow_down_rounded,
            color: AppColors.textSecondary,
            size: 20,
          ),
          borderRadius: BorderRadius.circular(12),
          dropdownColor: Colors.white,
          hint: Row(
            children: [
              Icon(icon, size: 17, color: AppColors.textMuted),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  hintText,
                  style: const TextStyle(
                    color: AppColors.textSecondary,
                    fontSize: 13,
                  ),
                ),
              ),
            ],
          ),
          items: items.map((item) {
            final isSelected = item == selectedValue;
            return DropdownMenuItem<T>(
              value: item,
              child: Row(
                children: [
                  Icon(
                    icon,
                    size: 17,
                    color: isSelected
                        ? AppColors.yemenRed
                        : AppColors.textSecondary,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      itemLabel(item),
                      style: TextStyle(
                        fontSize: 13.5,
                        fontWeight: isSelected
                            ? FontWeight.w700
                            : FontWeight.w500,
                        color: isSelected
                            ? AppColors.yemenBlack
                            : AppColors.textPrimary,
                      ),
                    ),
                  ),
                ],
              ),
            );
          }).toList(),
          onChanged: onChanged,
        ),
      ),
    );
  }

  Widget _buildLoadingField(String message) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
      decoration: BoxDecoration(
        color: AppColors.backgroundSubtle,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderSubtle, width: 0.8),
      ),
      child: Row(
        children: [
          const SizedBox(
            width: 15,
            height: 15,
            child: CircularProgressIndicator(
              strokeWidth: 1.8,
              color: AppColors.yemenRed,
            ),
          ),
          const SizedBox(width: 12),
          Text(
            message,
            style: const TextStyle(
              fontSize: 12.5,
              color: AppColors.textSecondary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorField(String message, VoidCallback onRetry) {
    return InkWell(
      onTap: onRetry,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
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
              Icons.refresh_rounded,
              color: AppColors.yemenRed,
              size: 18,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                message,
                style: const TextStyle(
                  color: AppColors.yemenRed,
                  fontSize: 12.5,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyBox(String text) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.backgroundSubtle,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderSubtle, width: 0.8),
      ),
      child: Text(
        text,
        textAlign: TextAlign.center,
        style: const TextStyle(fontSize: 12.5, color: AppColors.textSecondary),
      ),
    );
  }
}
