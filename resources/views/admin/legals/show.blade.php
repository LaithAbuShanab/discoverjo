@extends('admin.master')
@section('title', __('app.legal.dashboard-legals'))
@section('legal-legal', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.places')])
                <!-- end page title -->

                <div class="row justify-content-center" style="margin-top: 2.5%;">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body"
                                style="background-image: url('{{ $event['image'] }}'); background-size: cover; background-position: center;height: 250px">
                                <h2 class="card-title text-white">{{ $event['name'] }}</h2>
                            </div>
                            <div class="m-5">
                                <p class="card-text ">{{ $event['description'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>Address:</strong> {{ $event['address'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>Start Time:</strong> {{ $event['start_datetime'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>End Time:</strong> {{ $event['end_datetime'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>Status:</strong> {{ $event['status'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>Link:</strong> <a
                                        href="{{ $event['link'] }}"
                                        >{{ $event['link'] }}</a></p>
                                <hr style="background-color: black;">

                                <p class="card-text "><strong>Price:</strong> {{ $event['price'] }}</p>
                                <hr style="background-color: black;">

                                <p class="card-text "><strong>Region:</strong> {{ $event['region'] }}</p>

                                <hr style="background-color: black;">
                                {{-- Check and display tags --}}
                                <h3 class="card-title ">Organizers</h3>

                                <ul class="card-text ">
                                    @foreach ($event['organizers'] as $organizer)
                                        <li>{{ $organizer['name'] }}</li>
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
