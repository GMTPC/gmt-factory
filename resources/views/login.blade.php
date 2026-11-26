@extends('layouts.authlayout')
@section('content')

<div class="bg-white border border-border-color rounded-md shadow-sm w-full max-w-md sm:p-8 p-5">
    <div class="text-center mb-6">
        <div class="invoice-logo block dark:hidden">
            <a href="{{ url('index') }}"><img src="{{ asset('Login_v1/images/img-02.png') }}" class="h-20 max-w-full object-contain mx-auto mb-4" alt="Gypman Tech logo"></a>
        </div>
        <div class="invoice-logo-white hidden dark:block">
            <a href="{{ url('index') }}"><img src="{{ asset('Login_v1/images/img-02.png') }}" class="h-20 max-w-full object-contain mx-auto mb-4" alt="Gypman Tech logo"></a>
        </div>
        <h1 class="text-xl font-bold text-title mb-1">ยินดีต้อนรับกลับมา</h1>
        <p class="text-sm text-default mb-0">เข้าสู่ระบบ Gypman Tech</p>
    </div>

    <form class="space-y-4" action="{{ route('login.store') }}" method="POST">
        @csrf
        <div>
            <label for="username" class="text-sm font-semibold text-gray-900 mb-1 block">ชื่อผู้ใช้งาน</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" class="w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0 @error('username') border-red-500 @enderror" placeholder="ชื่อผู้ใช้งาน" autocomplete="username" required autofocus>
            @error('username')
                <p class="mt-1 text-sm text-red-600">ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง</p>
            @enderror
        </div>
        <div>
            <div class="flex justify-between items-center mb-1">
                <label for="password" class="text-sm text-gray-900">รหัสผ่าน</label>
            </div>
            <div class="relative">
                <input id="password" name="password" type="password" class="form-input form-input-icon w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:border-border-color focus:ring-0 h-10 @error('password') border-red-500 @enderror" placeholder="************" autocomplete="current-password" required>
                <span class="absolute start-0 top-2 ms-3"><i class="icon icon-lock"></i></span>
                <button type="button" data-hs-toggle-password="{ &quot;target&quot;: &quot;#password&quot; }" class="absolute inset-y-0 end-0 flex items-center z-20 px-3 cursor-pointer rounded-e-md focus:outline-hidden focus:text-blue-600">
                    <i class="icon icon-eye hidden hs-password-active:block"></i>
                    <i class="icon icon-eye-off hs-password-active:hidden"></i>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-border-color" id="remember">
            <label for="remember" class="text-sm text-default">จดจำการเข้าสู่ระบบ</label>
        </div>
        <button type="submit" class="w-full bg-dark text-white py-2.5 rounded-md text-sm font-semibold hover:bg-primary-hover cursor-pointer">เข้าสู่ระบบ</button>
        <p class="text-center text-sm text-default mb-0">ยังไม่มีบัญชี? <a href="{{ route('register') }}" class="text-primary hover:underline">สมัครสมาชิก</a></p>
    </form>
</div>

@endsection
