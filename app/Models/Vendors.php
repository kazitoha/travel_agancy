<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendors extends Model
{
    use LogsActivity, CompanyScoped;

    protected $table = 'vendors';

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'address',
        'company_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
}
