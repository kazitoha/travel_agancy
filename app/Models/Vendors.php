<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendors extends Model
{
    use LogsActivity;

    protected $table = 'vendors';

    protected $fillable = [
        'companies_id',
        'user_id',
        'name',
        'email',
        'mobile',
        'address',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class, 'companies_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
