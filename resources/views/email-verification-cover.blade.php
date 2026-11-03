@extends('layouts.authlayout')
@section('content')

    <!-- Start Email Verification -->
    <div class="bg-white border border-border-color rounded-md shadow-lg w-full max-w-md sm:p-8 p-5">
        <div class="text-center mb-6">
            <div class="invoice-logo block dark:hidden">
                <a href="{{url('index')}}"><img src="{{URL::asset('build/img/logo.svg')}}" class="h-10 mx-auto mb-4" alt="logo"></a>
            </div>
            <div class="invoice-logo-white hidden dark:block">
                <a href="{{url('index')}}"><img src="{{URL::asset('build/img/logo-white.svg')}}" class="h-10 mx-auto mb-4" alt="logo"></a>
            </div>
            <h1 class="text-xl font-bold text-title mb-1">Verify Email</h1>
            <p class="text-sm text-default mb-0">Enter the 6-digit code</p>
        </div>
        <form class="space-y-4" action="{{url('reset-password-cover')}}">
            <div class="grid grid-cols-6 gap-2">
                <input type="text" maxlength="1" class="aspect-square text-center text-lg border border-border-color rounded-md bg-white focus:outline-none focus:border-primary focus:ring-0">
                <input type="text" maxlength="1" class="aspect-square text-center text-lg border border-border-color rounded-md bg-white focus:outline-none focus:border-primary focus:ring-0">
                <input type="text" maxlength="1" class="aspect-square text-center text-lg border border-border-color rounded-md bg-white focus:outline-none focus:border-primary focus:ring-0">
                <input type="text" maxlength="1" class="aspect-square text-center text-lg border border-border-color rounded-md bg-white focus:outline-none focus:border-primary focus:ring-0">
                <input type="text" maxlength="1" class="aspect-square text-center text-lg border border-border-color rounded-md bg-white focus:outline-none focus:border-primary focus:ring-0">
                <input type="text" maxlength="1" class="aspect-square text-center text-lg border border-border-color rounded-md bg-white focus:outline-none focus:border-primary focus:ring-0">
            </div>
            <button type="submit" class="w-full bg-dark text-white py-2.5 rounded-md text-sm font-semibold hover:bg-primary-hover cursor-pointer">Verify Code</button>
        </form>
    </div>
    <!-- End Email Verification -->

@endsection
