<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserVoucher extends Pivot
{
    protected $table = 'user_vouchers';

    protected $fillable = [
        'user_id',
        'voucher_id',
        'is_used',
        'used_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'is_used' => 'boolean',
            'used_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
