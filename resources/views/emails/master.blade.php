<!doctype html>
@php
    $lang = app()->getLocale();
    $dir = $lang == 'ar' ? 'rtl' : 'ltr';
@endphp
<html lang="en" dir="{{ $dir }}">

@include('emails.layouts.head')

<body>
    <div class="email-wrapper">
        <div class="email-container">
            @yield('content')
        </div>
        @include('emails.layouts.footer')
    </div>
</body>

</html>
