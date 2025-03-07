@extends('admin.master')
@section('title', __('app.dashboard-trips'))
@section('trips-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.trip.trip')])
                <!-- end page title -->

                <div class="row justify-content-center" style="margin-top: 2.5%;">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body"
             >
                                <h2 class="card-title text-white">{{__('trip.name')}}:{{ $trip['name'] }}</h2>
                            </div>
                            <div class="m-5">
                                <p class="card-text ">{{__('trip.description')}}:{{ $trip['description'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('trip.place')}}:</strong> {{ $trip['place'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('trip.cost')}}:</strong> {{ $trip['cost'] }} JOD</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('trip.min_age')}}:</strong> {{ $trip['min_age'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('trip.max_age')}}:</strong> {{ $trip['max_age'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('trip.sex')}}:</strong> {{ $trip['sex'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('trip.datetime')}}:</strong> {{ $trip['datetime'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('trip.attendance_number')}}:</strong> {{ $trip['attendance_number'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('trip.status')}}:</strong> {{ $trip['status'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('trip.creator')}}:</strong> {{ $trip['creator'] }}</p>
                                <hr style="background-color: black;">

                                {{-- Check and display tags --}}
                                <h3 class="card-title ">Users Trip</h3>

                                <ul class="card-text ">
                                    @foreach ($trip['users_trip'] as $user)
                                        <li>{{ $user }}</li>
                                    @endforeach
                                </ul>

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
