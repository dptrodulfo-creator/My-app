@extends('layouts.app')

@section('title', 'Driver Roster')

@section('content')
    <h1>Driver Roster</h1>
    <p class="muted">Every driver, with the tasks they have been sent and the ones they have confirmed. Open a driver to see their tasks.</p>

    @forelse ($drivers as $driver)
        <article class="driver">
            <div class="driver-head">
                <h3>{{ $driver->name }}</h3>
                <span class="muted">{{ $driver->email }}</span>
            </div>

            <div class="driver-counts">
                @if ($driver->isOnHold())
                    <span class="chip chip-hold">On hold</span>
                @else
                    <span class="chip chip-received">Active</span>
                @endif
                @unless ($driver->hasVerifiedEmail())
                    <span class="chip chip-sent">Email not confirmed</span>
                @endunless
                <span class="chip chip-sent">{{ $driver->waiting_count }} waiting</span>
                <span class="chip chip-received">{{ $driver->received_count }} received</span>
            </div>

            <details>
                <summary>Tasks ({{ $driver->tasks->count() }})</summary>
                @if ($driver->tasks->isEmpty())
                    <p class="muted">No tasks sent to this driver yet.</p>
                @else
                    <ul>
                        @foreach ($driver->tasks as $task)
                            <li>
                                <strong>{{ $task->item_name }}</strong> ×{{ $task->item_quantity }} to {{ $task->recipient_name }}, {{ $task->destination }} ({{ $task->zone }}).
                                @if ($task->isReceived())
                                    <span class="chip chip-received">Received {{ $task->received_at?->diffForHumans() }}</span>
                                @else
                                    <span class="chip chip-sent">Sent {{ $task->created_at->diffForHumans() }}, not confirmed</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </details>
        </article>
    @empty
        <p class="empty">No drivers yet. Drivers appear here as soon as they register.</p>
    @endforelse
@endsection
