<!DOCTYPE html>
@if(Route::is('layout-fullwidth'))
<html lang="en" data-layout="full-width">
@elseif(Route::is('layout-hidden'))
<html lang="en" data-layout="hidden">
@elseif(Route::is('layout-hoverview'))
<html lang="en" data-layout="hoverview">
@elseif(Route::is('layout-mini'))
<html lang="en" data-layout="mini">
@elseif(Route::is('layout-rtl'))
<html lang="en" dir="rtl">
@else
<html lang="en">
@endif

<head>
    @include('partials.title-meta')

    @include('partials.head-css')
    @if (isset($page))
        @inertiaHead
    @endif
</head>

@if(Route::is('layout-mini'))
<body class="mini-sidebar">
@elseif(Route::is('layout-rtl'))
<body class="layout-rtl">
@elseif(Route::is('pos'))
<body class="bg-light min-h-screen">
@else
<body>
@endif

    @if (!Route::is(['pos']))
    <!-- Main Wrapper -->
    <div class="main-wrapper">
    @endif

        @include('partials.topbar')

        @if (!Route::is(['pos']))
        @include('partials.sidebar')
        @endif

        @if (!Route::is(['pos']))
        <div class="page-wrapper">
        @endif

            @if (Route::is(['pos']))
            <main class="mx-4 my-4 p-5 bg-white rounded-lg border border-border-color">
            @else
            <main>
            @endif

                @yield('content')

            </main>

            @include('partials.footer')

        @if (!Route::is(['pos']))
        </div>
        <!-- End Page Wrapper -->
        @endif

    @if (!Route::is(['pos']))
    </div>
    <!-- End Main Wrapper -->
    @endif

    @component('components.modal-popup')
    @endcomponent

    @include('partials.vendor-scripts')

</body>

</html>
