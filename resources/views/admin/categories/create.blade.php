@extends('admin.master')
@section('title', __('app.dashboard-category'))
@section('category-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.create-category')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>

                            <form method="post" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="name_en">{{ __('app.name-en') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.category-en') }}" name="name_en" value="{{ old('name_en') }}" id="name_en" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="name_ar">{{ __('app.name-ar') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.category-ar') }}" name="name_ar" value="{{ old('name_ar') }}" id="name_ar" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('app.priority') }}</label>
                                            <input type="number" class="form-control" id="priority" placeholder="{{ __('app.priority-order') }}" name="priority" value="{{ old('priority') }}" required min="1">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="parent_id">{{ __('app.parent-category') }}</label>
                                            <select class="form-control" name="parent_id" id="parent_id">
                                                <option value="">{{ __('app.no-parent') }}</option>
                                                @foreach($categories as $category)
                                                    @include('admin.categories.partials.category_option', ['category' => $category, 'depth' => 0])
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Main Image Field -->
                                <div class="row" id="mainImageField">
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
                                            <img src="{{ asset('assets/images/category.jpg') }}" alt="" id="previewImage" style="width: 80px; height: 80px;">
                                        </div>
                                    </div>
                                </div>

                                <!-- Active and Inactive Image Fields -->
                                <div class="row" id="activeInactiveImageFields" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="imageInputActive">{{ __('app.image-active') }}</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" name="image_active" id="imageInputActive">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="margin-top: 33px;">
                                        <div class="mb-3">
                                            <img src="{{ asset('assets/images/category.jpg') }}" alt="" id="previewImageActive" style="width: 100px; height: 100px;">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="imageInputInactive">{{ __('app.image-inactive') }}</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" name="image_inactive" id="imageInputInactive">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="margin-top: 33px;">
                                        <div class="mb-3">
                                            <img src="{{ asset('assets/images/category.jpg') }}" alt="" id="previewImageInactive" style="width: 100px; height: 100px;">
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
            // Handle image previews
            $('#imageInput').change(function() {
                displayImagePreview(this, '#previewImage');
            });

            $('#imageInputActive').change(function() {
                displayImagePreview(this, '#previewImageActive');
            });

            $('#imageInputInactive').change(function() {
                displayImagePreview(this, '#previewImageInactive');
            });

            function displayImagePreview(input, previewId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(previewId).attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Show/Hide image fields based on parent_id
            $('#parent_id').change(function() {
                var parentId = $(this).val();
                if (parentId) {
                    $('#mainImageField').hide();
                    $('#activeInactiveImageFields').show();
                } else {
                    $('#mainImageField').show();
                    $('#activeInactiveImageFields').hide();
                }
            }).trigger('change'); // Trigger change on page load to set initial state
        });
    </script>
@endpush
