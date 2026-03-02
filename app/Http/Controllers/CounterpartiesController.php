<?php

namespace App\Http\Controllers;

use App\Models\Counterparties;
use App\Models\Organization;
use Illuminate\Http\Request;

class CounterpartiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $counterparties = Counterparties::with('organization')
            ->orderByDesc('id')
            ->paginate(10);

        return view('counterparties.index', compact('counterparties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organizations = Organization::orderBy('name')->get();

        return view('counterparties.create', compact('organizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organization,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:client,supplier'],
        ]);

        Counterparties::create($validated);

        return redirect()
            ->route('counterparties.index')
            ->with('success', 'Contraparte criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $counterparty = Counterparties::with('organization')->findOrFail($id);

        return view('counterparties.show', compact('counterparty'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $counterparty = Counterparties::findOrFail($id);
        $organizations = Organization::orderBy('name')->get();

        return view('counterparties.edit', compact('counterparty', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $counterparty = Counterparties::findOrFail($id);

        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organization,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:client,supplier'],
        ]);

        $counterparty->update($validated);

        return redirect()
            ->route('counterparties.index')
            ->with('success', 'Contraparte atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $counterparty = Counterparties::findOrFail($id);
        $counterparty->delete();

        return redirect()
            ->route('counterparties.index')
            ->with('success', 'Contraparte removida com sucesso.');
    }
}
