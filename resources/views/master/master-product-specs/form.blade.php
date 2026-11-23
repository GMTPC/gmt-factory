@extends('layouts.mainlayout')
@section('content')
<div class="p-3 lg:py-6 lg:px-0 max-w-5xl">
    <div class="mb-4"><h1 class="text-gray-900 text-xl font-bold mb-1">{{ $item->exists ? 'Edit product specification' : 'Add product specification' }}</h1><a href="{{ route('master-product-specs.index') }}" class="text-sm text-primary">Back to product specifications</a></div>
    <form method="POST" action="{{ $item->exists ? route('master-product-specs.update', $item) : route('master-product-specs.store') }}" class="space-y-4 rounded-md border border-border-color bg-white p-4">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach(['code'=>'Code','name'=>'Name','brand'=>'Brand','grade'=>'Grade','category'=>'Category','status'=>'Status','sheet_type'=>'Sheet type','edge_type'=>'Edge type'] as $field => $label)
            <label class="block text-sm font-medium">{{ $label }}<input name="{{ $field }}" value="{{ old($field, $item->$field) }}" @if(in_array($field, ['code','name'])) required @endif class="mt-1 w-full rounded border border-border-color px-3 py-2">@error($field)<span class="text-red-600">{{ $message }}</span>@enderror</label>
            @endforeach
            @foreach(['thickness_mm'=>'Thickness (mm)','width_mm'=>'Width (mm)','length_mm'=>'Length (mm)'] as $field => $label)
            <label class="block text-sm font-medium">{{ $label }}<input type="number" min="0" step="0.01" name="{{ $field }}" value="{{ old($field, $item->$field) }}" class="mt-1 w-full rounded border border-border-color px-3 py-2">@error($field)<span class="text-red-600">{{ $message }}</span>@enderror</label>
            @endforeach
        </div>
        @foreach(['description'=>'Description','full_desc'=>'Full description'] as $field => $label)
        <label class="block text-sm font-medium">{{ $label }}<textarea name="{{ $field }}" rows="3" class="mt-1 w-full rounded border border-border-color px-3 py-2">{{ old($field, $item->$field) }}</textarea>@error($field)<span class="text-red-600">{{ $message }}</span>@enderror</label>
        @endforeach
        <div class="flex gap-2"><button class="btn-sm bg-dark px-4 text-white">Save</button><a href="{{ route('master-product-specs.index') }}" class="btn-sm border border-border-color px-4">Cancel</a></div>
    </form>
</div>
@endsection
