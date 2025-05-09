@extends('emails.master')
@section('title', __('app.account_active_notification'))
@section('content')
    <!-- Header -->
    <div class="email-header">
        <img src="{{ asset('assets/logo_eyes_yellow.jpeg') }}"
             alt="{{ __('app.logo', [], $user->lang) }}">
    </div>


    <!-- Body -->
    <div class="email-body">
        <h1>{{ \Illuminate\Support\Facades\Lang::get('app.account_active_notification', [], $user->lang) }}</h1>
        <p> {{ __('app.account_active_message', ['name' => $user->username], $user->lang) }}</p>
        <p> {{ __('app.thankYou', [], $user->lang) }}</p>
    </div>
@endsection
