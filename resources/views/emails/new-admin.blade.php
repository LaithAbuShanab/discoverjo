@extends('emails.master')
@section('title', __('app.new_admin_notification'))
@section('content')
    <!-- Header -->
    <div class="email-header">
        <img src="{{ asset('assets/logo_eyes_yellow.jpeg') }}"
            alt="{{ __('app.logo', [], $user->lang) }}">
    </div>

    <!-- Body -->
    <div class="email-body">
        <h1>{{ __('app.new_admin_notification', [], $user->lang) }}</h1>

        <p>{{ __('app.new_admin_message', ['name' => $user->name], $user->lang) }}</p>

        <p style="margin: 0px"><strong>{{ __('app.login_email', [], $user->lang) }}:</strong> {{ $user->email }}</p>
        <p style="margin: 0px"><strong>{{ __('app.temporary_password', [], $user->lang) }}:</strong> {{ $password }}</p>

        <a href="{{ url('/admin') }}" class="button">
            {{ __('app.login_to_admin_panel', [], $user->lang) }}
        </a>

        <p>{{ __('app.please_change_password_after_login', [], $user->lang) }}</p>
    </div>
@endsection
