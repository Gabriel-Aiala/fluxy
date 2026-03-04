<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Counterparties extends Model
{
    use HasFactory;

    protected $table = 'counterparties';

    protected $fillable = [
        'organization_id',
        'name',
        'type',
    ];

    protected $appends = [
        'type_label',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'counterparty_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'client' => 'Cliente',
            'supplier' => 'Fornecedor',
            default => (string) $this->type,
        };
    }
}
