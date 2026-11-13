<?php

namespace App\Http\Controllers\Desk;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTask;
use App\Models\User;
use Illuminate\View\View;

/**
 * The dispatcher's home page: a quick look at drivers and at who has confirmed what.
 */
class DeskController extends Controller
{
    public function index(): View
    {
        $drivers = User::where('role', User::ROLE_DRIVER);

        return view('desk.index', [
            'driverCount' => (clone $drivers)->count(),
            'onHoldCount' => (clone $drivers)->whereNotNull('suspended_at')->count(),
            'waitingCount' => DeliveryTask::where('status', DeliveryTask::SENT)->count(),
            'receivedCount' => DeliveryTask::where('status', DeliveryTask::RECEIVED)->count(),
            'waiting' => DeliveryTask::with('driver')
                ->where('status', DeliveryTask::SENT)->oldest()->limit(8)->get(),
            'latest' => DeliveryTask::with('driver')
                ->where('status', DeliveryTask::RECEIVED)->latest('received_at')->limit(8)->get(),
        ]);
    }
}
