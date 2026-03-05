<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Reference;

class Customers extends Model
{
    use LogsActivity, CompanyScoped;

    protected $table = 'customers';

    protected $fillable = [
        'company_id',
        'reference_id',
        'passport_number',
        'name',
        'email',
        'phone',
        'date_of_birth',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class);
    }
}
