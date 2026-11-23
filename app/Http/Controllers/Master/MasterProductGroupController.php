<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterProductGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterProductGroupController extends Controller
{
    public function index(Request $request): View
    {
        $query = MasterProductGroup::query();

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(fn ($builder) => $builder
                ->where('code', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%"));
        }

        return view('master.master-product-groups.index', [
            'items' => $query->orderByDesc('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('master.master-product-groups.form', ['item' => new MasterProductGroup()]);
    }

    public function store(Request $request): RedirectResponse
    {
        MasterProductGroup::create($request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:master_product_groups,code'],
            'name' => ['required', 'string', 'max:255'],
        ]));

        return redirect()->route('master-product-groups.index')->with('success', 'Product group created.');
    }

    public function edit(MasterProductGroup $masterProductGroup): View
    {
        return view('master.master-product-groups.form', ['item' => $masterProductGroup]);
    }

    public function update(Request $request, MasterProductGroup $masterProductGroup): RedirectResponse
    {
        $masterProductGroup->update($request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:master_product_groups,code,'.$masterProductGroup->id],
            'name' => ['required', 'string', 'max:255'],
        ]));

        return redirect()->route('master-product-groups.index')->with('success', 'Product group updated.');
    }

    public function destroy(MasterProductGroup $masterProductGroup): RedirectResponse
    {
        $masterProductGroup->delete();

        return redirect()->route('master-product-groups.index')->with('success', 'Product group deleted.');
    }
}
