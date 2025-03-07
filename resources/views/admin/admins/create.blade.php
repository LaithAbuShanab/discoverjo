@extends('admin.master')
@section('title', __('app.dashboard-admins'))
@section('admin-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                @include('layouts.admin.title', ['title' => __('app.create-admin')])
                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>

                            <form method="post" action="{{ route('admin.admins.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="username">{{ __('app.username') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.enter-username') }}" name="name" value="{{ old('name') }}" id="username" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="email">{{ __('app.email') }}</label>
                                            <input type="email" class="form-control" placeholder="{{ __('app.enter-email') }}" name="email" value="{{ old('email') }}" id="email" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="password">{{ __('app.password') }}</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" placeholder="{{ __('app.enter-password') }}" name="password" value="{{ old('password') }}" id="password"
                                                    required>
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="confirm-password">{{ __('app.confirm-password') }}</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" placeholder="{{ __('app.enter-confirm-password') }}" name="password_confirmation"
                                                    value="{{ old('confirm_password') }}" id="confirm-password" required>
                                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="imageInput">{{ __('app.image') }}</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" name="image" id="imageInput">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('app.role') }}</label>
                                            <select class="form-select" required="" name="role">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role['name'] }}">{{ $role['name_i18n'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <img src="{{ asset('assets/images/avatar.png') }}" alt="" id="previewImage" style="width: 80px; height: 80px;">
                                        </div>
                                    </div>
                                </div>

                                <div style="text-align: end;">
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

@push('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $("#togglePassword").click(function() {
                togglePasswordVisibility("password");
            });

            $("#toggleConfirmPassword").click(function() {
                togglePasswordVisibility("confirm_password");
            });

            function togglePasswordVisibility(fieldName) {
                var passwordField = $("input[name='" + fieldName + "']");
                var passwordFieldType = passwordField.attr('type');
                if (passwordFieldType == 'password') {
                    passwordField.attr('type', 'text');
                    $("#toggle" + fieldName.charAt(0).toUpperCase() + fieldName.slice(1) + " i").removeClass(
                        'fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    $("#toggle" + fieldName.charAt(0).toUpperCase() + fieldName.slice(1) + " i").removeClass(
                        'fa-eye-slash').addClass('fa-eye');
                }
            }

            $('#imageInput').change(function() {
                displayImagePreview(this);
            });

            function displayImagePreview(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#previewImage').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }
        });
    </script>
@endpush
