@extends('layouts.mainlayout')
@section('content')

<div class="p-3 lg:py-6 lg:px-0 space-y-5">
    <div>
        <p class="text-sm text-primary mb-1">Master</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-0">จัดการผู้ใช้งาน</h1>
    </div>

    @if (session('status'))
        <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <section class="bg-white border border-border-color rounded-md p-5">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">เพิ่มผู้ใช้งาน</h2>
        <form action="{{ route('master-users.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label for="name" class="text-sm font-semibold text-gray-900 mb-1 block">ชื่อ-นามสกุล</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="name">
            </div>
            <div>
                <label for="username" class="text-sm font-semibold text-gray-900 mb-1 block">ชื่อผู้ใช้งาน</label>
                <input id="username" name="username" value="{{ old('username') }}" maxlength="50" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="username">
            </div>
            <div>
                <label for="email" class="text-sm font-semibold text-gray-900 mb-1 block">อีเมล</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="email">
            </div>
            <div>
                <label for="department" class="text-sm font-semibold text-gray-900 mb-1 block">แผนก</label>
                <input id="department" name="department" value="{{ old('department') }}" maxlength="100" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="organization">
            </div>
            <div>
                <label for="position" class="text-sm font-semibold text-gray-900 mb-1 block">ตำแหน่ง</label>
                <input id="position" name="position" value="{{ old('position') }}" maxlength="100" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="organization-title">
            </div>
            <div>
                <label for="password" class="text-sm font-semibold text-gray-900 mb-1 block">รหัสผ่าน (อย่างน้อย 8 ตัวอักษร)</label>
                <input id="password" name="password" type="password" minlength="8" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="new-password">
            </div>
            <div>
                <label for="password_confirmation" class="text-sm font-semibold text-gray-900 mb-1 block">ยืนยันรหัสผ่าน</label>
                <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required class="w-full px-3 py-2 text-sm border border-border-color rounded-md" autocomplete="new-password">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="btn-sm bg-dark text-white border border-dark hover:bg-primary-hover rounded-md px-4 py-2">เพิ่มผู้ใช้</button>
            </div>
        </form>
    </section>

    <section class="bg-white border border-border-color rounded-md p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-0">บัญชีผู้ใช้</h2>
            <span class="text-sm text-gray-500">ทั้งหมด {{ $users->total() }} บัญชี</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-border-color text-gray-600">
                        <th class="py-3 px-2">ชื่อ</th>
                        <th class="py-3 px-2">Username</th>
                        <th class="py-3 px-2">อีเมล</th>
                        <th class="py-3 px-2">แผนก</th>
                        <th class="py-3 px-2">ตำแหน่ง</th>
                        <th class="py-3 px-2">วันที่สร้าง</th>
                        <th class="py-3 px-2 text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-b border-border-color last:border-0">
                            <td class="py-3 px-2 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="py-3 px-2">{{ $user->username }}</td>
                            <td class="py-3 px-2">{{ $user->email }}</td>
                            <td class="py-3 px-2">{{ $user->department ?: '—' }}</td>
                            <td class="py-3 px-2">{{ $user->position ?: '—' }}</td>
                            <td class="py-3 px-2">{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-2">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('master-users.edit', $user) }}" class="rounded border border-border-color px-3 py-1.5 text-xs font-medium hover:bg-light">แก้ไข</a>
                                    @if ((int) $user->id !== (int) auth()->id())
                                        <form action="{{ route('master-users.destroy', $user) }}" method="POST" onsubmit="return confirm('ยืนยันการลบบัญชีนี้หรือไม่?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">ลบ</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-gray-500">ยังไม่มีผู้ใช้งาน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </section>
</div>

@endsection
