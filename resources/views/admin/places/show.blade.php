@extends('admin.master')
@section('title', __('app.place.dashboard-places'))
@section('place-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.Place')])
                <!-- end page title -->

                <div class="row justify-content-center" style="margin-top: 2.5%;">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body"
                                style="background-image: url('{{ $place['main_image'] }}'); background-size: cover; background-position: center;height: 250px">
                                <h2 class="card-title text-white">{{ $place['name'] }}</h2>
                            </div>
                            <div class="m-5">
                                <p class="card-text ">{{ $place['description'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.address')}}:</strong> {{ $place['address'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.status')}}:</strong> {{ $place['business_status'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.place.google-map-url')}}:</strong> <a
                                        href="{{ $place['google_map_url'] }}"
                                        class="">{{ $place['google_map_url'] }}</a></p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.phone')}}:</strong> {{ $place['phone_number'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.price')}}:</strong> {{ $place['price_level'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.website')}}:</strong> <a href="{{ $place['website'] }}"
                                        class="">{{ $place['website'] }}</a></p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.rating')}}:</strong> {{ $place['rating'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.total-user-rating')}}:</strong> {{ $place['total_user_rating'] }}
                                </p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('app.region')}}:</strong> {{ $place['region'] }}</p>


                                {{-- Check and display opening hours --}}
                                @if (!empty($place['opening_hours']))
                                    <hr style="background-color: black;">
                                    <h3 class="card-title ">{{__('app.opening-hours')}}:</h3>
                                    <ul class="card-text ">
                                        @foreach ($place['opening_hours'] as $hours)
                                            @foreach ($hours as $hour)
                                                <li>{{ $hour['day_of_week'] }}: {{ $hour['opening_time'] }} -
                                                    {{ $hour['closing_time'] }}</li>
                                            @endforeach
                                        @endforeach
                                    </ul>
                                @endif

                                {{-- Check and display gallery images --}}
                                @if (!empty($place['gallery']))
                                    <hr style="background-color: black;">
                                    <h3 class="card-title ">{{__('app.gallery')}}:</h3>
                                    <div class="row">
                                        @foreach ($place['gallery'] as $image)
                                            <div class="col-md-3">
                                                <img src="{{ $image['url'] }}" alt="Gallery Image" class="img-fluid mb-3">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <hr style="background-color: black;">
                                {{-- Check and display tags --}}
                                <h3 class="card-title ">{{__('app.tags')}}:</h3>

                                <ul class="card-text ">
                                    @foreach ($place['tags'] as $tag)
                                        <li>{{ $tag['name'] }}</li>
                                    @endforeach
                                </ul>

                                <hr style="background-color: black;">

                                {{-- Check and display features --}}
                                <h3 class="card-title ">{{__('app.Feature')}}:</h3>
                                <ul class="card-text ">
                                    @foreach ($place['features'] as $feature)
                                        <li>{{ $feature['name'] }}</li>
                                    @endforeach
                                </ul>
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
