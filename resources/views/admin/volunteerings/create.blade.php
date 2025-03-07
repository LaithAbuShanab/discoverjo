@extends('admin.master')
@section('title', __('app.dashboard-volunteering'))
@section('volunteering-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.create-volunteering')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>
                            <form method="post" action="{{ route('admin.volunteering.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip01">{{ __('app.name-en') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.place-en') }}" name="name_en" value="{{ old('name_en') }}" id="validationTooltip01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip02">{{ __('app.name-ar') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.place-ar') }}" name="name_ar" value="{{ old('name_ar') }}" id="validationTooltip02" required>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip03">{{ __('app.description-en') }}</label>
                                            <div>
                                                <textarea required name="description_en" class="form-control" rows="3" id="validationTooltip03" placeholder="{{ __('app.description-enter-en') }}">{{ old('description_en') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip04">{{ __('app.description-ar') }}</label>
                                            <div>
                                                <textarea required name="description_ar" class="form-control" rows="3" id="validationTooltip04" placeholder="{{ __('app.description-enter-ar') }}">{{ old('description_ar') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip05">{{ __('app.address-en') }}</label>
                                            <input required type="text" class="form-control" placeholder="{{ __('app.address-enter-en') }}" name="address_en" value="{{ old('address_en') }}"
                                                id="validationTooltip05">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip06">{{ __('app.address-ar') }}</label>
                                            <input required type="text" class="form-control" placeholder="{{ __('app.address-enter-ar') }}" name="address_ar" value="{{ old('address_ar') }}"
                                                id="validationTooltip06">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip05">{{ __('app.start-time') }}</label>
                                            <input required type="datetime-local" class="form-control" placeholder="{{ __('app.start_datetime-enter-en') }}" name="start_datetime"
                                                value="{{ old('start_datetime') }}" id="validationTooltip05">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip06">{{ __('app.end_datetime') }}</label>
                                            <input required type="datetime-local" class="form-control" placeholder="{{ __('app.end_datetime-enter-ar') }}" name="end_datetime"
                                                value="{{ old('end_datetime') }}" id="validationTooltip06">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip07">{{ __('app.link') }}</label>
                                            <input required type="url" class="form-control" placeholder="{{ __('app.link-enter') }}" name="link" value="{{ old('link') }}"
                                                id="validationTooltip07">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip11">{{ __('app.hours_worked') }}</label>
                                            <input type="number" class="form-control" placeholder="{{ __('app.hours_worked-enter') }}" name="hours_worked" value="{{ old('hours_worked') }}"
                                                id="validationTooltip11" step="0.001">
                                        </div>
                                    </div>

                                </div>




                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip16">{{ __('app.regions') }}</label>
                                            <select class="form-select" name="region_id" id="validationTooltip16">
                                                <option value="" selected>{{ __('app.select-one') }}</option>
                                                @foreach ($regions as $region)
                                                    <option value="{{ $region['id'] }}">{{ $region['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip11">{{ __('app.attendance-number') }}</label>
                                            <input type="number" class="form-control" placeholder="{{ __('app.attendance-number') }}" name="attendance_number"
                                                value="{{ old('attendance_number') }}" id="validationTooltip11">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip17">{{ __('app.status') }}</label>
                                            <select class="form-select" name="status" id="validationTooltip17">
                                                <option value="">{{ __('app.select-one') }}</option>
                                                <option value="1">{{ __('app.active') }}</option>
                                                <option value="0">{{ __('app.inactive') }}</option>

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="validationTooltip18">{{ __('app.organizers') }}</label>
                                            <select class="select2 form-control select2-multiple" name="organizers_id[]" multiple data-placeholder="{{ __('app.choose...') }}" required
                                                id="validationTooltip18">
                                                <option value="">{{ __('app.select-one') }}</option>
                                                @foreach ($organizers as $organizer)
                                                    <option value="{{ $organizer['id'] }}">{{ $organizer['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="mainImageInput">{{ __('app.image') }}</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" name="image" id="mainImageInput" required accept="image/*">
                                            </div>
                                            <small class="form-text text-muted">{{ __('app.choose-a-main-image-for-your-category.') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <img src=" {{ asset('assets/images/category.jpg') }} " alt="{{ __('app.image') }}" id="mainPreviewImage" style="width: 80px; height: 80px;">
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
            $("#validationTooltip17").select2({
                placeholder: "{{ __('app.select-one') }}",
                width: "100%",
            });
            $("#validationTooltip16").select2({
                placeholder: "{{ __('app.select-one') }}",
                width: "100%",
            });
            $("#validationTooltip15").select2({
                placeholder: "{{ __('app.select-one') }}",
                width: "100%",
            });


            $('#mainImageInput').change(function() {
                displayImagePreview(this, '#mainPreviewImage');
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
