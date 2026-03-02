<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\TransactionGroup;
use Illuminate\Http\Request;

class TransactionGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactionGroups = TransactionGroup::with('organization')
            ->withCount('transactions')
            ->orderByDesc('id')
            ->paginate(10);

        return view('transaction-groups.index', compact('transactionGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organizations = Organization::orderBy('name')->get();

        return view('transaction-groups.create', compact('organizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organization,id'],
            'type' => ['required', 'in:income,expense'],
            'description' => ['nullable', 'string', 'max:255'],
            'occurred_on' => ['required', 'date'],
            'customer_installments' => ['required', 'integer', 'min:1'],
            'flow_installments' => ['required', 'integer', 'min:1'],
            'anticipation' => ['nullable', 'boolean'],
        ]);

        $validated['anticipation'] = $request->boolean('anticipation');

        TransactionGroup::create($validated);

        return redirect()
            ->route('transaction-groups.index')
            ->with('success', 'Grupo de transacao criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transactionGroup = TransactionGroup::with('organization')
            ->withCount('transactions')
            ->findOrFail($id);

        return view('transaction-groups.show', compact('transactionGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $transactionGroup = TransactionGroup::findOrFail($id);
        $organizations = Organization::orderBy('name')->get();

        return view('transaction-groups.edit', compact('transactionGroup', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $transactionGroup = TransactionGroup::findOrFail($id);

        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organization,id'],
            'type' => ['required', 'in:income,expense'],
            'description' => ['nullable', 'string', 'max:255'],
            'occurred_on' => ['required', 'date'],
            'customer_installments' => ['required', 'integer', 'min:1'],
            'flow_installments' => ['required', 'integer', 'min:1'],
            'anticipation' => ['nullable', 'boolean'],
        ]);

        $validated['anticipation'] = $request->boolean('anticipation');

        $transactionGroup->update($validated);

        return redirect()
            ->route('transaction-groups.index')
            ->with('success', 'Grupo de transacao atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transactionGroup = TransactionGroup::findOrFail($id);
        $transactionGroup->delete();

        return redirect()
            ->route('transaction-groups.index')
            ->with('success', 'Grupo de transacao removido com sucesso.');
    }
}
