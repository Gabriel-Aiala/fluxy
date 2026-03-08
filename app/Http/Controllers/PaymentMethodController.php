<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organizationId = $this->currentOrganizationId();

        $paymentMethods = PaymentMethod::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->paginate(10);

        return view('payment-methods.index', compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->currentOrganizationId();

        return view('payment-methods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $organizationId = $this->currentOrganizationId();

        $validated = $request->validate([
            'organization_id' => ['prohibited'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_method', 'name')->where(
                    fn ($query) => $query->where('organization_id', $organizationId)
                ),
            ],
        ]);

        $validated['organization_id'] = $organizationId;

        PaymentMethod::create($validated);

        return redirect()
            ->route('payment-methods.index')
            ->with('success', 'Forma de pagamento criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $paymentMethod = PaymentMethod::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);

        return view('payment-methods.show', compact('paymentMethod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $paymentMethod = PaymentMethod::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);

        return view('payment-methods.edit', compact('paymentMethod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $paymentMethod = PaymentMethod::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);

        $validated = $request->validate([
            'organization_id' => ['prohibited'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_method', 'name')
                    ->where(fn ($query) => $query->where('organization_id', $organizationId))
                    ->ignore($paymentMethod->id),
            ],
        ]);

        $validated['organization_id'] = $organizationId;

        $paymentMethod->update($validated);

        return redirect()
            ->route('payment-methods.index')
            ->with('success', 'Forma de pagamento atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $paymentMethod = PaymentMethod::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
        $paymentMethod->delete();

        return redirect()
            ->route('payment-methods.index')
            ->with('success', 'Forma de pagamento removida com sucesso.');
    }

    private function currentOrganizationId(): int
    {
        $organizationId = (int) Auth::user()?->organization_id;

        abort_if($organizationId <= 0, 403, 'Usuario sem organizacao vinculada.');

        return $organizationId;
    }
}
