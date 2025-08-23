<!doctype html>

<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets') }}/" data-template="horizontal-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Login</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets') }}/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/tabler-icons.css" />
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/rtl/theme-default.css"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/libs/typeahead-js/typeahead.css" />
    <!-- Vendor -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/libs/@form-validation/form-validation.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/pages/page-auth.css" />

    <!-- Helpers -->
    <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{{ asset('assets') }}/vendor/js/template-customizer.js"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets') }}/js/config.js"></script>


</head>

<body>
<!-- Content -->

<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
            <!-- Login -->
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-4 mt-2">
                        <a href="index.html" class="app-brand-link gap-2">
                     <img href="#" src="{{ asset('assets/img') }}/LOGO2.png" alt="Logo" width="80px"/>
                            <span class="app-brand-text demo text-body fw-bold ms-1">Project Name</span>
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1 pt-2">Welcome to Project Name!👋</h4>
                    <p class="mb-4">Please sign-in to your account to access the dashboard</p>

                <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"  id="email"
                            name="email"
                            placeholder="Enter your email address"
                            value="{{ old('email') }}"  required autofocus />

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <div class="d-flex justify-content-between">
                            <label class="form-label" for="password">Password</label>
                        </div>
                        <div class="input-group input-group-merge">
                            <input
                                type="password"
                                id="password"
                                class="form-control @error('password') is-invalid @enderror" name="password"
                                placeholder="Enter your password"
                                required/>
                            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
                    </div>
                </form>
                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>
</div>

<!-- / Content -->

<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->

<script src="{{ asset('assets') }}/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('assets') }}/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
<script src="{{ asset('assets') }}/vendor/libs/node-waves/node-waves.js"></script>
<script src="{{ asset('assets') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="{{ asset('assets') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets') }}/vendor/libs/i18n/i18n.js"></script>
<script src="{{ asset('assets') }}/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="{{ asset('assets') }}/vendor/js/menu.js"></script>

<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{ asset('assets') }}/vendor/libs/@form-validation/popular.js"></script>
<script src="{{ asset('assets') }}/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="{{ asset('assets') }}/vendor/libs/@form-validation/auto-focus.js"></script>

<!-- Main JS -->
<script src="{{ asset('assets') }}/js/main.js"></script>

<!-- Page JS -->
<script src="{{ asset('assets') }}/js/pages-auth.js"></script>
</body>
</html>

{{-- show validate errors --}}
<script>
    @if ($errors->any())
    var errorMessage = "";
    @foreach ($errors->all() as $error)
        errorMessage += "{{ $error }}"; @endforeach
        document.getElementById('error-message').textContent = errorMessage;
      @endif
    </script>
