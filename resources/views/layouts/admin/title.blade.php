@php
    $count = \App\Models\Place::all()->count();
@endphp
<div class="row" style="background-color: #fff;  padding-top: 1.5% ;margin: -22px -25px 0 -25px;">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $title }} ( {{$count}})</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('app.rehletna') }}</a></li>
                    <li class="breadcrumb-item active">{{ $title }}
                    </li>
                </ol>
            </div>

        </div>
    </div>
</div>
