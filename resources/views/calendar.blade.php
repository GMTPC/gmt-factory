@extends('layouts.mainlayout')
@section('content')

    <!-- Start Content -->
    <div class="p-3 lg:py-6 lg:px-0">

        <div class="flex flex-wrap items-center justify-between gap-3 mb-3 lg:mb-6">
            <h1 class="text-gray-900 text-xl font-bold mb-0">Calendar</h1>
        </div>

        <!-- Start grid -->
        <div class="grid grid-cols-1 calendar-wrapper">
            <div class="bg-white p-5 border border-border-color rounded-lg">
                <div id="calendar"></div>
            </div>
        </div>
        <!-- End grid -->

    </div>
    <!-- End Content -->

@endsection
