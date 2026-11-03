<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.title-meta')

    @include('partials.head-css')
</head>

@if (Route::is(['email-verification-illustration', 'forgot-password-illustration', 'login-illustration', 'register-illustration', 'reset-password-illustration']))
<body class="bg-light min-h-screen">
@elseif (Route::is(['email-verification-cover', 'forgot-password-cover', 'login-cover', 'register-cover', 'reset-password-cover']))
<body class="min-h-screen bg-cover bg-center flex items-center justify-center p-4" style="background-image: linear-gradient(135deg, rgba(15,118,110,0.9), rgba(5,150,105,0.9))">
@else
<body class="bg-light min-h-screen flex items-center justify-center p-4"></body>
@endif

    @yield('content')

    @include('partials.vendor-scripts')

</body>

</html>
