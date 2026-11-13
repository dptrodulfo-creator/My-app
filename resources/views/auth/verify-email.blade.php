@extends('layouts.app')

@section('title', 'Check your inbox')
@section('page_class', 'page-narrow')

@section('content')
    <section class="auth-card">
        <h1>Check your inbox</h1>
        <p>We sent a confirmation link to <strong>{{ auth()->user()->email }}</strong>. Click it to unlock RunSheet.</p>
        <p class="muted">Nothing there? Look in your spam folder, or ask for a fresh link.</p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn" type="submit">Send the link again</button>
        </form>
    </section>
@endsection
