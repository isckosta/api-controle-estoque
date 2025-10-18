<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Sale $sale;

    /**
     * Criar uma nova instância do evento.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
    }
}
