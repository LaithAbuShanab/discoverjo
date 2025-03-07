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
                                <p class="card-text "><strong>{{__('app.first_name')}}:</strong>{{ $guide['first_name'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.last_name')}}:</strong> {{ $guide['last_name'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.username')}}:</strong> {{ $guide['username'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.birthday')}}:</strong> {{ $guide['birthday'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.sex')}}:</strong> {{ $guide['sex'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.email')}}:</strong> {{ $guide['email'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.phone_number')}}:</strong> {{ $guide['phone_number'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.status')}}:</strong> {{ $guide['status'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.description')}}:</strong> {{ $guide['description'] }}</p>
                                {{-- Check and display gallery images --}}
                                <hr style="background-color: black;">
                                <h3 class="card-title ">Image</h3>

                                <div class="col-md-3">
                                    <img src="{{ $guide['image'] }}" alt="avatar Image" class="img-fluid mb-3">
                                </div>

                                <hr style="background-color: black;">
                                <div class="col-md-3">
                                    <a href="{{ $guide['file'] }}" target="_blank">Download File</a>
                                </div>



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
