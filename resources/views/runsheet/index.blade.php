@extends('layouts.app')

@section('title', 'My Run Sheet')

@section('content')
    <h1>My Run Sheet</h1>
    <p class="muted">Showing zone {{ $zone }}. Use the zone picker in the top bar to look at another zone.</p>

    {{-- The "notification": new tasks that are waiting for this driver to confirm. --}}
    @if ($waiting->count() > 0)
        <p class="notice">You have {{ $waiting->count() }} new {{ $waiting->count() === 1 ? 'task' : 'tasks' }} waiting for your confirmation in {{ $zone }}.</p>
    @endif
    @if ($waitingElsewhere > 0)
        <p class="muted">{{ $waitingElsewhere }} more new {{ $waitingElsewhere === 1 ? 'task is' : 'tasks are' }} waiting in other zones. Switch zones to see {{ $waitingElsewhere === 1 ? 'it' : 'them' }}.</p>
    @endif

    <h2>Waiting for your confirmation</h2>
    @forelse ($waiting as $task)
        <article class="ticket">
            <span class="tape">New</span>
            <div class="stub"><span aria-hidden="true">×{{ $task->item_quantity }}</span><span class="sr-only">Quantity {{ $task->item_quantity }}</span></div>
            <div class="ticket-body">
                <h3>{{ $task->item_name }}</h3>
                @if ($task->item_notes)
                    <p class="notes">{{ $task->item_notes }}</p>
                @endif
                <dl class="facts">
                    <div><dt>Deliver to</dt><dd>{{ $task->recipient_name }}</dd></div>
                    <div><dt>Destination</dt><dd>{{ $task->destination }}</dd></div>
                    <div><dt>Sent</dt><dd title="{{ $task->created_at->format('M j, Y g:i A') }}">{{ $task->created_at->diffForHumans() }}</dd></div>
                </dl>
                <form method="POST" action="{{ route('runsheet.confirm', $task) }}">
                    @csrf
                    <button class="btn" type="submit">Confirm received</button>
                </form>
            </div>
        </article>
    @empty
        <p class="empty">Nothing is waiting for you in {{ $zone }}.</p>
    @endforelse

    <h2>Confirmed</h2>
    @forelse ($received as $task)
        <article class="ticket done">
            <span class="stamp">Received</span>
            <div class="stub"><span aria-hidden="true">×{{ $task->item_quantity }}</span><span class="sr-only">Quantity {{ $task->item_quantity }}</span></div>
            <div class="ticket-body">
                <h3>{{ $task->item_name }}</h3>
                @if ($task->item_notes)
                    <p class="notes">{{ $task->item_notes }}</p>
                @endif
                <dl class="facts">
                    <div><dt>Deliver to</dt><dd>{{ $task->recipient_name }}</dd></div>
                    <div><dt>Destination</dt><dd>{{ $task->destination }}</dd></div>
                    <div>
                        <dt>Confirmed</dt>
                        <dd title="{{ $task->received_at?->format('M j, Y g:i A') }}">{{ $task->received_at?->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>
        </article>
    @empty
        <p class="empty">No confirmed tasks in {{ $zone }} yet.</p>
    @endforelse
@endsection
