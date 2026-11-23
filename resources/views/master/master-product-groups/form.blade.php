@extends('layouts.mainlayout')
@section('content')
<div class="p-3 lg:py-6 lg:px-0 max-w-3xl">
    <div class="mb-4"><h1 class="text-gray-900 text-xl font-bold mb-1">{{ $item->exists ? 'Edit product group' : 'Add product group' }}</h1><a href="{{ route('master-product-groups.index') }}" class="text-sm text-primary">Back to product groups</a></div>
    <form method="POST" action="{{ $item->exists ? route('master-product-groups.update', $item) : route('master-product-groups.store') }}" class="space-y-4 rounded-md border border-border-color bg-white p-4">
        @csrf @if($item->exists) @method('PUT') @endif
        <label class="block text-sm font-medium">Code<input name="code" value="{{ old('code', $item->code) }}" maxlength="10" required class="mt-1 w-full rounded border border-border-color px-3 py-2">@error('code')<span class="text-red-600">{{ $message }}</span>@enderror</label>
        <label class="block text-sm font-medium">Name<input name="name" value="{{ old('name', $item->name) }}" required class="mt-1 w-full rounded border border-border-color px-3 py-2">@error('name')<span class="text-red-600">{{ $message }}</span>@enderror</label>
        <div class="flex gap-2"><button class="btn-sm bg-dark px-4 text-white">Save</button><a href="{{ route('master-product-groups.index') }}" class="btn-sm border border-border-color px-4">Cancel</a></div>
    </form>
</div>
@endsection
