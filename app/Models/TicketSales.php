<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSales extends Model
{
    protected $table = 'ticket_sales';

    protected $fillable = [
        'purchase_id',
        'customer_id',
        'account_id',
        'sell_price',
        'paid',
        'due',
        'issue_date',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'sell_price' => 'decimal:2',
            'paid' => 'decimal:2',
            'due' => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(TicketPurchases::class, 'purchase_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
}
