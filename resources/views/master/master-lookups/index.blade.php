@extends('layouts.mainlayout')
@section('content')
<div class="p-3 lg:py-6 lg:px-0">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="mb-1 text-xl font-bold text-gray-900">{{ $catalog['title'] }}</h1><p class="mb-0 text-sm text-default">Manage product catalog values.</p></div>
        <a href="{{ route($catalog['route'].'.create') }}" class="btn-sm inline-flex items-center gap-2 border border-dark bg-dark text-white hover:bg-primary-hover"><i class="ph ph-plus"></i> Add entry</a>
    </div>
    @if (session('success'))<div class="mb-3 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>@endif
    <div class="rounded-md border border-border-color bg-white p-4">
        <form method="GET" class="mb-4 flex gap-2"><input name="q" value="{{ request('q') }}" class="w-full max-w-sm rounded border border-border-color px-3 py-2 text-sm" placeholder="Search"><button class="btn-sm border border-border-color bg-white px-3">Search</button>@if(request()->filled('q'))<a href="{{ route($catalog['route'].'.index') }}" class="btn-sm border border-border-color bg-white px-3">Clear</a>@endif</form>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead><tr class="border-b border-border-color text-left">@foreach($catalog['fields'] as $field => $label)<th class="px-2 py-2">{{ $label }}</th>@endforeach<th class="px-2 py-2">Actions</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr class="border-b border-border-color">
                    @foreach($catalog['fields'] as $field => $label)
                        <td class="px-2 py-2.5">{{ $field === 'is_default' ? ($item->$field ? 'Yes' : 'No') : ($item->$field ?? '—') }}</td>
                    @endforeach
                    <td class="px-2 py-2.5"><div class="flex gap-3"><a class="text-primary" href="{{ route($catalog['route'].'.edit', $item) }}">Edit</a><form method="POST" action="{{ route($catalog['route'].'.destroy', $item) }}" onsubmit="return confirm('Delete this entry?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="{{ count($catalog['fields']) + 1 }}" class="px-2 py-8 text-center text-default">No entries found.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</div>
@endsection
