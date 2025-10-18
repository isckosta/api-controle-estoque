<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'product_id',
        'quantity',
        'last_updated',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'last_updated' => 'datetime',
    ];

    /**
     * Obter o produto que possui este inventário.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Adicionar quantidade ao inventário.
     */
    public function addQuantity(int $quantity): void
    {
        $this->increment('quantity', $quantity);
        $this->update(['last_updated' => now()]);
    }

    /**
     * Remover quantidade do inventário.
     */
    public function removeQuantity(int $quantity): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }

        $this->decrement('quantity', $quantity);
        $this->update(['last_updated' => now()]);

        return true;
    }

    /**
     * Verificar se há estoque suficiente.
     */
    public function hasStock(int $quantity): bool
    {
        return $this->quantity >= $quantity;
    }

    /**
     * Obter valor total no inventário (custo).
     */
    public function getTotalCostAttribute(): float
    {
        return $this->quantity * $this->product->cost_price;
    }

    /**
     * Obter valor total no inventário (venda).
     */
    public function getTotalValueAttribute(): float
    {
        return $this->quantity * $this->product->sale_price;
    }

    /**
     * Obter lucro projetado.
     */
    public function getProjectedProfitAttribute(): float
    {
        return $this->total_value - $this->total_cost;
    }
}
