<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Admin | Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesdesign" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{asset('assets')}}/images/logo.png">

    <!-- Bootstrap Css -->
    <link href="{{asset('assets')}}/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{asset('assets')}}/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{asset('assets')}}/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

</head>

<body >
<div class="bg-overlay"></div>
<div class="wrapper-page">
    <div class="container-fluid p-0">
        <div class="card">
            <div class="card-body">

                <div class="text-center mt-4">
                    <div class="mb-3">
                        <a href="index.html" class="auth-logo">
                            <img src="{{asset('assets')}}/images/logo.png" height="100" class="logo-dark mx-auto"
                                 alt="">
                            <img src="{{asset('assets')}}/images/logo.png" height="100" class="logo-light mx-auto"
                                 alt="">
                        </a>
                    </div>
                </div>

                <h4 class="text-muted text-center font-size-18"><b>Admin Register</b></h4>

                <div class="p-3">
                    <form class="form-horizontal mt-3" method="POST" action="{{ route('admin.register') }}">
                        @csrf
                        <div class="form-group mb-3 row">
                            <div class="col-12">
                                <input class="form-control" type="email" required="" placeholder="Email" name="email" value="{{old('email')}}">
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>

                        <div class="form-group mb-3 row">
                            <div class="col-12">
                                <input class="form-control" type="text" required="" placeholder="Name" name="name" value="{{old('name')}}">
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                        </div>

                        <div class="form-group mb-3 row">
                            <div class="col-12">
                                <input class="form-control" type="password" required="" placeholder="Password" name="password">
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                        </div>

                        <div class="form-group mb-3 row">
                            <div class="col-12">
                                <input class="form-control" type="password" required="" placeholder="Password Confirmation" name="password_confirmation">
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>

                        <div class="form-group text-center row mt-3 pt-1">
                            <div class="col-12">
                                <button class="btn btn-info w-100 waves-effect waves-light"
                                        type="submit">Register</button>
                            </div>
                        </div>

                        <div class="form-group mt-2 mb-0 row">
                            <div class="col-12 mt-3 text-center">
                                <a href="{{route('admin.login')}}" class="text-muted">Already have account?</a>
                            </div>
                        </div>
                    </form>
                    <!-- end form -->
                </div>
            </div>
            <!-- end cardbody -->
        </div>
        <!-- end card -->
    </div>
    <!-- end container -->
</div>
<!-- end -->


<!-- JAVASCRIPT -->
<script src="{{asset('assets')}}/libs/jquery/jquery.min.js"></script>
<script src="{{asset('assets')}}/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="{{asset('assets')}}/libs/metismenu/metisMenu.min.js"></script>
<script src="{{asset('assets')}}/libs/simplebar/simplebar.min.js"></script>
<script src="{{asset('assets')}}/libs/node-waves/waves.min.js"></script>

<script src="{{asset('assets')}}/js/app.js"></script>

</body>

</html>
