<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accounts extends Model
{
    use LogsActivity, CompanyScoped;


    protected $fillable = [
        'user_id',
        'name',
        'type',
        'opening_balance',
        'current_balance',
        'status',
        'logo',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Accounts $account) {
            if ($account->current_balance === null) {
                $account->current_balance = $account->opening_balance;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expenses::class, 'account_id');
    }
}
