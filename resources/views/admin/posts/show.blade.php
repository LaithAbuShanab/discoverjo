@extends('admin.master')
@section('title', __('app.dashboard-post'))
@section('post-active', 'active')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.post')])
                <!-- end page title -->

                <div class="row justify-content-center" style="margin-top: 2.5%;">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="m-5">
                                <h2 class="card-title ">{{__('post.creator')}}:{{ $post['creator'] }}</h2>
                                <hr style="background-color: black;">
                                <p class="card-text ">{{__('post.content')}}:{{ $post['content'] }}</p>
                                <hr style="background-color: black;">
                                <p class="card-text "><strong>{{__('post.privacy')}}:</strong> {{ $post['privacy'] ==1 ? __('app.public'):__('app.only-me')}}</p>
                                @if (!empty($post['media']))
                                    <hr style="background-color: black;">
                                    <h3 class="card-title ">Gallery</h3>
                                    <div class="row">
                                        @foreach ($post['media'] as $image)
                                            <div class="col-md-3">
                                                <img src="{{ $image['url'] }}" alt="Gallery Image" class="img-fluid mb-3">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page-content -->
        @include('layouts.admin.footer')
    </div>
@endsection
