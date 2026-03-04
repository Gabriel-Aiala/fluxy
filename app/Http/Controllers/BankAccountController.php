<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organizationId = $this->currentOrganizationId();

        $bankAccounts = BankAccount::with('organization')
            ->where('organization_id', $organizationId)
            ->orderByDesc('id')
            ->paginate(10);

        return view('bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->currentOrganizationId();
        $organizationName = Auth::user()?->organization?->name ?? '-';

        return view('bank-accounts.create', compact('organizationName'));
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
        ]);

        $validated['organization_id'] = $organizationId;

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
        $organizationId = $this->currentOrganizationId();

        $bankAccount = BankAccount::with('organization')
            ->where('organization_id', $organizationId)
            ->findOrFail($id);

        return view('bank-accounts.show', compact('bankAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $bankAccount = BankAccount::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
        $organizationName = Auth::user()?->organization?->name ?? '-';

        return view('bank-accounts.edit', compact('bankAccount', 'organizationName'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $bankAccount = BankAccount::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);

        $validated = $request->validate([
            'organization_id' => ['prohibited'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['organization_id'] = $organizationId;

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
        $organizationId = $this->currentOrganizationId();

        $bankAccount = BankAccount::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
        $bankAccount->delete();

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Conta bancaria removida com sucesso.');
    }

    private function currentOrganizationId(): int
    {
        $organizationId = (int) Auth::user()?->organization_id;

        abort_if($organizationId <= 0, 403, 'Usuario sem organizacao vinculada.');

        return $organizationId;
    }
}
