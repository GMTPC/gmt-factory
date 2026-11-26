@extends('layouts.mainlayout')
@section('content')

<div class="p-3 lg:py-6 lg:px-0 space-y-5 max-w-3xl">
    <div>
        <p class="text-sm text-primary mb-1"><a href="{{ route('master-users.index') }}" class="hover:underline">Master · Users</a></p>
        <h1 class="text-2xl font-bold text-gray-900 mb-0">แก้ไขผู้ใช้งาน</h1>
    </div>

    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <section class="bg-white border border-border-color rounded-md p-5">
        <form action="{{ route('master-users.update', $user) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="text-sm font-semibold text-gray-900 mb-1 block">ชื่อ-นามสกุล</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="name">
            </div>
            <div>
                <label for="username" class="text-sm font-semibold text-gray-900 mb-1 block">ชื่อผู้ใช้งาน</label>
                <input id="username" name="username" value="{{ old('username', $user->username) }}" maxlength="50" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="username">
            </div>
            <div class="md:col-span-2">
                <label for="email" class="text-sm font-semibold text-gray-900 mb-1 block">อีเมล</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="email">
            </div>
            <div>
                <label for="department" class="text-sm font-semibold text-gray-900 mb-1 block">แผนก</label>
                <input id="department" name="department" value="{{ old('department', $user->department) }}" maxlength="100" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="organization">
            </div>
            <div>
                <label for="position" class="text-sm font-semibold text-gray-900 mb-1 block">ตำแหน่ง</label>
                <input id="position" name="position" value="{{ old('position', $user->position) }}" maxlength="100" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="organization-title">
            </div>
            <div>
                <label for="password" class="text-sm font-semibold text-gray-900 mb-1 block">รหัสผ่านใหม่ (เว้นว่างหากไม่เปลี่ยน)</label>
                <input id="password" name="password" type="password" minlength="8" class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="new-password">
            </div>
            <div>
                <label for="password_confirmation" class="text-sm font-semibold text-gray-900 mb-1 block">ยืนยันรหัสผ่านใหม่</label>
                <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="new-password">
            </div>
            <div class="md:col-span-2 flex flex-wrap gap-2 pt-2">
                <button type="submit" class="btn-sm bg-dark text-white border border-dark hover:bg-primary-hover rounded-md px-4 py-2">บันทึก</button>
                <a href="{{ route('master-users.index') }}" class="btn-sm bg-white border border-border-color rounded-md px-4 py-2">ยกเลิก</a>
            </div>
        </form>
    </section>
</div>

@endsection
