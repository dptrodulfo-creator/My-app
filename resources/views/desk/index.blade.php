@extends('layouts.app')

@section('title', 'Dispatch Desk')

@section('content')
    <h1>Dispatch Desk</h1>
    <p class="muted">All zones at a glance. Send work from the Task Board, then watch the confirmations arrive here.</p>

    <div class="strip">
        <div>
            <span class="big">{{ $driverCount }}</span>
            {{ $driverCount === 1 ? 'driver' : 'drivers' }}, {{ $onHoldCount }} on hold
        </div>
        <div>
            <span class="big">{{ $waitingCount }}</span>
            waiting for confirmation
        </div>
        <div>
            <span class="big">{{ $receivedCount }}</span>
            confirmed received
        </div>
    </div>

    <div class="cols">
        <section>
            <h2>Still waiting</h2>
            @if ($waiting->isEmpty())
                <p class="empty">Every task has been confirmed.</p>
            @else
                <ul class="feed">
                    @foreach ($waiting as $task)
                        <li>
                            <strong>{{ $task->item_name }}</strong> ×{{ $task->item_quantity }}<br>
                            <span class="muted">Sent to {{ $task->driver?->name }} in {{ $task->zone }}, {{ $task->created_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section>
            <h2>Latest confirmations</h2>
            @if ($latest->isEmpty())
                <p class="empty">No confirmations yet.</p>
            @else
                <ul class="feed">
                    @foreach ($latest as $task)
                        <li>
                            <strong>{{ $task->item_name }}</strong> ×{{ $task->item_quantity }}<br>
                            <span class="muted">Confirmed by {{ $task->driver?->name }}, {{ $task->received_at?->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
