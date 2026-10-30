<?php

namespace App\Services;

use App\Models\Category;
use DomainException;
use Illuminate\Support\Facades\Log;

class RoutingService
{
    /**
     * Determine the designated ministry and department for a given subcategory.
     * Strictly verifies active state and ministry integrity.
     * Throws an exception if routing cannot be safely established (no random fallback).
     *
     * @return array{ministry_id: int, department_id: int}
     * @throws DomainException
     */
    public function route(int $categoryId): array
    {
        $category = Category::with(['department.ministry', 'parent'])->find($categoryId);

        if (! $category) {
            throw new DomainException('التصنيف المطلوب غير متوفر في النظام.');
        }

        if (! $category->is_active) {
            throw new DomainException('هذا التصنيف معطل حالياً ولا يمكن استقبال بلاغات جديدة عليه.');
        }

        if ($category->level === 1 || empty($category->parent_id)) {
            throw new DomainException('لا يمكن تقديم بلاغ على تصنيف رئيسي مباشرة؛ يرجى اختيار نوع المشكلة من التصنيفات الفرعية.');
        }

        if ($category->parent && ! $category->parent->is_active) {
            throw new DomainException('التصنيف الرئيسي التابع له هذا التصنيف معطل حالياً.');
        }

        if (! $category->department_id || ! $category->department) {
            throw new DomainException('لم يتم تعيين إدارة خدمية مسؤولة لهذا التصنيف.');
        }

        if (! $category->department->is_active) {
            throw new DomainException('الإدارة الخدمية المسؤولة عن هذا التصنيف معطلة حالياً.');
        }

        $ministryId = $category->ministry_id ?? $category->parent?->ministry_id ?? $category->department->ministry_id;
        $departmentMinistryId = $category->department->ministry_id;
        $parentMinistryId = $category->parent?->ministry_id;

        // Tri-partite integrity check: subcategory.ministry_id = parent.ministry_id = department.ministry_id
        if ($ministryId !== $departmentMinistryId || ($parentMinistryId && $ministryId !== $parentMinistryId)) {
            Log::critical('Ministry mismatch in category routing', [
                'category_id' => $category->id,
                'category_ministry' => $ministryId,
                'parent_ministry' => $parentMinistryId,
                'department_ministry' => $departmentMinistryId,
            ]);
            throw new DomainException('تعذر توجيه البلاغ بسبب عدم تطابق تبعية الوزارة بين التصنيف والإدارة المسؤولة.');
        }

        return [
            'ministry_id' => (int) $ministryId,
            'department_id' => (int) $category->department_id,
        ];
    }
}
