@extends('admin.master')
@section('title', __('app.dashboard-role'))
@section('role-active', 'active')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.create-role')])
                <!-- end page title -->
                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>

                            <form method="post" action="{{ route('admin.roles.store') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="name_en">{{ __('app.name-en') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.role-en') }}"
                                                name='name_en' id='name_en' value="{{ old('name_en') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('app.name-ar') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.role-ar') }}"
                                                name='name_ar' value="{{ old('name_ar') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('app.guard') }}</label>
                                            <select class="form-select" name="guard" id="guard"
                                                onchange="changeGuard()">
                                                <option value="">{{ __('app.select-one') }}</option>
                                                <option value="admin">{{ __('app.admin') }}</option>
                                                <option value="planner">{{ __('app.planner') }}</option>
                                                <option value="user">{{ __('app.user') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-none" id="labelPermission">
                                    <hr>
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <label class="form-label"
                                                style="font-size:16px;font-weight:bold">{{ __('app.permissions') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="permissions">

                                </div>
                                <div style="text-align: end">
                                    <button class="btn btn-primary" type="submit">{{ __('app.create') }}</button>
                                </div>
                            </form>


                        </div>
                    </div>
                    <!-- end card -->
                </div>
            </div>
        </div>
        <!-- End Page-content -->
        @include('layouts.admin.footer')
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets') }}/js/js/roles.js"></script>
@endpush
