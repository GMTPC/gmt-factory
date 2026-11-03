@extends('layouts.authlayout')
@section('content')

    <!-- Start Content -->
    <div class="text-center"> <i class="ph-duotone ph-warning-circle text-9xl text-warning mb-4"></i>
        <h1 class="text-9xl font-bold text-title mb-2">404</h1>
        <h2 class="text-xl font-semibold text-title mb-2">Page Not Found</h2>
        <p class="text-sm text-default mb-6">The page you're looking for doesn't exist or has been moved.</p>
        <a href="{{url('index')}}" class="inline-flex items-center gap-2 bg-dark text-white px-5 py-2.5 rounded-md text-sm font-semibold hover:bg-primary-hover cursor-pointer"><i class="ph ph-house"></i> Back to Home</a>
    </div>

@endsection
