<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    public const TYPES = ['perte', 'casse', 'inventaire', 'autre'];

    public const TYPE_LABELS = [
        'perte' => 'Perte (vol, disparition)',
        'casse' => 'Casse (produit endommage)',
        'inventaire' => 'Inventaire (correction apres comptage)',
        'autre' => 'Autre',
    ];

    protected $fillable = [
        'product_id',
        'user_id',
        'quantity',
        'type',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
