<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::with('organization')
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
    public function create()
    {
        $organizations = Organization::orderBy('name')->get();
        $defaultType = request()->string('type')->toString();
        $defaultCostType = request()->string('cost_type')->toString();

        return view('categories.create', compact('organizations', 'defaultType', 'defaultCostType'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organization,id'],
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
        $category = Category::with('organization')->findOrFail($id);

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        $organizations = Organization::orderBy('name')->get();

        return view('categories.edit', compact('category', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organization,id'],
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
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria removida com sucesso.');
    }
}
