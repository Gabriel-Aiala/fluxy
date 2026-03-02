<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transaction';

    protected $fillable = [
        'organization_id',
        'transaction_group_id',
        'bank_account_id',
        'payment_method_id',
        'counterparty_id',
        'category_id',
        'installment_number',
        'expected_payment_date',
        'payment_date',
        'amount',
        'payment_status',
        'expense_type',
        'type',
    ];

    protected $casts = [
        'expected_payment_date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function transactionGroup(): BelongsTo
    {
        return $this->belongsTo(TransactionGroup::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(Counterparties::class, 'counterparty_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
