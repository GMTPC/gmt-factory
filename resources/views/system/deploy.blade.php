@extends('layouts.mainlayout')
@section('content')
@php
    $tz = config('finishing.timezone', 'Asia/Bangkok');
    $time = fn ($value) => $value ? \Carbon\Carbon::parse($value)->timezone($tz)->format('d/m/Y H:i:s') : '-';
    $ago = fn ($value) => $value ? \Carbon\Carbon::parse($value)->locale('th')->diffForHumans() : '';
    $short = fn ($sha) => $sha ? substr($sha, 0, 7) : '-';
    $verdictStyle = [
        'success' => 'border-green-300 bg-green-50 text-green-900',
        'info' => 'border-blue-300 bg-blue-50 text-blue-900',
        'warning' => 'border-amber-300 bg-amber-50 text-amber-900',
        'danger' => 'border-red-300 bg-red-50 text-red-900',
        'muted' => 'border-gray-300 bg-gray-50 text-gray-700',
    ][$verdict['level']];
    $runBadge = function (array $run): array {
        if ($run['status'] !== 'completed') return ['กำลังทำงาน', 'bg-blue-100 text-blue-800'];
        return match ($run['conclusion']) {
            'success' => ['สำเร็จ', 'bg-green-100 text-green-800'],
            'failure' => ['ล้มเหลว', 'bg-red-100 text-red-800'],
            'cancelled' => ['ยกเลิก', 'bg-gray-200 text-gray-700'],
            default => [(string) $run['conclusion'], 'bg-amber-100 text-amber-800'],
        };
    };
    $isRunning = collect($github['runs'])->contains(fn ($run) => $run['status'] !== 'completed');
@endphp

