<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'organization';

    protected $fillable = [
        'name',
        'cnpj',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function transactionGroups(): HasMany
    {
        return $this->hasMany(TransactionGroup::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
