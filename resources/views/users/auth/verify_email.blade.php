@php
    app()->setLocale(request()->lang);
@endphp


    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.verify-email') }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
        }

        .container {
            max-width: 450px;
            width: 100%;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 25px;
        }

        /*.logo {*/
        /*    width: 100px;*/
        /*    height: 100px;*/
        /*    margin: 0 auto;*/
        /*    display: block;*/
        /*}*/

        .logo {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            display: block;
        }

        .responsive-logo {
            width: 100%;
            height: auto;
            max-width: 250px; /* You can increase/decrease this based on your preference */
            display: block;
            margin: 0 auto 20px;
        }

        .form-box h1 {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }

        .form-box p {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-bottom: 40px;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group label {
            position: absolute;
            top: 12px;
        {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: 10px;
            font-size: 14px;
            color: #aaa;
            transition: 0.3s;
            pointer-events: none;
        }

        .input-group input {
            width: 100%;
            padding: 15px 10px 15px;
            border: none;
            border-bottom: 2px solid #ddd;
            font-size: 14px;
            color: #333;
            background: transparent;
            transition: border-color 0.3s;
            outline: none;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }

        .input-group input:focus {
            border-bottom: 2px solid #00bcd4;
        }

        .input-group input:focus+label,
        .input-group input:not(:placeholder-shown)+label {
            top: -10px;
            font-size: 12px;
            color: #00bcd4;
        }

        .password-strength {
            height: 5px;
            width: 100%;
            background-color: #ddd;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 10px;
        }

        .password-strength div {
            height: 100%;
            width: 0%;
            background-color: red;
            transition: width 0.3s, background-color 0.3s;
        }

        .error-message {
            font-size: 12px;
            color: red;
            margin-top: 5px;
        }

        .policy {
            font-size: 12px;
            color: #777;
            margin: 15px 0;
            text-align: center;
        }

        .policy a {
            color: #00bcd4;
            text-decoration: none;
            font-weight: bold;
        }

        .policy a:hover {
            text-decoration: underline;
        }

        .register-btn {
            width: 100%;
            padding: 12px;
            background-color: #ffcc00;
            border: none;
            border-radius: 8px;
            color: #333;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .register-btn:hover {
            background-color: #e6b800;
            transform: translateY(-2px);
        }

        .register-btn:active {
            transform: translateY(0);
        }
        .responsive-logo {
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 20px; /* Center it and add some spacing */
            max-width: 100%; /* Extra safety */
            border-radius: 8px; /* Optional, if you want slightly rounded corners */
        }
    </style>
</head>

<body>
<div class="container">
    <div class="form-box">
        <img src="{{ asset('assets/logo_black_cut.JPG') }}"
             alt="{{ __('app.logo') }}"
             class="responsive-logo">

        <h1>{{$message}}</h1>



    </div>
</div>

<link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

</body>

</html>
