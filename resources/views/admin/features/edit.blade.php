@extends('admin.master')
@section('title', __('app.dashboard-feature'))
@section('feature-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.edit-feature')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>



                            <form method="post" action="{{ route('admin.features.update', $feature['id']) }}" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <input type="hidden" name="id" value="{{ $feature['id'] }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="name_en">{{ __('app.name-en') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.feature-en') }}" id="name_en" name="name_en"
                                                value="{{ old('name_en', $feature['name_en']) }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="name_ar">{{ __('app.name-ar') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.feature-ar') }}" id="name_ar" name="name_ar"
                                                value="{{ old('name_ar', $feature['name_ar']) }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="imageInput">{{ __('app.image-active') }}</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" name="image_active" id="imageInput">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <img src="{{ $feature['image_active'] != null ? asset($feature['image_active']) : asset('assets/images/category.jpg') }}"
                                                alt="{{ $feature['image_active'] != null ? $feature['image_active'] : 'avatar' }}" id="previewImage" style="width: 100px; height: 100px;">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="imageInput2">{{ __('app.image-inactive') }}</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" name="image_inactive" id="imageInput2">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6" style="margin-top: 33px;">
                                        <div class="mb-3">
                                            <img src="{{ $feature['image_inactive'] != null ? asset($feature['image_inactive']) : asset('assets/images/category.jpg') }}"
                                                alt="{{ $feature['image_inactive'] != null ? $feature['image_inactive'] : 'avatar' }}" id="previewImage2" style="width: 100px; height: 100px;">
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

@push('script')
    <script type="text/javascript">
        $(document).ready(function() {

            $('#imageInput').change(function() {
                displayImagePreview(this, '#previewImage');
            });

            $('#imageInput2').change(function() {
                displayImagePreview(this, '#previewImage2');
            });

            function displayImagePreview(input, previewElement) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $(previewElement).attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }
        });
    </script>
@endpush
