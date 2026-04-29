<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemBranch extends Model
{
    use HasFactory;

    protected $table = 'menu_item_branch';

    protected $fillable = [
        'menu_item_id',
        'branch_id',
        'is_available',
        'stock',
        'discount_type',
        'discount_percentage',
        'discount_amount',
        'is_promo_active',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'is_promo_active' => 'boolean',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => 'decimal:2',
        ];
    }

    /**
     * Menu item terkait.
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Cabang terkait.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Hitung harga final setelah diskon (jika promo aktif).
     */
    public function getFinalPriceAttribute(): string
    {
        $basePrice = $this->menuItem->base_price;

        if (!$this->is_promo_active || !$this->discount_type) {
            return $basePrice;
        }

        if ($this->discount_type === 'percentage') {
            return bcsub($basePrice, bcmul($basePrice, bcdiv($this->discount_percentage, '100', 4), 2), 2);
        }

        if ($this->discount_type === 'fixed') {
            $finalPrice = bcsub($basePrice, $this->discount_amount, 2);
            return bccomp($finalPrice, '0', 2) > 0 ? $finalPrice : '0.00';
        }

        return $basePrice;
    }
}
