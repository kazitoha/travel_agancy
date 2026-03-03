<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketPurchasePaymentHistory extends Model
{
    protected $fillable = [
        'ticket_purchase_id',
        'account_id',
        'paid',
        'due',
        'company_id',
    ];

    public function ticketPurchase()
    {
        return $this->belongsTo(TicketPurchases::class, 'ticket_purchase_id');
    }

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }
}
