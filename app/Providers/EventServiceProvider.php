<?php

namespace App\Providers;

use App\Events\SaleCompleted;
use App\Listeners\UpdateInventoryOnSale;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Os mapeamentos de event listeners para a aplicação.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        SaleCompleted::class => [
            UpdateInventoryOnSale::class,
        ],
    ];

    /**
     * Registrar quaisquer eventos para sua aplicação.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determinar se eventos e listeners devem ser descobertos automaticamente.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
