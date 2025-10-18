<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'cost_price',
        'sale_price',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    /**
     * Obter o registro de inventário do produto.
     */
    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    /**
     * Obter os itens de venda do produto.
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Calcular percentual de margem de lucro.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->sale_price == 0) {
            return 0;
        }

        return (($this->sale_price - $this->cost_price) / $this->sale_price) * 100;
    }

    /**
     * Calcular lucro por unidade.
     */
    public function getUnitProfitAttribute(): float
    {
        return $this->sale_price - $this->cost_price;
    }
}
