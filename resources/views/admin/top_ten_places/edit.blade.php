@extends('admin.master')
@section('title', __('app.dashboard-category'))
@section('category-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.edit-category')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>

                            <form method="post" action="{{ route('admin.topTenPlaces.update', $topTen['id']) }}">
                                @csrf
                                @method('put')
                                <input type="hidden" name="id" value="{{ $topTen['id'] }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="place_id">{{ __('app.places') }}</label>
                                            <select class="select2 form-control" name="place_id" data-placeholder="{{ __('app.choose...') }}" required id="place_id">
                                                <option value="{{ $topTen['place_id'] }}" selected>{{ $topTen['place_name'] }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="rank">{{ __('app.rank') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.rank') }}" name="rank" value="{{ old('rank', $topTen['rank']) }}" id="rank" required>
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
