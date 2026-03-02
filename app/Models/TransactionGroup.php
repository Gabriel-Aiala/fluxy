<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionGroup extends Model
{
    use HasFactory;

    protected $table = 'transaction_group';

    protected $fillable = [
        'id',
        'organization_id',
        'type',
        'description',
        'occurred_on',
        'customer_installments',
        'flow_installments',
        'anticipation',
        'created_at',
        'updated_at',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transaction_group_id');
    }
}
