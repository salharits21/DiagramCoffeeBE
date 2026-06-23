<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_name',
        'branch_id',
        'order_type',
        'table_number',
        'order_number',
        'status',
        'payment_method',
        'payment_status',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'subtotal',
        'discount_total',
        'voucher_id',
        'total_amount',
        'loyalty_points_earned',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // ==========================================
    // Relationships
    // ==========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Item pesanan.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Rincian fee tambahan (admin fee, dll).
     */
    public function fees(): HasMany
    {
        return $this->hasMany(OrderFee::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeByBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    // ==========================================
    // Helpers
    // ==========================================

    /**
     * Generate nomor pesanan unik: ORD-YYYYMMDD-XXXXX
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -5));
        return "ORD-{$date}-{$random}";
    }

    /**
     * Cek apakah order bisa di-cancel.
     */
    public function isCancellable(): bool
    {
        return $this->status === 'pending';
    }
}
