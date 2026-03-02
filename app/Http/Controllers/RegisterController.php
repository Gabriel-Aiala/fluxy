<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\PaymentMethod;

class RegisterController extends Controller
{
    public function index()
    {
        $incomeCategories = Category::query()
            ->where('type', 'income')
            ->orderBy('name')
            ->get();

        $fixedExpenseCategories = Category::query()
            ->where('type', 'expense')
            ->where('cost_type', 'fixed')
            ->orderBy('name')
            ->get();

        $commonExpenseCategories = Category::query()
            ->where('type', 'expense')
            ->where('cost_type', 'variable')
            ->orderBy('name')
            ->get();

        $bankAccounts = BankAccount::query()
            ->orderBy('name')
            ->get();

        $paymentMethods = PaymentMethod::query()
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
}
