@extends('admin.master')
@section('title', __('app.dashboard-admins'))
@section('admin-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.edit-admin')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>



                            <form method="post" action="{{ route('admin.admins.update', $admin['id']) }}" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <input type="hidden" name="id" value="{{ $admin['id'] }}">
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="username">{{ __('app.username') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.enter-username') }}" name="name" value="{{ old('name', $admin['name']) }}" id="username"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="email">{{ __('app.email') }}</label>
                                            <input type="email" class="form-control" placeholder="{{ __('app.enter-email') }}" name="email" value="{{ old('email', $admin['email']) }}" id="email"
                                                required>
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
                                                    <option value="{{ $role['name'] }}" @if ($admin['role'] == $role['name']) checked @endif>
                                                        {{ $role['name_i18n'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <img src="{{ $admin['image'] != null ? asset($admin['image']) : asset('assets/images/avatar.png') }}"
                                                alt="{{ $admin['image'] != null ? $admin['name'] : 'avatar' }}" id="previewImage" style="width: 80px; height: 80px;">
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
