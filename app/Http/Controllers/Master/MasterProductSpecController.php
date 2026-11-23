<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterProductSpec;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterProductSpecController extends Controller
{
    public function index(Request $request): View
    {
        $query = MasterProductSpec::query();

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(fn ($builder) => $builder
                ->where('code', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
                ->orWhere('brand', 'like', "%{$term}%"));
        }

        return view('master.master-product-specs.index', [
            'items' => $query->orderByDesc('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('master.master-product-specs.form', ['item' => new MasterProductSpec()]);
    }

    public function store(Request $request): RedirectResponse
    {
        MasterProductSpec::create($this->validatedData($request));

        return redirect()->route('master-product-specs.index')->with('success', 'Product specification created.');
    }

    public function edit(MasterProductSpec $masterProductSpec): View
    {
        return view('master.master-product-specs.form', ['item' => $masterProductSpec]);
    }

    public function update(Request $request, MasterProductSpec $masterProductSpec): RedirectResponse
    {
        $masterProductSpec->update($this->validatedData($request, $masterProductSpec));

        return redirect()->route('master-product-specs.index')->with('success', 'Product specification updated.');
    }

    public function destroy(MasterProductSpec $masterProductSpec): RedirectResponse
    {
        $masterProductSpec->delete();

        return redirect()->route('master-product-specs.index')->with('success', 'Product specification deleted.');
    }

    private function validatedData(Request $request, ?MasterProductSpec $item = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:master_product_specs,code'.($item ? ','.$item->id : '')],
            'name' => ['required', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'thickness_mm' => ['nullable', 'numeric', 'min:0'],
            'width_mm' => ['nullable', 'numeric', 'min:0'],
            'length_mm' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'full_desc' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:50'],
            'sheet_type' => ['nullable', 'string', 'max:50'],
            'edge_type' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
