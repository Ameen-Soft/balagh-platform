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

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // 1. Ministry Section
        _buildSectionHeader(
          stepNumber: '1',
          title: 'اختر الجهة / الوزارة المعنية',
          subtitle: 'حدد الجهة الحكومية ذات الاختصاص بموضوع البلاغ',
        ),
        const SizedBox(height: 12),
        ministriesAsync.when(
          data: (ministries) {
            if (ministries.isEmpty) {
              return _buildEmptyBox('لا توجد وزارات متاحة حالياً.');
            }
            return _buildDropdown<MinistryEntity>(
              hintText: 'اضغط لاختيار الوزارة...',
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
          loading: () => _buildLoadingField('جارٍ تحميل قائمة الوزارات...'),
          error: (err, _) => _buildErrorField(
            'تعذر تحميل الوزارات. اضغط لإعادة المحاولة',
            () => ref.refresh(ministriesProvider),
          ),
        ),

        const SizedBox(height: 24),

        // 2. Main Category Section (shown after ministry is selected)
        if (complaintState.selectedMinistry != null) ...[
          _buildSectionHeader(
            stepNumber: '2',
            title: 'التصنيف الرئيسي',
            subtitle: 'اختر القطاع أو المجال الذي يندرج تحته البلاغ',
          ),
          const SizedBox(height: 12),
          Consumer(
            builder: (context, ref, child) {
              final parentCatsAsync = ref.watch(
                parentCategoriesProvider(complaintState.selectedMinistry!.id),
              );

              return parentCatsAsync.when(
                data: (categories) {
                  if (categories.isEmpty) {
                    return _buildEmptyBox('لا توجد تصنيفات رئيسية مسجلة لهذه الوزارة.');
                  }
                  return _buildDropdown<CategoryEntity>(
                    hintText: 'اختر التصنيف الرئيسي...',
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
                  'تعذر تحميل التصنيفات الرئيسية. اضغط لإعادة المحاولة',
                  () => ref.refresh(
                    parentCategoriesProvider(complaintState.selectedMinistry!.id),
                  ),
                ),
              );
            },
          ),
          const SizedBox(height: 24),
        ],

        // 3. Sub-Category Section (shown if parent category has children)
        if (complaintState.selectedParentCategory != null &&
            complaintState.selectedParentCategory!.hasChildren) ...[
          _buildSectionHeader(
            stepNumber: '3',
            title: 'التصنيف الفرعي الدقيق',
            subtitle: 'حدد نوع المشكلة بدقة لتسريع التوجيه والمعالجة',
          ),
          const SizedBox(height: 12),
          Consumer(
            builder: (context, ref, child) {
              final subCatsAsync = ref.watch(
                subCategoriesProvider(complaintState.selectedParentCategory!.id),
              );

              return subCatsAsync.when(
                data: (subCategories) {
                  if (subCategories.isEmpty) {
                    return _buildEmptyBox('لا توجد تصنيفات فرعية.');
                  }
                  return _buildDropdown<CategoryEntity>(
                    hintText: 'اختر التصنيف الفرعي...',
                    icon: Icons.subdirectory_arrow_left_rounded,
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
                loading: () => _buildLoadingField('جارٍ تحميل التصنيفات الفرعية...'),
                error: (err, _) => _buildErrorField(
                  'تعذر تحميل التصنيفات الفرعية. اضغط للمحاولة',
                  () => ref.refresh(
                    subCategoriesProvider(complaintState.selectedParentCategory!.id),
                  ),
                ),
              );
            },
          ),
        ],

        // Selection confirmation pill
        if (complaintState.selectedCategory != null) ...[
          const SizedBox(height: 20),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: AppColors.yemenEmeraldLight,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: AppColors.yemenEmerald.withValues(alpha: 0.3),
              ),
            ),
            child: Row(
              children: [
                const Icon(
                  Icons.check_circle_rounded,
                  color: AppColors.yemenEmerald,
                  size: 20,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'التصنيف المعتمد: ${complaintState.selectedCategory!.name}',
                    style: const TextStyle(
                      color: AppColors.yemenEmerald,
                      fontWeight: FontWeight.w700,
                      fontSize: 13,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildSectionHeader({
    required String stepNumber,
    required String title,
    required String subtitle,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 26,
          height: 26,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: AppColors.yemenBlack,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Text(
            stepNumber,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
              fontSize: 13,
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w800,
                  color: AppColors.yemenBlack,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                subtitle,
                style: const TextStyle(
                  fontSize: 12,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
      ],
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
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: selectedValue != null
              ? AppColors.yemenRed
              : AppColors.borderSubtle,
          width: selectedValue != null ? 1.5 : 1.0,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<T>(
          isExpanded: true,
          value: items.contains(selectedValue) ? selectedValue : null,
          hint: Row(
            children: [
              const SizedBox(width: 14),
              Icon(icon, size: 20, color: AppColors.textMuted),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  hintText,
                  style: const TextStyle(
                    color: AppColors.textSecondary,
                    fontSize: 14,
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
                  const SizedBox(width: 14),
                  Icon(
                    icon,
                    size: 20,
                    color: isSelected ? AppColors.yemenRed : AppColors.textSecondary,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      itemLabel(item),
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: isSelected ? FontWeight.w700 : FontWeight.normal,
                        color: isSelected ? AppColors.yemenRed : AppColors.textPrimary,
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
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Row(
        children: [
          const SizedBox(
            width: 18,
            height: 18,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: AppColors.yemenRed,
            ),
          ),
          const SizedBox(width: 14),
          Text(
            message,
            style: const TextStyle(
              fontSize: 13,
              color: AppColors.textSecondary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorField(String message, VoidCallback onRetry) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.yemenRedLight,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.yemenRedTint),
      ),
      child: Row(
        children: [
          const Icon(Icons.error_outline_rounded, color: AppColors.yemenRed, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(
                color: AppColors.yemenRed,
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppColors.yemenRed, size: 20),
            onPressed: onRetry,
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyBox(String text) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.backgroundSubtle,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Center(
        child: Text(
          text,
          style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
        ),
      ),
    );
  }
}
