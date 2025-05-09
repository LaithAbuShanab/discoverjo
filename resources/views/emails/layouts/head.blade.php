<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    @php
        $lang = $user->lang ?? app()->getLocale();
    @endphp

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            color: #333333;
            background-color: #ffffff;
        }

        .email-wrapper {
            width: 90%;
            max-width: 800px; /* Increased max-width for larger desktop size */
            margin: 0 auto;
            background-image: url('{{ asset('assets/background_email2.png') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            border-radius: 10px;
            overflow: hidden;
        }

        .email-container {
            margin: 0 auto;
            text-align: {{ $lang === 'ar' ? 'right' : 'left' }};
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px; /* Increased padding for larger desktop size */
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            direction: {{ $lang === 'ar' ? 'rtl' : 'ltr' }};
        }

        .email-header img {
            max-width: 200px; /* Increased max-width for larger desktop size */
            display: block;
            margin: 0 auto 1.25em auto;
            border-radius: 10px; /* Rounded corners */
            width: 8em;      /* 150px ÷ 16 = 9.375em */
            height: 8em;
        }

        .email-body {
            margin: 0;
        }

        .email-body h1 {
            font-size: 24px; /* Increased font size for larger desktop size */
            color: #00bcd4;
            margin-bottom: 15px;
        }

        .email-body p {
            font-size: 16px; /* Increased font size for larger desktop size */
            line-height: 1.8; /* Adjusted line height for readability */
            color: #555555;
            margin-bottom: 20px;
        }

        .email-body .button {
            display: inline-block;
            background-color: #00bcd4;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 30px; /* Increased padding for larger desktop size */
            font-size: 16px; /* Increased font size for larger desktop size */
            border-radius: 8px;
            font-weight: bold;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .email-body .button:hover {
            background-color: #019ba3;
        }

        .email-footer {
            background-color: #FFD700;
            background-image: url('{{ asset('assets/background_email2.png') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            color: #333333;
            text-align: center;
            padding: 30px 10px; /* Increased padding for larger desktop size */
            border-radius: 0 0 10px 10px;
        }

        .email-footer a {
            display: inline-block;
            margin: 0 8px;
            color: #333333;
            text-decoration: none;
        }

        .email-footer a img {
            width: 28px;
            height: 28px;
            margin: 0 5px;
            vertical-align: middle;
        }

        .email-footer a:hover img {
            filter: brightness(0.8);
            transition: filter 0.3s ease;
        }

        .email-footer p {
            margin-top: 10px;
            font-size: 14px; /* Increased font size for larger desktop size */
            color: #333333;
        }

        @media only screen and (max-width: 600px) {
            .email-wrapper {
                width: 100%;
            }

            .email-body h1 {
                font-size: 18px;
            }

            .email-body p {
                font-size: 14px;
            }

            .email-body .button {
                padding: 10px 20px;
                font-size: 14px;
            }
        }

        @media only screen and (max-width: 400px) {
            .email-body p {
                font-size: 12px;
            }

            .email-footer p {
                font-size: 10px;
            }
        }
    </style>
</head>
