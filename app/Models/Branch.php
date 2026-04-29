<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'status',
        'opening_time',
        'closing_time',
    ];

    protected function casts(): array
    {
        return [
            'opening_time' => 'datetime:H:i',
            'closing_time' => 'datetime:H:i',
        ];
    }

    /**
     * Admin yang bertugas di cabang ini.
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    /**
     * Menu items yang tersedia di cabang ini (via pivot).
     */
    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_branch')
            ->withPivot([
                'is_available',
                'stock',
                'discount_type',
                'discount_percentage',
                'discount_amount',
                'is_promo_active',
            ])
            ->withTimestamps();
    }

    /**
     * Relasi langsung ke pivot records.
     */
    public function menuItemBranches(): HasMany
    {
        return $this->hasMany(MenuItemBranch::class);
    }
}