<div class="p-3 lg:py-6 lg:px-0 space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm text-primary mb-1">System</p>
            <h1 class="text-2xl font-bold text-gray-900 mb-0">ตรวจสอบการ Deploy</h1>
            <p class="text-xs text-gray-500 mb-0">เซิร์ฟเวอร์ {{ request()->getHost() }} · ตรวจเมื่อ {{ now($tz)->format('d/m/Y H:i:s') }}{{ $isRunning ? ' · รีเฟรชอัตโนมัติทุก 15 วินาที' : '' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('system.deploy') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold">รีเฟรช</a>
            <a href="{{ $github['actions_url'] }}" target="_blank" rel="noopener" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">เปิด GitHub Actions</a>
        </div>
    </div>

    <div class="rounded-md border-2 px-4 py-3 text-base font-bold {{ $verdictStyle }}">{{ $verdict['text'] }}</div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-md border border-gray-200 bg-white p-4">
            <h2 class="text-base font-bold mb-3">เวอร์ชันบนเซิร์ฟเวอร์นี้</h2>
            @if ($deployed)
                <dl class="grid grid-cols-[140px_1fr] gap-y-2 text-sm">
                    <dt class="text-gray-500">Commit</dt><dd class="font-mono font-bold">{{ $short($deployed['sha']) }} <span class="font-sans font-normal text-gray-500">{{ $deployed['message'] ?? '' }}</span></dd>
                    <dt class="text-gray-500">Deploy run</dt><dd>@if (!empty($deployed['run_url']))<a href="{{ $deployed['run_url'] }}" target="_blank" rel="noopener" class="text-primary underline">#{{ $deployed['run_number'] ?? '-' }}</a>@else #{{ $deployed['run_number'] ?? '-' }} @endif · โดย {{ $deployed['actor'] ?? '-' }}</dd>
                    <dt class="text-gray-500">Deploy เมื่อ</dt><dd>{{ $time($deployed['deployed_at'] ?? null) }} <span class="text-gray-500">({{ $ago($deployed['deployed_at'] ?? null) }})</span></dd>
                </dl>
            @else
                <p class="text-sm text-gray-600 mb-2">ไม่พบ <code>storage/app/deploy-info.json</code> · ไฟล์นี้สร้างโดย workflow "Deploy to IIS server" หลัง deploy สำเร็จ</p>
                @if ($localHead)
                    <p class="text-sm mb-0">Commit ใน working copy นี้: <span class="font-mono font-bold">{{ $short($localHead) }}</span></p>
                @endif
            @endif
        </section>

        <section class="rounded-md border {{ $migrations['ok'] ? 'border-gray-200' : 'border-red-300' }} bg-white p-4">
            <h2 class="text-base font-bold mb-3">Database migrations</h2>
            @if ($migrations['error'])
                <p class="text-sm text-red-700 mb-0">ตรวจไม่ได้: {{ $migrations['error'] }}</p>
            @elseif ($migrations['ok'])
                <p class="text-sm text-green-800 font-semibold mb-0">✓ รันครบทุก migration แล้ว</p>
            @else
                <p class="text-sm text-red-800 font-semibold mb-2">ยังไม่ได้รัน {{ count($migrations['pending']) }} รายการ · Deploy ไม่ได้รัน migrate ให้อัตโนมัติ ต้องรัน <code>php artisan migrate</code> บนเซิร์ฟเวอร์</p>
                <ul class="max-h-48 overflow-y-auto rounded border border-red-100 bg-red-50 p-2 text-xs font-mono">
                    @foreach ($migrations['pending'] as $name)<li>{{ $name }}</li>@endforeach
                </ul>
            @endif
            <p class="text-xs text-gray-500 mt-2 mb-0">ฐานข้อมูล: {{ $env['db_connection'] }} · {{ $env['db_database'] }}</p>
        </section>
    </div>

    <section class="rounded-md border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
            <h2 class="text-base font-bold mb-0">GitHub Actions · Deploy to IIS server</h2>
            @if ($github['error'])<span class="text-xs text-red-700">{{ $github['error'] }}</span>@endif
        </div>
        @if (! $github['configured'])
            <div class="p-4 text-sm text-gray-700 space-y-1">
                <p class="mb-0">ยังไม่ได้ตั้งค่า token จึงดึงสถานะจาก GitHub ไม่ได้ (ส่วนอื่นของหน้านี้ใช้งานได้ปกติ)</p>
                <p class="mb-0">ใส่ใน <code>.env</code> ของเซิร์ฟเวอร์: <code>GITHUB_DEPLOY_TOKEN=</code> (Fine-grained token · repo {{ config('services.github.repository') }} · สิทธิ์ Actions: Read-only) แล้วรัน <code>php artisan config:cache</code></p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead class="bg-gray-50 text-left text-xs text-gray-500"><tr><th class="px-4 py-2">Run</th><th class="px-4 py-2">สถานะ</th><th class="px-4 py-2">Commit</th><th class="px-4 py-2">โดย</th><th class="px-4 py-2">เริ่ม</th><th class="px-4 py-2">ใช้เวลา</th><th class="px-4 py-2"></th></tr></thead>
                    <tbody>
                        @forelse ($github['runs'] as $run)
                            @php([$label, $badge] = $runBadge($run))
                            @php($isHere = $deployed && $deployed['sha'] === $run['sha'] && $run['conclusion'] === 'success')
                            <tr class="border-t border-gray-100 {{ $isHere ? 'bg-green-50' : '' }}">
                                <td class="px-4 py-2 font-bold">#{{ $run['run_number'] }}</td>
                                <td class="px-4 py-2"><span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $badge }}">{{ $label }}</span></td>
                                <td class="px-4 py-2"><span class="font-mono">{{ $short($run['sha']) }}</span> {{ $run['title'] }} @if ($isHere)<span class="ml-1 rounded bg-green-600 px-1.5 py-0.5 text-[11px] font-bold text-white">อยู่บนเซิร์ฟเวอร์นี้</span>@endif</td>
                                <td class="px-4 py-2">{{ $run['actor'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $time($run['started_at']) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $run['status'] === 'completed' ? \Carbon\Carbon::parse($run['started_at'])->diff(\Carbon\Carbon::parse($run['updated_at']))->format('%im %ss') : $ago($run['started_at']) }}</td>
                                <td class="px-4 py-2 text-right"><a href="{{ $run['url'] }}" target="_blank" rel="noopener" class="text-primary underline">ดู log</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">ไม่มีข้อมูล run</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="rounded-md border border-gray-200 bg-white p-4">
        <h2 class="text-base font-bold mb-3">สภาพแวดล้อม</h2>
        <dl class="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="text-xs text-gray-500">APP_ENV / DEBUG</dt><dd class="font-semibold">{{ $env['app_env'] }} · {{ $env['app_debug'] ? 'debug เปิด' : 'debug ปิด' }}</dd></div>
            <div><dt class="text-xs text-gray-500">PHP / Laravel</dt><dd class="font-semibold">{{ $env['php'] }} · {{ $env['laravel'] }}</dd></div>
            <div><dt class="text-xs text-gray-500">Config / Route cache</dt><dd class="font-semibold">{{ $env['config_cached'] ? 'config cached' : 'config ไม่ได้ cache' }} · {{ $env['routes_cached'] ? 'route cached' : 'route ไม่ได้ cache' }}</dd></div>
            <div><dt class="text-xs text-gray-500">Assets (Vite)</dt><dd class="font-semibold">{{ $env['vite_hot'] ? 'dev server (hot)' : ($env['build_manifest_at'] ? 'build '.$time($env['build_manifest_at']) : 'ไม่พบ build') }}</dd></div>
            <div><dt class="text-xs text-gray-500">APP_URL</dt><dd class="font-semibold break-all">{{ $env['app_url'] }}</dd></div>
            <div><dt class="text-xs text-gray-500">Maintenance</dt><dd class="font-semibold">{{ $env['maintenance'] ? 'ปิดปรับปรุงอยู่' : 'เปิดใช้งาน' }}</dd></div>
        </dl>
    </section>
</div>

@if ($isRunning)
    <script>setTimeout(() => window.location.reload(), 15000)</script>
@endif
@endsection
