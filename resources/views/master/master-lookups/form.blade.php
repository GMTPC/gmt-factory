@extends('layouts.mainlayout')
@section('content')
<div class="p-3 lg:py-6 lg:px-0 max-w-3xl">
    <div class="mb-4"><h1 class="mb-1 text-xl font-bold text-gray-900">{{ $item->exists ? 'Edit '.$catalog['title'] : 'Add '.$catalog['title'] }}</h1><a href="{{ route($catalog['route'].'.index') }}" class="text-sm text-primary">Back to {{ strtolower($catalog['title']) }}</a></div>
    <form method="POST" action="{{ $item->exists ? route($catalog['route'].'.update', $item) : route($catalog['route'].'.store') }}" class="space-y-4 rounded-md border border-border-color bg-white p-4">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach($catalog['fields'] as $field => $label)
                @if($field === 'is_default')
                    <label class="flex items-center gap-2 text-sm font-medium"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $item->is_default))> {{ $label }}</label>
                @else
                    <label class="block text-sm font-medium">{{ $label }}
                        <input name="{{ $field }}" value="{{ old($field, $item->$field) }}" @if(in_array($field, ['width_mm','length_mm','mm'])) type="number" min="0" step="0.01" @endif @if($field === 'code' || ($field !== 'label' || $catalog['route'] !== 'master-thicknesses')) required @endif class="mt-1 w-full rounded border border-border-color px-3 py-2">
                        @error($field)<span class="text-red-600">{{ $message }}</span>@enderror
                    </label>
                @endif
            @endforeach
        </div>
        <div class="flex gap-2"><button class="btn-sm bg-dark px-4 text-white">Save</button><a href="{{ route($catalog['route'].'.index') }}" class="btn-sm border border-border-color px-4">Cancel</a></div>
    </form>
</div>
@endsection
