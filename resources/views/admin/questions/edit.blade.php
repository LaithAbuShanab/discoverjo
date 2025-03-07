@extends('admin.master')

@section('title', __('app.dashboard-questions'))

@section('question-active', 'active')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                @include('layouts.admin.title', ['title' => __('app.edit-question')])
                <!-- end page title -->

                <div class="col-xl-12 mx-auto" style="margin-top: 2.5%;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title"></h4>
                            <form method="post" action="{{ route('admin.questions.update', $question['id']) }}" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <input type="hidden" name="id" value="{{ $question['id'] }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="question_en">{{ __('app.question-en') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.enter-question-en') }}" name="question_en"
                                                value="{{ old('question_en', $question['question_en']) }}" id="question_en" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="question_ar">{{ __('app.question-ar') }}</label>
                                            <input type="text" class="form-control" placeholder="{{ __('app.enter-question-en') }}" name="question_ar"
                                                value="{{ old('question_ar', $question['question_ar']) }}" id="question_ar" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="is_first_question">{{ __('app.is-first-question') }}</label>
                                            <select class="form-select" name="is_first_question" id="is_first_question" required>
                                                <option value="" selected>{{ __('app.select-one') }}</option>
                                                <option value="1" {{ $question['status'] == 1 ? 'selected' : '' }}>{{ __('app.yes') }}</option>
                                                <option value="0" {{ $question['status'] == 0 ? 'selected' : '' }}>{{ __('app.no') }}</option>
                                            </select>
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
    <script type="text/javascript">
        $(document).ready(function() {
            $("#is_first_question").select2({
                placeholder: "{{ __('app.select-one') }}",
                width: "100%",
            })
        });
    </script>
@endpush
