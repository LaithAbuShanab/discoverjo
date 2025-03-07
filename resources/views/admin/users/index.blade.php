@extends('admin.master')
@section('title', __('app.dashboard-user'))
@section('user-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.user')])
                <!-- end page title -->

                <div class="row" style="margin-top: 2.5%;">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <h4 class="card-title"></h4>
                                <table class="table table-hover table-bordered text-center" id="sampleTable">
                                    <thead>
                                    <tr>
                                        <th class="text-center">{{ __('app.id') }}</th>
                                        <th class="text-center">{{ __('app.first_name') }}</th>
                                        <th class="text-center">{{ __('app.last_name') }}</th>
                                        <th class="text-center">{{ __('app.avatar') }}</th>

                                        <th class="text-center">{{ __('app.action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($users as $key => $user)
                                        <tr>
                                            <td class="text-center col-1">{{ $user['id'] }}</td>
                                            <td class="text-center col-2">{{ $user['first_name'] }}</td>
                                            <td class="text-center col-2">{{ $user['last_name'] }}</td>
                                            <td class="text-center col-2"><img src="{{ $user['image'] != null ? asset($user['image']) : asset('assets/images/category.jpg') }}"
                                                                               alt="{{ $user['image'] != null ? $user['first_name'] : 'avatar' }}" width="50px" height="50px">
                                            </td>

                                            <td class="text-center col-2">
                                                {{--                                                    @if (AdminPermission('show contact')) --}}
                                                <a class="btn btn-outline-primary btn-sm" title="Show" href="{{ route('admin.user.show', $user['id']) }}">
                                                    <i class="fas fa-eye" title="show"></i>
                                                </a>
                                                {{--                                                    @endif --}}



                                            </td>

                                        </tr>
                                    @endforeach




                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div>
        </div>
        <!-- End Page-content -->
        @include('layouts.admin.footer')
    </div>
@endsection

@push('script')
    <!-- Buttons examples -->
    <script src="{{ asset('assets') }}/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="{{ asset('assets') }}/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="{{ asset('assets') }}/libs/jszip/jszip.min.js"></script>
    <script src="{{ asset('assets') }}/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="{{ asset('assets') }}/libs/pdfmake/build/vfs_fonts.js"></script>
    <script src="{{ asset('assets') }}/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="{{ asset('assets') }}/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="{{ asset('assets') }}/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>
    <script src="{{ asset('assets') }}/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="{{ asset('assets') }}/libs/datatables.net-select/js/dataTables.select.min.js"></script>
    <script src="{{ asset('assets') }}/js/pages/datatables.init.js"></script>

    <!-- Data table plugin-->
    <script type="text/javascript" src="{{asset("assets/js/plugins/jquery.dataTables.min.js")}}"></script>
    <script type="text/javascript" src="{{asset("assets/js/plugins/dataTables.bootstrap.min.js")}}"></script>
    <script type="text/javascript">$('#sampleTable').DataTable();</script>
@endpush
