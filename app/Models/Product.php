<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'purchase_price',
        'sale_price',
        'quantity',
        'image',
        'max_discount',
        'stock_alert',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'quantity' => 'integer',
        'max_discount' => 'integer',
        'stock_alert' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockEntries(): HasMany
    {
        return $this->hasMany(StockEntry::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->stock_alert;
    }

    public function getMargeAttribute(): float
    {
        return $this->sale_price - $this->purchase_price;
    }

    public function getMargePercentAttribute(): float
    {
        return $this->purchase_price > 0
            ? ($this->marge / $this->purchase_price) * 100
            : 0;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }
}
