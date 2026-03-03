<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TicketSalesPaymentHistory extends Model
{
    use CompanyScoped, LogsActivity;
    protected $table = 'ticket_sales_payment_histories';

    protected $fillable = [
        'ticket_sales_id',
        'account_id',
        'paid',
        'due',
        'company_id',
    ];

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function ticketSale()
    {
        return $this->belongsTo(TicketSales::class, 'ticket_sales_id');
    }
}
