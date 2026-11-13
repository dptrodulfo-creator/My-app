<?php

namespace App\Http\Controllers\Desk;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTask;
use App\Models\User;
use App\Support\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Monitor delivery tasks, and send new ones to drivers.
 */
class TaskBoardController extends Controller
{
    public function index(): View
    {
        $zone = Zone::current();

        return view('desk.tasks', [
            'zone' => $zone,
            // Feature 2: only the tasks of the zone picked in the session.
            'tasks' => DeliveryTask::with('driver')->where('zone', $zone)->latest()->limit(50)->get(),
            'drivers' => User::where('role', User::ROLE_DRIVER)
                ->whereNull('suspended_at')
                ->whereNotNull('email_verified_at')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:120'],
            'item_quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'item_notes' => ['nullable', 'string', 'max:500'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'destination' => ['required', 'string', 'max:255'],
            'zone' => ['required', Rule::in(Zone::all())],
            // A task can only go to an active driver who has confirmed their email.
            'driver_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', User::ROLE_DRIVER)
                        ->whereNull('suspended_at')
                        ->whereNotNull('email_verified_at');
                }),
            ],
        ]);

        $task = DeliveryTask::create($data);

        return back()->with('status', "Task sent to {$task->driver->name} in {$task->zone}. It shows on their run sheet now.");
    }
}
