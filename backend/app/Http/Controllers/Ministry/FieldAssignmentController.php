<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\FieldAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FieldAssignmentController extends Controller
{
    /**
     * List all field assignments scoped to this ministry/department.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        if (! $ministryId) {
            abort(403, 'حساب المشرف غير مرتبط بوزارة معتمدة في النظام.');
        }

        $status = $request->get('status');

        $query = FieldAssignment::with([
            'worker:id,name,phone',
            'assignedBy:id,name',
            'complaint:id,title,status,priority',
            'evidence',
        ])->whereHas('complaint.currentDepartment', fn ($q) => $q->where('ministry_id', $ministryId));

        if ($status && in_array($status, ['pending', 'accepted', 'in_progress', 'completed', 'failed'])) {
            $query->where('status', $status);
        }

        $assignments = $query->latest()->paginate(15);

        return view('ministry.assignments.index', compact('assignments', 'status'));
    }
}
