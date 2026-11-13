@extends('layouts.app')

@section('title', 'Choose a new password')
@section('page_class', 'page-narrow')

@section('content')
    <section class="auth-card">
        <h1>Choose a new password</h1>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="username">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror

            <label for="password">New password (at least 8 characters)</label>
            <input id="password" name="password" type="password" required autofocus autocomplete="new-password">
            @error('password')<p class="field-error">{{ $message }}</p>@enderror

            <label for="password_confirmation">Type it again</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">

            <button class="btn btn-block" type="submit">Save new password</button>
        </form>
    </section>
@endsection
