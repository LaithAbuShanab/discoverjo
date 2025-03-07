@extends('admin.master')
@section('title', __('app.dashboard-notification'))
@section('notification-active', 'active')
@section('content')


    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.send-notification-for-all-user')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>



                            <form method="post" action="{{ route('admin.notifications.store') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="title_en">{{ __('app.title_en') }}</label>
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('app.title_en') }}" name="title_en"
                                                value="{{ old('title_en') }}" id="title_en" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="title_ar">{{ __('app.title_ar') }}</label>
                                            <input type="text" class="form-control"
                                                   placeholder="{{ __('app.title_ar') }}" name="title_ar"
                                                   value="{{ old('title_ar') }}" id="title_ar" required>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="body_en">{{ __('app.body_en') }}</label>
                                            <input type="text" class="form-control"
                                                   placeholder="{{ __('app.body_en') }}" name="body_en"
                                                   value="{{ old('body_en') }}" id="title_en" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="body_ar">{{ __('app.body_ar') }}</label>
                                            <input type="text" class="form-control"
                                                   placeholder="{{ __('app.body_ar') }}" name="body_ar"
                                                   value="{{ old('body_ar') }}" id="body_ar" required>
                                        </div>
                                    </div>

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
