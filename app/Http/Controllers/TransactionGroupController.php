<?php

namespace App\Http\Controllers;

use App\Models\TransactionGroup;

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transactionGroup = TransactionGroup::with('organization')
            ->withCount('transactions')
            ->findOrFail($id);

        return view('transaction-groups.show', compact('transactionGroup'));
    }
}
