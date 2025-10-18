<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    /**
     * Obter a venda que possui este item.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Obter o produto que possui este item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obter subtotal deste item.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Obter custo total deste item.
     */
    public function getTotalCostAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    /**
     * Obter lucro deste item.
     */
    public function getProfitAttribute(): float
    {
        return $this->subtotal - $this->total_cost;
    }
}
