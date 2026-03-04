<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $organizationId = $this->currentOrganizationId();

        $categories = Category::with('organization')
            ->where('organization_id', $organizationId)
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->string('type')->toString());
            })
            ->when($request->filled('cost_type'), function ($query) use ($request) {
                $query->where('cost_type', $request->string('cost_type')->toString());
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->currentOrganizationId();

        $defaultType = $request->string('type')->toString();
        if (! in_array($defaultType, ['income', 'expense'], true)) {
            $defaultType = null;
        }

        $defaultCostType = $request->string('cost_type')->toString();
        if (! in_array($defaultCostType, ['fixed', 'variable'], true)) {
            $defaultCostType = null;
        }

        if ($defaultType !== 'expense') {
            $defaultCostType = null;
        }

        $lockedType = $defaultType;
        $lockedCostType = null;

        if ($defaultType === 'income') {
            $lockedCostType = 'income';
        }

        if ($defaultType === 'expense' && in_array($defaultCostType, ['fixed', 'variable'], true)) {
            $lockedCostType = $defaultCostType;
        }

        $organizationName = Auth::user()?->organization?->name ?? '-';

        return view('categories.create', compact(
            'organizationName',
            'defaultType',
            'defaultCostType',
            'lockedType',
            'lockedCostType',
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $organizationId = $this->currentOrganizationId();

        $validated = $request->validate([
            'organization_id' => ['prohibited'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense'],
            'cost_type' => [
                Rule::requiredIf(fn () => $request->string('type')->toString() === 'expense'),
                'nullable',
                Rule::in(['fixed', 'variable', 'income']),
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $type = $request->string('type')->toString();

                    if ($type === 'expense' && ! in_array($value, ['fixed', 'variable'], true)) {
                        $fail('Tipo de custo invalido para despesa.');
                    }
                },
            ],
        ]);

        if ($validated['type'] === 'income') {
            $validated['cost_type'] = 'income';
        }

        $validated['organization_id'] = $organizationId;

        Category::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $category = Category::with('organization')
            ->where('organization_id', $organizationId)
            ->findOrFail($id);

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $category = Category::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
        $organizationName = Auth::user()?->organization?->name ?? '-';

        return view('categories.edit', compact('category', 'organizationName'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $category = Category::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);

        $validated = $request->validate([
            'organization_id' => ['prohibited'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense'],
            'cost_type' => [
                Rule::requiredIf(fn () => $request->string('type')->toString() === 'expense'),
                'nullable',
                Rule::in(['fixed', 'variable', 'income']),
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $type = $request->string('type')->toString();

                    if ($type === 'expense' && ! in_array($value, ['fixed', 'variable'], true)) {
                        $fail('Tipo de custo invalido para despesa.');
                    }
                },
            ],
        ]);

        if ($validated['type'] === 'income') {
            $validated['cost_type'] = 'income';
        }

        $validated['organization_id'] = $organizationId;

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $category = Category::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria removida com sucesso.');
    }

    private function currentOrganizationId(): int
    {
        $organizationId = (int) Auth::user()?->organization_id;

        abort_if($organizationId <= 0, 403, 'Usuario sem organizacao vinculada.');

        return $organizationId;
    }
}
