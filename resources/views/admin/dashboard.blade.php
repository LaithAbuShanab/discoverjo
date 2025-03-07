@extends('admin.master')
@section('title', __('app.dashboard'))
@section('dashboard-active', 'active')
@section('content')

    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.dashboard')])

                <div class="row mt-5">
                    <div class="col-xl-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-truncate font-size-14 mb-2">{{ __('app.all-admins') }}</p>
                                        <h4 class="mb-2">{{ $admins }}</h4>
                                    </div>
                                    <div class="avatar-sm">
                                        <span class="avatar-title bg-light text-primary rounded-3">
                                            <i class="ri-user-3-line font-size-24"></i>
                                        </span>
                                    </div>
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
