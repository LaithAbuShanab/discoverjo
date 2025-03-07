@extends('admin.master')
@section('title', __('app.dashboard-slider'))
@section('slider-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.create-slider')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>



                            <form method="post" action="{{ route('admin.sliders.store') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="title_en">{{ __('app.title-en') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.title-en') }}" name="title_en" value="{{ old('title_en') }}" id="title_en" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="title_ar">{{ __('app.title_ar') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.title-ar') }}" name="title_ar" value="{{ old('title_ar') }}" id="title_ar" required>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="content_en">{{ __('app.content-en') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.content-en') }}" name="content_en" value="{{ old('content_en') }}" id="content_en" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="content_ar">{{ __('app.content_ar') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.content-ar') }}" name="content_ar" value="{{ old('content_ar') }}" id="content_ar"
                                                required>
                                        </div>
                                    </div>

                                </div>


                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="type">{{ __('app.type') }}</label>
                                            <select class="form-select" name="type" id="type">
                                                <option value="">{{ __('app.select-one') }}</option>
                                                <option value="onboarding">{{ __('app.onboarding') }}</option>
                                                <option value="banner">{{ __('app.banner') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="status-slider">{{ __('app.status') }}</label>
                                            <select class="form-select" name="status" id="status-slider">
                                                <option value="">{{ __('app.select-one') }}</option>
                                                <option value=1>{{ __('app.active') }}</option>
                                                <option value=0>{{ __('app.inactive') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>



                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('app.priority') }}</label>
                                            <input type="number" class="form-control" id="priority" placeholder="{{ __('app.priority-order') }}" name="priority" value="{{ old('priority') }}" required
                                                min="1">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="imageInput">{{ __('app.image') }}</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" name="image" id="imageInput">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <img src="{{ asset('assets/images/category.jpg') }}" alt="" id="previewImage" style="width: 80px; height: 80px;">
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
