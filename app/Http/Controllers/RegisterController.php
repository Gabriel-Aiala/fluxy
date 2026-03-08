<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function index()
    {
        $organizationId = $this->currentOrganizationId();

        $incomeCategories = Category::query()
            ->where('organization_id', $organizationId)
            ->where('type', 'income')
            ->orderBy('name')
            ->get();

        $fixedExpenseCategories = Category::query()
            ->where('organization_id', $organizationId)
            ->where('type', 'expense')
            ->where('cost_type', 'fixed')
            ->orderBy('name')
            ->get();

        $commonExpenseCategories = Category::query()
            ->where('organization_id', $organizationId)
            ->where('type', 'expense')
            ->where('cost_type', 'variable')
            ->orderBy('name')
            ->get();

        $bankAccounts = BankAccount::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();

        $paymentMethods = PaymentMethod::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();

        return view('registers.index', compact(
            'incomeCategories',
            'fixedExpenseCategories',
            'commonExpenseCategories',
            'bankAccounts',
            'paymentMethods'
        ));
    }

    private function currentOrganizationId(): int
    {
        $organizationId = (int) Auth::user()?->organization_id;

        abort_if($organizationId <= 0, 403, 'Usuario sem organizacao vinculada.');

        return $organizationId;
    }
}
