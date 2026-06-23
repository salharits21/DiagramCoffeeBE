<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'fee_key',
        'fee_label',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Pesanan terkait.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
