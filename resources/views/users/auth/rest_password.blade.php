@php
    app()->setLocale(request()->lang);
@endphp


<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.reset_password_title') }}</title>

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

        .logo {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            display: block;
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
            <img src="{{ asset('assets/images/logo_yellow2.JPG') }}"
                 alt="{{ __('app.logo') }}"
                 class="responsive-logo">

            <h1>{{ __('app.reset_password') }}</h1>
            <p>{{ __('app.reset_password_description') }}</p>

            <form id="registerForm">
                @csrf
                <div class="input-group">
                    <input type="email" id="email" name="email" placeholder=" "
                        value="{{ old('email', request()->email) }}" disabled>
                    <label for="email"><i class="fas fa-envelope"></i> {{ __('app.email') }}</label>
                    <div class="error-message" id="emailError"></div>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <label for="password"><i class="fas fa-lock"></i> {{ __('app.password') }}</label>
                    <div class="password-strength" id="passwordStrength">
                        <div></div>
                    </div>
                    <div class="error-message" id="passwordError"></div>
                </div>
                <div class="input-group">
                    <input type="password" id="confirmPassword" name="password_confirmation" placeholder=" " required>
                    <label for="confirmPassword"><i class="fas fa-lock"></i> {{ __('app.confirm-password') }}</label>
                    <div class="error-message" id="confirmPasswordError"></div>
                </div>
                <button type="submit" class="register-btn">{{ __('app.reset-button') }}</button>
            </form>

        </div>
    </div>

    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        function showToastrWithTime(type, message, options = {}) {
            toastr.options = {
                closeButton: true,
                debug: false,
                newestOnTop: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                preventDuplicates: false,
                showDuration: '300',
                hideDuration: '1000',
                timeOut: '5000',
                extendedTimeOut: '1000',
                showEasing: 'swing',
                hideEasing: 'linear',
                showMethod: 'fadeIn',
                hideMethod: 'fadeOut',
                ...options
            };

            // Show Toastr notification based on the type
            switch (type) {
                case 'success':
                    toastr.success(`${message}`, 'Success');
                    break;
                case 'error':
                    toastr.error(`${message}`, 'Error');
                    break;
                case 'info':
                    toastr.info(`${message}`, 'Info');
                    break;
                case 'warning':
                    toastr.warning(`${message}`, 'Warning');
                    break;
                default:
                    console.error('Invalid Toastr type provided');
            }
        }
    </script>

    <script>
        const form = document.getElementById("registerForm");
        const passwordInput = document.getElementById("password");
        const confirmPasswordInput = document.getElementById("confirmPassword");
        const strengthBar = document.getElementById("passwordStrength").querySelector("div");
        const confirmPasswordError = document.getElementById("confirmPasswordError");

        passwordInput.addEventListener("input", () => {
            const value = passwordInput.value;

            let strength = 0;

            if (value.length >= 8) strength += 1;
            if (/[A-Z]/.test(value)) strength += 1;
            if (/[!@#$%^&*(),.?":{}|<>]/.test(value)) strength += 1;
            if (/[0-9]/.test(value)) strength += 1;

            switch (strength) {
                case 0:
                    strengthBar.style.width = "0%";
                    strengthBar.style.backgroundColor = "#ddd";
                    break;
                case 1:
                    strengthBar.style.width = "25%";
                    strengthBar.style.backgroundColor = "red";
                    break;
                case 2:
                    strengthBar.style.width = "50%";
                    strengthBar.style.backgroundColor = "orange";
                    break;
                case 3:
                    strengthBar.style.width = "75%";
                    strengthBar.style.backgroundColor = "yellow";
                    break;
                case 4:
                    strengthBar.style.width = "100%";
                    strengthBar.style.backgroundColor = "green";
                    break;
            }
        });

        confirmPasswordInput.addEventListener("input", () => {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordError.textContent = "{{ __('app.passwords_do_not_match') }}";
            } else {
                confirmPasswordError.textContent = "";
            }
        });

        form.addEventListener("submit", async (event) => {
            event.preventDefault();

            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordError.textContent = "{{ __('app.passwords_do_not_match') }}";
                return;
            } else {
                confirmPasswordError.textContent = "";
            }

            const data = {
                email: "{{ request()->email }}",
                password: passwordInput.value,
                password_confirmation: confirmPasswordInput.value,
                token: "{{ request()->token }}"
            };

            try {
                const response = await fetch("{{ route('api.password.store', $lang) }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok) {
                    showToastrWithTime('success', "{{ __('app.password_reset_successfully') }}");
                    setTimeout(() => {
                        window.location.href = "https://discoverjo.com";
                    }, 5000);
                } else {
                    if (result.errors) {
                        Object.entries(result.errors).forEach(([field, messages]) => {
                            document.getElementById(`${field}Error`).textContent = messages.join(", ");
                        });
                    } else {
                        showToastrWithTime('error', result.message || "{{ __('app.something_went_wrong') }}");
                    }
                }
            } catch (error) {
                showToastrWithTime('error', "{{ __('app.something_went_wrong') }}");
            }
        });

        function validatePassword(password) {
            return (
                password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[0-9]/.test(password) &&
                /[!@#$%^&*(),.?":{}|<>]/.test(password)
            );
        }
    </script>

</body>

</html>
