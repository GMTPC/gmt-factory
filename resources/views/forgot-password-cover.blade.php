@extends('layouts.authlayout')
@section('content')

    <!-- Start Forgot Password -->
    <div class="bg-white border border-border-color rounded-md shadow-lg w-full max-w-md sm:p-8 p-5">
        <div class="text-center mb-6">
            <div class="invoice-logo block dark:hidden">
                <a href="{{url('index')}}"><img src="{{URL::asset('build/img/logo.svg')}}" class="h-10 mx-auto mb-4" alt="logo"></a>
            </div>
            <div class="invoice-logo-white hidden dark:block">
                <a href="{{url('index')}}"><img src="{{URL::asset('build/img/logo-white.svg')}}" class="h-10 mx-auto mb-4" alt="logo"></a>
            </div>
            <h1 class="text-xl font-bold text-title mb-1">Forgot Password?</h1>
            <p class="text-sm text-default mb-0">Enter email for reset link</p>
        </div>
        <form class="space-y-4" action="{{url('email-verification-cover')}}">
            <div>
                <label class="text-sm font-semibold text-gray-900 mb-1 block">Email</label>
                <input type="email" class="w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
            </div>
            <button type="submit" class="w-full bg-dark text-white py-2.5 rounded-md text-sm font-semibold hover:bg-primary-hover cursor-pointer">Send Reset Link</button>
            <p class="text-center text-sm text-default mb-0"><a href="{{url('login-cover')}}" class="text-primary hover:underline">← Back to Sign In</a></p>
        </form>
    </div>
    <!-- End Forgot Password -->

@endsection
