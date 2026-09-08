<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Department;

class RoutingService
{
    /**
     * Determine the designated ministry and department for a given category.
     * Includes a robust fallback mechanism if direct mapping is missing.
     *
     * @return array{ministry_id: int, department_id: int, is_fallback: bool}
     */
    public function route(int $categoryId): array
    {
        $category = Category::with('department')->find($categoryId);

        // Check if category has a valid department associated
        if ($category && $category->department_id && $category->department) {
            $department = $category->department;

            if ($department->ministry_id) {
                return [
                    'ministry_id' => (int) $department->ministry_id,
                    'department_id' => (int) $department->id,
                    'is_fallback' => false,
                ];
            }
        }

        // If parent category exists, check if parent has a department mapping
        if ($category && $category->parent_id) {
            $parentCategory = Category::with('department')->find($category->parent_id);
            if ($parentCategory && $parentCategory->department_id && $parentCategory->department) {
                return [
                    'ministry_id' => (int) $parentCategory->department->ministry_id,
                    'department_id' => (int) $parentCategory->department->id,
                    'is_fallback' => false,
                ];
            }
        }

        // Fallback to configured default department and ministry
        $fallbackDepartmentId = (int) config('balagh.routing.fallback_department_id', 1);
        $department = Department::find($fallbackDepartmentId);

        $fallbackMinistryId = $department
            ? (int) $department->ministry_id
            : (int) config('balagh.routing.fallback_ministry_id', 1);

        return [
            'ministry_id' => $fallbackMinistryId,
            'department_id' => $fallbackDepartmentId,
            'is_fallback' => true,
        ];
    }
}
