@extends('layouts.mainlayout')
@section('content')
<div class="p-3 lg:py-6 lg:px-0">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div><h1 class="text-gray-900 text-xl font-bold mb-1">Product Specifications</h1><p class="text-sm text-default mb-0">Manage product codes, dimensions, and production specifications.</p></div>
        <a href="{{ route('master-product-specs.create') }}" class="btn-sm bg-dark text-white border border-dark inline-flex items-center gap-2 hover:bg-primary-hover"><i class="ph ph-plus"></i> Add specification</a>
    </div>
    @if (session('success'))<div class="mb-3 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>@endif
    <div class="bg-white border border-border-color rounded-md p-4">
        <form method="GET" class="mb-4 flex gap-2"><input name="q" value="{{ request('q') }}" class="w-full max-w-sm rounded border border-border-color px-3 py-2 text-sm" placeholder="Search code, name, or brand"><button class="btn-sm border border-border-color bg-white px-3">Search</button></form>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="border-b border-border-color text-left"><th class="px-2 py-2">Code</th><th class="px-2 py-2">Name</th><th class="px-2 py-2">Brand / Grade</th><th class="px-2 py-2">Dimensions (mm)</th><th class="px-2 py-2">Category</th><th class="px-2 py-2">Actions</th></tr></thead><tbody>
            @forelse ($items as $item)
            <tr class="border-b border-border-color"><td class="px-2 py-2.5 font-semibold">{{ $item->code }}</td><td class="px-2 py-2.5">{{ $item->name }}</td><td class="px-2 py-2.5">{{ $item->brand ?: '—' }} / {{ $item->grade ?: '—' }}</td><td class="px-2 py-2.5">{{ $item->thickness_mm ?? '—' }} × {{ $item->width_mm ?? '—' }} × {{ $item->length_mm ?? '—' }}</td><td class="px-2 py-2.5">{{ $item->category ?: '—' }}</td><td class="px-2 py-2.5"><div class="flex gap-3"><a class="text-primary" href="{{ route('master-product-specs.edit', $item) }}">Edit</a><form method="POST" action="{{ route('master-product-specs.destroy', $item) }}" onsubmit="return confirm('Delete this product specification?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form></div></td></tr>
            @empty<tr><td colspan="6" class="px-2 py-8 text-center text-default">No product specifications found.</td></tr>@endforelse
        </tbody></table></div><div class="mt-4">{{ $items->links() }}</div>
    </div>
</div>
@endsection
