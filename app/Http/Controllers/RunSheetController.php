<?php

namespace App\Http\Controllers;

use App\Models\DeliveryTask;
use App\Support\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The driver's page: receive delivery tasks (with destination and item information)
 * and send the "received" confirmation.
 */
class RunSheetController extends Controller
{
    public function index(Request $request): View
    {
        $me = $request->user();
        $zone = Zone::current();

        // Feature 2: only this driver's tasks in the zone picked in the session.
        $inZone = $me->tasks()->where('zone', $zone)->latest()->get();

        return view('runsheet.index', [
            'zone' => $zone,
            'waiting' => $inZone->where('status', DeliveryTask::SENT),
            'received' => $inZone->where('status', DeliveryTask::RECEIVED),
            // New tasks hiding in zones that are not picked right now.
            'waitingElsewhere' => $me->tasks()
                ->where('status', DeliveryTask::SENT)
                ->where('zone', '!=', $zone)
                ->count(),
        ]);
    }

    /** "Confirm received": the driver confirms they got the task. */
    public function confirm(Request $request, DeliveryTask $task): RedirectResponse
    {
        // A driver can only confirm their own tasks.
        abort_unless((int) $task->driver_id === (int) $request->user()->id, 403);

        if ($task->status === DeliveryTask::SENT) {
            $task->forceFill([
                'status' => DeliveryTask::RECEIVED,
                'received_at' => now(),
            ])->save();

            return back()->with('status', "Receipt confirmed for “{$task->item_name}”. The dispatch desk can see it now.");
        }

        return back();
    }
}
