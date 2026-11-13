@extends('layouts.app')

@section('title', 'Forgot your password?')
@section('page_class', 'page-narrow')

@section('content')
    <section class="auth-card">
        <h1>Forgot your password?</h1>
        <p class="muted">Enter your email and we’ll send a one-time link for choosing a new password.</p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror

            <button class="btn btn-block" type="submit">Email me the link</button>
        </form>

        <p class="auth-links">
            <a href="{{ route('login') }}">Back to sign in</a>
        </p>
    </section>
@endsection
