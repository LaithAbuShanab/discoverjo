@extends('emails.master')
@section('title', __('app.verifyEmail'))
@section('content')
    <!-- Header -->
    <div class="email-header">
        <img src="{{ asset('assets/images/logo_yellow2.JPG') }}"
             alt="{{ __('app.logo') }}">
    </div>

    <!-- Body -->
    <div class="email-body">
        <h1>{{ __('app.verifyEmail') }}</h1>
        <p>{{ __('app.verifyEmailMessage') }}</p>
        <a href="{{ $url }}" class="button">{{__('app.verifyEmail') }}</a>
        <p>{{ __('app.thankYou') }}</p>
    </div>
@endsection
