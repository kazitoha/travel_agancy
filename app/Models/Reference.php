<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reference extends Model
{
    use LogsActivity, CompanyScoped;

    protected $fillable = [
        'company_id',
        'company_name',
        'contact_person_name',
        'phone',
        'address',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customers::class);
    }
}
