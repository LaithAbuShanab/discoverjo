@extends('admin.master')
@section('title', __('app.dashboard-contact'))
@section('contact-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.contact')])
                <!-- end page title -->

                <div class="row justify-content-center" style="margin-top: 2.5%;">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="m-5">
                                <p class="card-text "><strong>{{__('app.first_name')}}:</strong>{{ $user['first_name'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.last_name')}}:</strong> {{ $user['last_name'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.username')}}:</strong> {{ $user['username'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.birthday')}}:</strong> {{ $user['birthday'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.sex')}}:</strong> {{ $user['sex'] == 2 ?'female':'male'}}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.email')}}:</strong> {{ $user['email'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.phone_number')}}:</strong> {{ $user['phone_number'] }}</p>
                                <hr style="background-color: black;">
                                @if($user['status'] == 2)
                                    <p class="card-text "><strong>{{__('app.status')}}:</strong> {{__('first-login')}}</p>
                                @elseif($user['status'] == 0)
                                    <p class="card-text "><strong>{{__('app.status')}}:</strong> {{__('inactive')}}</p>
                                @elseif($user['status'] == 1)
                                    <p class="card-text "><strong>{{__('app.status')}}:</strong> {{__('active')}}</p>
                                @elseif($user['status'] == 3)
                                    <p class="card-text "><strong>{{__('app.status')}}:</strong> {{__('inactive-by-admin')}}</p>
                                @endif
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.description')}}:</strong> {{ $user['description'] }}</p>
                                {{-- Check and display gallery images --}}
                                <hr style="background-color: black;">
                                <h3 class="card-title ">Image</h3>

                                <div class="col-md-3">
                                    <img src="{{ $user['image'] }}" alt="avatar Image" class="img-fluid mb-3">
                                </div>

                                <hr style="background-color: black;">



                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page-content -->
        @include('layouts.admin.footer')
    </div>
@endsection
