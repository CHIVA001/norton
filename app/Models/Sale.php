<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;
    protected $fillable = [
        'stock_id',
        'qty',
        'price',
        'total',
        'sale_date',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'integer',
        'total' => 'integer',
        'sale_date' => 'datetime',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
