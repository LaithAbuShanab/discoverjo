@extends('admin.master')
@section('title', __('app.place.dashboard-places'))
@section('place-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                @include('layouts.admin.title', ['title' => __('app.place.places')])

                <div class="row" style="margin-top: 2.5%;">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <h4 class="card-title"></h4>
                                <table class="table table-hover table-bordered text-center" id="sampleTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{ __('app.id') }}</th>
                                            <th class="text-center">{{ __('app.name') }}</th>
                                            <th class="text-center">{{ __('app.region') }}</th>
                                            <th class="text-center">{{ __('app.image') }}</th>
                                            <th class="text-center">{{ __('app.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.admin.footer')
    </div>
@endsection

@push('script')
    <script type="text/javascript">
        $(function() {
            $('#sampleTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.ajax.place.index') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name.{{ app()->getLocale() }}',
                        name: 'name'
                    }, // Adjust this based on how you're storing localized names
                    {
                        data: 'region_name',
                        name: 'region_name'
                    }, // Ensure this matches the column returned from the server
                    {
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, full, meta) {
                            return '<img src="' + data + '" style="height: 50px;width:50px;"/>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
    </script>
@endpush
