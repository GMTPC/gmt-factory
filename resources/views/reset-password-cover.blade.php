@extends('layouts.authlayout')
@section('content')

    <!-- Start Reset Password -->
    <div class="bg-white border border-border-color rounded-md shadow-lg w-full max-w-md sm:p-8 p-5">
        <div class="text-center mb-6">
            <div class="invoice-logo block dark:hidden">
                <a href="{{url('index')}}"><img src="{{URL::asset('build/img/logo.svg')}}" class="h-10 mx-auto mb-4" alt="logo"></a>
            </div>
            <div class="invoice-logo-white hidden dark:block">
                <a href="{{url('index')}}"><img src="{{URL::asset('build/img/logo-white.svg')}}" class="h-10 mx-auto mb-4" alt="logo"></a>
            </div>
            <h1 class="text-xl font-bold text-title mb-1">Reset Password</h1>
        </div>
        <form class="space-y-4" action="{{url('login-cover')}}">
            <div>
                <label class="text-sm font-semibold text-gray-900 mb-1 block">New Password</label>
                <div class="relative">
                    <input id="hs-toggle-password2" type="password" class="form-input form-input-icon w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:border-border-color focus:ring-0 h-10" placeholder="************" value="">
                    <span class="absolute start-0 top-2 ms-3"><i class="icon icon-lock"></i></span>
                    <button type="button" data-hs-toggle-password="{ &quot;target&quot;: &quot;#hs-toggle-password2&quot; }" class="absolute inset-y-0 end-0 flex items-center z-20 px-3 cursor-pointer rounded-e-md focus:outline-hidden focus:text-blue-600">
                        <i class="icon icon-eye hidden hs-password-active:block"></i>
                        <i class="icon icon-eye-off hs-password-active:hidden"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-900 mb-1 block">Confirm Password</label>
                <div class="relative">
                    <input id="hs-toggle-password3" type="password" class="form-input form-input-icon w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:border-border-color focus:ring-0 h-10" placeholder="************" value="">
                    <span class="absolute start-0 top-2 ms-3"><i class="icon icon-lock"></i></span>
                    <button type="button" data-hs-toggle-password="{ &quot;target&quot;: &quot;#hs-toggle-password3&quot; }" class="absolute inset-y-0 end-0 flex items-center z-20 px-3 cursor-pointer rounded-e-md focus:outline-hidden focus:text-blue-600">
                        <i class="icon icon-eye hidden hs-password-active:block"></i>
                        <i class="icon icon-eye-off hs-password-active:hidden"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="w-full bg-dark text-white py-2.5 rounded-md text-sm font-semibold hover:bg-primary-hover cursor-pointer">Reset Password</button>
            <p class="text-center text-sm text-default mb-0"><a href="{{url('login-cover')}}" class="text-primary hover:underline">← Back to Sign In</a></p>
        </form>
    </div>
    <!-- End Reset Password -->

@endsection
