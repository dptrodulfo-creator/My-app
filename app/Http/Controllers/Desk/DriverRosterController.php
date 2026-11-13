<?php

namespace App\Http\Controllers\Desk;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTask;
use App\Models\User;
use Illuminate\View\View;

/**
 * Monitor the drivers and the tasks each one has received.
 */
class DriverRosterController extends Controller
{
    public function index(): View
    {
        $drivers = User::where('role', User::ROLE_DRIVER)
            ->withCount([
                'tasks as waiting_count' => fn ($query) => $query->where('status', DeliveryTask::SENT),
                'tasks as received_count' => fn ($query) => $query->where('status', DeliveryTask::RECEIVED),
            ])
            ->with(['tasks' => fn ($query) => $query->latest()])
            ->orderBy('name')
            ->get();

        return view('desk.drivers', ['drivers' => $drivers]);
    }
}
