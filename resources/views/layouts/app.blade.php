<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Welcome') | RunSheet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&amp;family=Barlow+Condensed:wght@600;700&amp;display=swap">
    <link rel="stylesheet" href="{{ asset('css/runsheet.css') }}">
</head>
<body>

@auth
    @php($me = auth()->user())
    <header class="topbar">
        <a class="brand" href="{{ url('/home') }}"><span class="brand-mark"></span>RunSheet</a>

        {{-- The menu and the zone picker only appear once the email is confirmed. --}}
        @if ($me->hasVerifiedEmail())
            <nav class="nav" aria-label="Main">
                @if ($me->isDispatcher())
                    <a href="{{ route('desk.index') }}" @class(['on' => request()->routeIs('desk.index')])>Dispatch Desk</a>
                    <a href="{{ route('desk.tasks') }}" @class(['on' => request()->routeIs('desk.tasks')])>Task Board</a>
                    <a href="{{ route('desk.drivers') }}" @class(['on' => request()->routeIs('desk.drivers')])>Driver Roster</a>
                    <a href="{{ route('desk.accounts') }}" @class(['on' => request()->routeIs('desk.accounts')])>Accounts</a>
                @else
                    <a href="{{ route('runsheet.index') }}" @class(['on' => request()->routeIs('runsheet.*')])>
                        My Run Sheet
                        @if (($badgeCount = $me->waitingCount()) > 0)
                            <span class="badge" title="Tasks waiting for your confirmation">{{ $badgeCount }}</span>
                        @endif
                    </a>
                @endif
            </nav>

            {{-- Feature 2: the dropdown. Picking a zone is saved in the session (see ZoneController). --}}
            <form method="POST" action="{{ route('zone.update') }}" class="zone">
                @csrf
                <label for="zone-picker">Zone</label>
                <select id="zone-picker" name="zone" onchange="this.form.submit()">
                    @foreach (\App\Support\Zone::all() as $zoneName)
                        <option value="{{ $zoneName }}" @selected($zoneName === \App\Support\Zone::current())>{{ $zoneName }}</option>
                    @endforeach
                </select>
                <noscript><button class="btn btn-small" type="submit">Switch</button></noscript>
            </form>
        @else
            <span class="nav"></span>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-ghost btn-small" type="submit">Sign out</button>
        </form>
    </header>
@endauth

@guest
    <header class="topbar">
        <a class="brand" href="{{ url('/home') }}"><span class="brand-mark"></span>RunSheet</a>
    </header>
@endguest

<main class="page @yield('page_class')">
    {{-- Flash messages: register, sign in, zone switch and sign out all show up here. --}}
    @if (session('status'))
        <p class="flash" role="status">{{ session('status') }}</p>
    @endif
    @if (session('warning'))
        <p class="flash flash-warn" role="alert">{{ session('warning') }}</p>
    @endif

    @yield('content')
</main>

</body>
</html>
