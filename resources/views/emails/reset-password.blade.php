@extends('emails.master')
@section('title', __('app.reset_password_notification'))
@section('content')
    <!-- Header -->
    <div class="email-header">
        <img src="{{ asset('assets/logo_black_without_background.png') }}"
             alt="{{ __('app.logo') }}">
    </div>

    <!-- Body -->
    <div class="email-body">
        <h1>{{ __('app.reset_password') }}</h1>
        <p>{{ __('app.received_password_reset_request') }}</p>
        <a href="{{ $url }}" class="button">{{ __('app.reset_password') }}</a>
        <p>{{ __('app.password_reset_link_expire', ['count' => $expiresIn]) }}</p>
        <p>{{ __('app.no_further_action_required') }}</p>
    </div>
@endsection
