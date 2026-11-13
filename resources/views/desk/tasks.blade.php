@extends('layouts.app')

@section('title', 'Task Board')

@section('content')
    <h1>Task Board</h1>
    <p class="muted">Showing zone {{ $zone }}. Use the zone picker in the top bar to look at another zone.</p>

    <section class="panel">
        <h2>Send a new delivery task</h2>

        @if ($drivers->isEmpty())
            <p class="empty">No active drivers yet. A driver has to register and confirm their email first.</p>
        @else
            <form method="POST" action="{{ route('desk.tasks.store') }}" class="grid-form">
                @csrf

                <div>
                    <label for="item_name">Item</label>
                    <input id="item_name" name="item_name" type="text" value="{{ old('item_name') }}" required>
                    @error('item_name')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="item_quantity">Quantity</label>
                    <input id="item_quantity" name="item_quantity" type="number" min="1" max="999" value="{{ old('item_quantity', 1) }}" required>
                    @error('item_quantity')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="wide">
                    <label for="item_notes">Item notes (optional)</label>
                    <textarea id="item_notes" name="item_notes">{{ old('item_notes') }}</textarea>
                    @error('item_notes')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="recipient_name">Deliver to</label>
                    <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name') }}" required>
                    @error('recipient_name')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="destination">Destination address</label>
                    <input id="destination" name="destination" type="text" value="{{ old('destination') }}" required>
                    @error('destination')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="zone">Zone</label>
                    <select id="zone" name="zone" required>
                        @foreach (\App\Support\Zone::all() as $zoneName)
                            <option value="{{ $zoneName }}" @selected(old('zone', $zone) === $zoneName)>{{ $zoneName }}</option>
                        @endforeach
                    </select>
                    @error('zone')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="driver_id">Driver</label>
                    <select id="driver_id" name="driver_id" required>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" @selected((string) old('driver_id') === (string) $driver->id)>{{ $driver->name }}</option>
                        @endforeach
                    </select>
                    @error('driver_id')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="wide">
                    <button class="btn" type="submit">Send task</button>
                </div>
            </form>
        @endif
    </section>

    <h2>Tasks in {{ $zone }}</h2>
    @if ($tasks->isEmpty())
        <p class="empty">No tasks in {{ $zone }} yet.</p>
    @else
        <div class="scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Deliver to</th>
                        <th>Driver</th>
                        <th>Status</th>
                        <th>Sent</th>
                        <th>Confirmed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                        <tr>
                            <td>
                                <strong>{{ $task->item_name }}</strong> ×{{ $task->item_quantity }}
                                @if ($task->item_notes)
                                    <br><span class="muted">{{ $task->item_notes }}</span>
                                @endif
                            </td>
                            <td>{{ $task->recipient_name }}<br><span class="muted">{{ $task->destination }}</span></td>
                            <td>{{ $task->driver?->name }}</td>
                            <td>
                                @if ($task->isReceived())
                                    <span class="chip chip-received">Received</span>
                                @else
                                    <span class="chip chip-sent">Sent</span>
                                @endif
                            </td>
                            <td title="{{ $task->created_at->format('M j, Y g:i A') }}">{{ $task->created_at->diffForHumans() }}</td>
                            <td>{{ $task->received_at ? $task->received_at->diffForHumans() : 'Not yet' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
