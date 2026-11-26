@extends('layouts.authlayout')
@section('content')

<div class="bg-white border border-border-color rounded-md shadow-sm w-full max-w-md sm:p-8 p-5">
    <div class="text-center mb-6">
        <div class="invoice-logo block dark:hidden">
            <a href="{{ route('login') }}"><img src="{{ asset('Login_v1/images/img-02.png') }}" class="h-20 max-w-full object-contain mx-auto mb-4" alt="Gypman Tech logo"></a>
        </div>
        <div class="invoice-logo-white hidden dark:block">
            <a href="{{ route('login') }}"><img src="{{ asset('Login_v1/images/img-02.png') }}" class="h-20 max-w-full object-contain mx-auto mb-4" alt="Gypman Tech logo"></a>
        </div>
        <h1 class="text-xl font-bold text-title mb-1">สมัครสมาชิก</h1>
        <p class="text-sm text-default mb-0">สร้างบัญชีเพื่อเข้าใช้งานระบบ</p>
    </div>

    <form class="space-y-4" action="{{ route('register.store') }}" method="POST">
        @csrf
        <div>
            <label for="name" class="text-sm font-semibold text-gray-900 mb-1 block">ชื่อ-นามสกุล</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus class="w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0 @error('name') border-red-500 @enderror">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="username" class="text-sm font-semibold text-gray-900 mb-1 block">ชื่อผู้ใช้งาน</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" maxlength="50" autocomplete="username" required class="w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0 @error('username') border-red-500 @enderror">
            @error('username')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="text-sm font-semibold text-gray-900 mb-1 block">อีเมล</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0 @error('email') border-red-500 @enderror">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="department" class="text-sm font-semibold text-gray-900 mb-1 block">แผนก</label>
            <input id="department" name="department" type="text" value="{{ old('department') }}" maxlength="100" autocomplete="organization" required class="w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0 @error('department') border-red-500 @enderror">
            @error('department')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="position" class="text-sm font-semibold text-gray-900 mb-1 block">ตำแหน่ง</label>
            <input id="position" name="position" type="text" value="{{ old('position') }}" maxlength="100" autocomplete="organization-title" required class="w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0 @error('position') border-red-500 @enderror">
            @error('position')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="text-sm font-semibold text-gray-900 mb-1 block">รหัสผ่าน (อย่างน้อย 8 ตัวอักษร)</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8" class="w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0 @error('password') border-red-500 @enderror">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="text-sm font-semibold text-gray-900 mb-1 block">ยืนยันรหัสผ่าน</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8" class="w-full px-3 py-2.5 text-sm border border-border-color rounded-md bg-white focus:outline-none focus:ring-0">
        </div>
        <button type="submit" class="w-full bg-dark text-white py-2.5 rounded-md text-sm font-semibold hover:bg-primary-hover cursor-pointer">สมัครสมาชิก</button>
        <p class="text-center text-sm text-default mb-0">มีบัญชีอยู่แล้ว? <a href="{{ route('login') }}" class="text-primary hover:underline">เข้าสู่ระบบ</a></p>
    </form>
</div>

@endsection
