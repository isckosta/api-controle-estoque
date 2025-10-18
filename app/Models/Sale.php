<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_amount',
        'total_cost',
        'total_profit',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_profit' => 'decimal:2',
    ];

    /**
     * Obter os itens da venda.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Calcular totais a partir dos itens.
     */
    public function calculateTotals(): void
    {
        $this->total_amount = $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $this->total_cost = $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_cost;
        });

        $this->total_profit = $this->total_amount - $this->total_cost;
    }

    /**
     * Marcar venda como concluída.
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Marcar venda como cancelada.
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Verificar se a venda está concluída.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verificar se a venda está pendente.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Obter percentual de margem de lucro.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->total_amount == 0) {
            return 0;
        }

        return ($this->total_profit / $this->total_amount) * 100;
    }
}
