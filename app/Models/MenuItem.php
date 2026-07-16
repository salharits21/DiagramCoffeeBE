<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'image_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // protected function imageUrl(): Attribute
    // {
    //     return Attribute::make(
    //         get: function ($value) {
    //             if (!$value) return null;
                
    //             // Jika value sudah berupa URL utuh (misal ada data lama/dummy), kembalikan langsung
    //             if (Str::startsWith($value, ['http://', 'https://'])) {
    //                 return $value;
    //             }

    //             // Jika berupa path (menu-images/...), generate URL dari disk S3/R2
    //             return Storage::disk('s3')->url($value);
    //         },
    //     );
    // }

    /**
     * Kategori dari menu ini.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Cabang-cabang yang menyediakan menu ini (via pivot).
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'menu_item_branch')
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
