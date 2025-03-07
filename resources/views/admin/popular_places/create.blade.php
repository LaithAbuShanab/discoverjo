@extends('admin.master')
@section('title', __('app.popular-place.dashboard-popular'))
@section('popular-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.popular-place.create-popular')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>



                            <form method="post" action="{{ route('admin.popularPlaces.store') }}">
                                @csrf

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="place_id">{{ __('app.places') }}</label>
                                            <select class="select2 form-control" name="place_id" id="place_id" data-placeholder="{{ __('app.choose...') }}" required>
                                                <!-- Options will be loaded dynamically -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="local_price">{{ __('app.popular-place.local-price') }}</label>
                                            <input type="text" class="form-control" name="local_price" value="{{ old('local_price') }}" id="local_price" required>
                                        </div>

                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="foreign_price">{{ __('app.popular-place.foreign-price') }}</label>
                                            <input type="text" class="form-control" name="foreign_price" value="{{ old('foreign_price') }}" id="foreign_price" required>
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
    <script>
        $(document).ready(function() {
            $('#place_id').select2({
                placeholder: "{{ __('app.choose...') }}",
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('admin.ajax.places.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // The search query
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(place) {
                                return {
                                    id: place.id,
                                    text: place.text
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            // $('#place_id').on('select2:open', function() {
            //     console.log('Select2 opened!');
            // });
        });
    </script>
@endpush
