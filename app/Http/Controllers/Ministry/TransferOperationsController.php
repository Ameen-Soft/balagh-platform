<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\ComplaintTransfer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferOperationsController extends Controller
{
    /**
     * List incoming and outgoing transfers for this department.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $deptId = $user->department_id;
        $type = $request->get('type', 'incoming'); // incoming or outgoing

        if ($type === 'outgoing') {
            $transfers = ComplaintTransfer::with([
                'complaint:id,title,status',
                'toDepartment.ministry',
                'transferredByUser:id,name',
            ])
            ->where('from_department_id', $deptId)
            ->latest()
            ->paginate(15);
        } else {
            $transfers = ComplaintTransfer::with([
                'complaint:id,title,status',
                'fromDepartment.ministry',
                'transferredByUser:id,name',
            ])
            ->where('to_department_id', $deptId)
            ->latest()
            ->paginate(15);
        }

        return view('ministry.transfers.index', compact('transfers', 'type'));
    }
}
