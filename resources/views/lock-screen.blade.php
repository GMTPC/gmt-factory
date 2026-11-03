@extends('layouts.authlayout')
@section('content')

    <!-- Start Lock Screen -->
    <div class="bg-white border border-border-color rounded-md shadow-sm w-full max-w-md sm:p-8 p-5">
        <div class="text-center mb-6">
            <div class="invoice-logo block dark:hidden">
                <a href="{{url('index')}}"><img src="{{URL::asset('build/img/logo.svg')}}" class="h-10 mx-auto mb-4" alt="logo"></a>
            </div>
            <div class="invoice-logo-white hidden dark:block">
                <a href="{{url('index')}}"><img src="{{URL::asset('build/img/logo-white.svg')}}" class="h-10 mx-auto mb-4" alt="logo"></a>
            </div>
            <img src="{{URL::asset('build/img/avatar/avatar-01.jpg')}}" class="size-16 rounded-full mx-auto mb-3 border border-border-color" alt="user avatar">
            <h1 class="text-xl font-bold text-title mb-1">James Hong</h1>
            <p class="text-sm text-default mb-0">james@example.com</p>
        </div>
        <form class="space-y-4" action="{{url('index')}}">
            <div>
                <div class="flex justify-between items-center mb-1">
                <label class="text-sm text-gray-900">Password</label>
                <a href="{{url('forgot-password')}}" class="text-sm text-primary hover:underline">Forgot Password?</a>
            </div>
                <div class="relative">
                    <input id="hs-toggle-password4" type="password" class="form-input form-input-icon w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:border-border-color focus:ring-0 h-10" placeholder="************" value="">
                    <span class="absolute start-0 top-2 ms-3"><i class="icon icon-lock"></i></span>
                    <button type="button" data-hs-toggle-password="{ &quot;target&quot;: &quot;#hs-toggle-password4&quot; }" class="absolute inset-y-0 end-0 flex items-center z-20 px-3 cursor-pointer rounded-e-md focus:outline-hidden focus:text-blue-600">
                        <i class="icon icon-eye hidden hs-password-active:block"></i>
                        <i class="icon icon-eye-off hs-password-active:hidden"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="w-full bg-dark text-white py-2.5 rounded-md text-sm font-semibold hover:bg-primary-hover cursor-pointer">Unlock</button>
            <p class="text-center text-sm text-default mb-0">Not you? <a href="{{url('login')}}" class="text-primary hover:underline">Sign in as a different user</a></p>
        </form>
    </div>
    <!-- End Lock Screen -->

@endsection
