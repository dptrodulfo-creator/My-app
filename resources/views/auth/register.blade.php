@extends('layouts.app')

@section('title', 'Create account')
@section('page_class', 'page-narrow')

@section('content')
    <section class="auth-card">
        <h1>Join RunSheet</h1>
        <p class="muted">New accounts start as driver accounts. We’ll email you a link to confirm your address.</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <label for="name">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name')<p class="field-error">{{ $message }}</p>@enderror

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror

            <label for="password">Password (at least 8 characters)</label>
            <input id="password" name="password" type="password" required autocomplete="new-password">
            @error('password')<p class="field-error">{{ $message }}</p>@enderror

            <label for="password_confirmation">Type the password again</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">

            <button class="btn btn-block" type="submit">Create account</button>
        </form>

        <p class="auth-links">
            <a href="{{ route('login') }}">I already have an account</a>
        </p>
    </section>
@endsection
