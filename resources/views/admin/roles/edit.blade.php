@extends('admin.master')
@section('title', __('app.dashboard-role'))
@section('role-active', 'active')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.update-role')])
                <!-- end page title -->
                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>

                            <form method="post" action="{{ route('admin.roles.update', $role) }}">
                                @csrf
                                @method('put')
                                <input type="hidden" name="id" value="{{ $role->id }}">
                                <div class="row">
                                    @foreach ($role->getTranslations('name_i18n') as $key => $value)
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="name{{ $key }}">{{ __('app.name-' . $key) }}</label>
                                                <input type="text" class="form-control"
                                                    placeholder="{{ __('app.role-' . $key) }}"
                                                    name="name_{{ $key }}" id="name{{ $key }}"
                                                    value="{{ old('name_' . $key, $value) }}" required>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('app.guard') }}</label>
                                            <select class="form-select" name="guard" id="guard" readonly>
                                                <option value="{{ $role->guard_name }}" selected>
                                                    {{ $role->guard_name }}</option>

                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <hr>
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <label class="form-label"
                                                style="font-size:16px;font-weight:bold">{{ __('app.permissions') }}</label>

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    @foreach ($permissions as $key => $permission)
                                        <div class="col-md-4">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                                    id="formCheck{{ $key }}" value="{{ $permission['name'] }}"
                                                    @if ($role->getAllPermissions()->pluck('name')->contains($permission['name'])) checked @endif>
                                                <label class="form-check-label"
                                                    for="formCheck{{ $key }}">{{ $permission['name_i18n'] }}</label>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                                <div>
                                    <button class="btn btn-primary" type="submit">{{ __('app.submit') }}</button>
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
