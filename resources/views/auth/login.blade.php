@extends('layouts.app')

@section('title', 'Sign in')
@section('page_class', 'page-narrow')

@section('content')
    <section class="auth-card">
        <h1>Sign in</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">
            @error('password')<p class="field-error">{{ $message }}</p>@enderror

            <button class="btn btn-block" type="submit">Sign in</button>
        </form>

        <p class="auth-links">
            <a href="{{ route('password.request') }}">Forgot your password?</a>
            <a href="{{ route('register') }}">Create an account</a>
        </p>
    </section>
@endsection
