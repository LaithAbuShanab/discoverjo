@extends('admin.master')
@section('title', __('app.dashboard-contact'))
@section('contact-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.contact')])
                <!-- end page title -->

                <div class="row" style="margin-top: 2.5%;">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <h4 class="card-title"></h4>
                                <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{ __('app.id') }}</th>
                                            <th class="text-center">{{ __('app.name') }}</th>
                                            <th class="text-center">{{ __('app.email') }}</th>
                                            <th class="text-center">{{ __('app.subject') }}</th>
                                            <th class="text-center">{{ __('app.status') }}</th>
                                            <th class="text-center">{{ __('app.action') }}</th>
                                        </tr>
                                    </thead>


                                    <tbody>
                                        @foreach ($contacts as $key => $contact)
                                            <tr @if($contact['status'] =="Unseen") class="table-warning" @endif>
                                                <td class="text-center col-1">{{ $contact['id'] }}</td>
                                                <td class="text-center col-2">{{ $contact['name'] }}</td>
                                                <td class="text-center col-2">{{ $contact['email'] }}</td>
                                                <td class="text-center col-2">{{ $contact['subject'] }}</td>
                                                <td class="text-center col-2">{{ $contact['status'] }}</td>
                                                <td class="text-center col-2">
{{--                                                    @if(AdminPermission('show contact'))--}}
                                                    <a class="btn btn-outline-primary btn-sm" title="Show"
                                                       href="{{ route('admin.contact.show', $contact['id']) }}">
                                                        <i class="fas fa-eye" title="show"></i>
                                                    </a>
{{--                                                    @endif--}}

                                                    {{--                                                        @if (AdminPermission('edit contact')) --}}
                                                    <form method="post"
                                                          action="{{ route('admin.contact.unseen', $contact['id']) }}"
                                                          style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                title="Delete" style="padding-bottom: 1px;"
                                                                onclick="return confirm('Are you sure you want to unseen?')">
                                                            <i class="ri-eye-off-line" title="unseen"></i>
                                                        </button>
                                                    </form>


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
@endpush
