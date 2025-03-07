@extends('admin.master')
@section('title', __('app.dashboard-suggestion-places'))
@section('suggestion-places-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.suggestion-places')])
                <!-- end page title -->

                <div class="row justify-content-center" style="margin-top: 2.5%;">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="m-5">
                                <p class="card-text "><strong>Name:</strong>{{ $suggestion_place['place_name'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>Address:</strong> {{ $suggestion_place['address'] }}</p>

                                @if (!empty($suggestion_place['images']))
                                    <hr style="background-color: black;">
                                    <h3 class="card-title ">Images</h3>
                                    <div class="row">
                                        @foreach ($suggestion_place['images'] as $image)
                                            <div class="col-md-3">
                                                <img src="{{ $image['url'] }}" alt="Gallery Image" class="img-fluid mb-3">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

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
