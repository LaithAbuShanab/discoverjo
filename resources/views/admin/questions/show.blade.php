@extends('admin.master')
@section('title', __('app.dashboard-questions'))
@section('question-active', 'active')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                @include('layouts.admin.title', ['title' => __('app.update-question-chain')])
                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>
                            <livewire:livewire.question-chain-update :id="$question['id']" :mainQuestion="$question['question']" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.admin.footer')
    </div>
@endsection
