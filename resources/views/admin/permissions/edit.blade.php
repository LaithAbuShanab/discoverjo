@extends('admin.master')
@section('title', __('app.dashboard-permission'))
@section('permission-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.edit-permission')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>

                            <form method="post" action="{{ route('admin.permissions.update', $permission) }}">
                                @csrf
                                @method('put')
                                <input type="hidden" name="id" value="{{ $permission->id }}">
                                <div class="row">
                                    @foreach ($permission->getTranslations('name_i18n') as $key => $value)
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('app.name-' . $key) }}</label>
                                                <input type="text" class="form-control"
                                                    placeholder="{{ __('app.permission-' . $key) }}"
                                                    name="name_{{ $key }}"
                                                    value="{{ old('name_' . $key, $value) }}" required>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('app.guard') }}</label>
                                            <select class="form-select" name="guard" required>
                                                <option value="admin" @if ($permission->guard_name == 'admin') selected @endif>
                                                    {{ __('app.admin') }}</option>
                                                <option value="planner" @if ($permission->guard_name == 'planner') selected @endif>
                                                    {{ __('app.planner') }}</option>
                                                <option value="user" @if ($permission->guard_name == 'user') selected @endif>
                                                    {{ __('app.user') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: end">
                                    <button class="btn btn-primary" type="submit">{{ __('app.update') }}</button>
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
