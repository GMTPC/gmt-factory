<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterBrand;
use App\Models\Master\MasterEdge;
use App\Models\Master\MasterFace;
use App\Models\Master\MasterSheetType;
use App\Models\Master\MasterSize;
use App\Models\Master\MasterThickness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class MasterLookupController extends Controller
{
    private const CATALOGS = [
        'brands' => ['title' => 'Brands', 'route' => 'master-brands', 'model' => MasterBrand::class, 'search' => ['code', 'name'], 'fields' => ['code' => 'Code', 'name' => 'Name']],
        'edges' => ['title' => 'Edges', 'route' => 'master-edges', 'model' => MasterEdge::class, 'search' => ['code', 'name'], 'fields' => ['code' => 'Code', 'name' => 'Name']],
        'faces' => ['title' => 'Faces', 'route' => 'master-faces', 'model' => MasterFace::class, 'search' => ['code', 'name'], 'fields' => ['code' => 'Code', 'name' => 'Name', 'is_default' => 'Default face']],
        'sheet-types' => ['title' => 'Sheet Types', 'route' => 'master-sheet-types', 'model' => MasterSheetType::class, 'search' => ['code', 'name'], 'fields' => ['code' => 'Code', 'name' => 'Name']],
        'sizes' => ['title' => 'Sizes', 'route' => 'master-sizes', 'model' => MasterSize::class, 'search' => ['code', 'label'], 'fields' => ['code' => 'Code', 'label' => 'Label', 'width_mm' => 'Width (mm)', 'length_mm' => 'Length (mm)']],
        'thicknesses' => ['title' => 'Thicknesses', 'route' => 'master-thicknesses', 'model' => MasterThickness::class, 'search' => ['code', 'label'], 'fields' => ['code' => 'Code', 'mm' => 'Thickness (mm)', 'label' => 'Label']],
    ];

    public function index(Request $request): View
    {
        $catalog = $this->catalog($request);
        $model = $catalog['model'];
        $query = $model::query();

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(fn ($builder) => collect($catalog['search'])->each(
                fn (string $field, int $index) => $index === 0
                    ? $builder->where($field, 'like', "%{$term}%")
                    : $builder->orWhere($field, 'like', "%{$term}%")
            ));
        }

        return view('master.master-lookups.index', [
            'catalog' => $catalog,
            'items' => $query->orderByDesc('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        $model = $this->catalog($request)['model'];
        return $this->form($request, new $model());
    }

    public function store(Request $request): RedirectResponse
    {
        $catalog = $this->catalog($request);
        $data = $this->validatedData($request, $catalog);
        $model = $catalog['model'];
        $model::create($data);

        return redirect()->route($catalog['route'].'.index')->with('success', $catalog['title'].' entry created.');
    }

    public function edit(Request $request): View
    {
        $catalog = $this->catalog($request);
        return $this->form($request, $this->item($request, $catalog));
    }

    public function update(Request $request): RedirectResponse
    {
        $catalog = $this->catalog($request);
        $item = $this->item($request, $catalog);
        $item->update($this->validatedData($request, $catalog, $item));

        return redirect()->route($catalog['route'].'.index')->with('success', $catalog['title'].' entry updated.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $catalog = $this->catalog($request);
        $this->item($request, $catalog)->delete();

        return redirect()->route($catalog['route'].'.index')->with('success', $catalog['title'].' entry deleted.');
    }

    private function form(Request $request, Model $item): View
    {
        return view('master.master-lookups.form', [
            'catalog' => $this->catalog($request),
            'item' => $item,
        ]);
    }

    private function catalog(Request $request): array
    {
        $key = $request->route('catalog');
        abort_unless(isset(self::CATALOGS[$key]), 404);

        return self::CATALOGS[$key];
    }

    private function item(Request $request, array $catalog): Model
    {
        $id = $request->route('item');
        abort_unless($id, 404);

        $model = $catalog['model'];
        return $model::findOrFail($id);
    }

    private function validatedData(Request $request, array $catalog, ?Model $item = null): array
    {
        $model = $catalog['model'];
        $table = (new $model())->getTable();
        $rules = [];

        foreach ($catalog['fields'] as $field => $label) {
            if ($field === 'is_default') {
                $rules[$field] = ['nullable', 'boolean'];
            } elseif (in_array($field, ['width_mm', 'length_mm', 'mm'], true)) {
                $rules[$field] = ['nullable', 'numeric', 'min:0'];
            } elseif (in_array($field, ['code'], true)) {
                $rules[$field] = ['required', 'string', 'max:50', 'unique:'.$table.','.$field.($item ? ','.$item->getKey() : '')];
            } else {
                $rules[$field] = [$field === 'label' && $table === 'master_thicknesses' ? 'nullable' : 'required', 'string', 'max:255'];
            }
        }

        $data = $request->validate($rules);
        if (array_key_exists('is_default', $catalog['fields'])) {
            $data['is_default'] = $request->boolean('is_default');
        }

        return Arr::only($data, array_keys($catalog['fields']));
    }
}
