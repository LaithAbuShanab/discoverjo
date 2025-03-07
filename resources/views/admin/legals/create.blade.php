@extends('admin.master')
@section('title', __('app.legal.dashboard-legals'))
@section('legal-legal', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.create-legal')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>
                            <livewire:livewire.legal-create-livewire />
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
