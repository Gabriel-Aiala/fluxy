<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Organization;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bankAccounts = BankAccount::with('organization')
            ->orderByDesc('id')
            ->paginate(10);

        return view('bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organizations = Organization::orderBy('name')->get();

        return view('bank-accounts.create', compact('organizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organization,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        BankAccount::create($validated);

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Conta bancaria criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bankAccount = BankAccount::with('organization')->findOrFail($id);

        return view('bank-accounts.show', compact('bankAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $organizations = Organization::orderBy('name')->get();

        return view('bank-accounts.edit', compact('bankAccount', 'organizations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organization,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $bankAccount->update($validated);

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Conta bancaria atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $bankAccount->delete();

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Conta bancaria removida com sucesso.');
    }
}
