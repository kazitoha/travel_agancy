<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Reference;

class TicketSales extends Model
{
    use CompanyScoped, LogsActivity;
    protected $table = 'ticket_sales';

    protected $fillable = [
        'reference_id',
        'flight_date',
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
            'flight_date' => 'date',
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

    public function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class, 'reference_id');
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
