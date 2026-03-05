<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketPurchases extends Model
{
    use LogsActivity, CompanyScoped;
    protected $table = 'ticket_purchases';

    protected $fillable = [
        'vendor_id',
        'customer_id',
        'account_id',
        'flight_date',
        'sector',
        'carrier',
        'net_fare',
        'paid_amount',
        'due_amount',
        'issue_date',
        'notes',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'flight_date' => 'date',
            'issue_date' => 'date',
            'net_fare' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendors::class, 'vendor_id');
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
    public function ticketSale()
    {
        return $this->hasOne(TicketSales::class, 'purchase_id', 'id');
    }
}
