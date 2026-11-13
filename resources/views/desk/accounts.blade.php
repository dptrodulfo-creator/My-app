@extends('layouts.app')

@section('title', 'Accounts')

@section('content')
    <h1>Accounts</h1>
    <p class="muted">Every account in RunSheet. A driver put on hold is signed out on their next click and can’t sign in until you reinstate them.</p>

    <div class="scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Role</th>
                    <th>Email confirmed</th>
                    <th>Last sign-in</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accounts as $account)
                    <tr>
                        <td><strong>{{ $account->name }}</strong><br><span class="muted">{{ $account->email }}</span></td>
                        <td>{{ $account->role }}</td>
                        <td>{{ $account->hasVerifiedEmail() ? 'Yes' : 'Not yet' }}</td>
                        <td>{{ $account->last_login_at ? $account->last_login_at->diffForHumans() : 'Never' }}</td>
                        <td>
                            @if ($account->isOnHold())
                                <span class="chip chip-hold">On hold</span>
                            @else
                                <span class="chip chip-received">Active</span>
                            @endif
                        </td>
                        <td>
                            @if ($account->isDispatcher())
                                <span class="muted">Protected</span>
                            @elseif ($account->isOnHold())
                                <form method="POST" action="{{ route('desk.accounts.reinstate', $account) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-line btn-small" type="submit">Reinstate</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('desk.accounts.hold', $account) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-warn btn-small" type="submit">Put on hold</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
