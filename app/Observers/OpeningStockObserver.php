<?php

namespace App\Observers;

use App\Models\OpeningStock;
use App\Services\StockMovementService;

class OpeningStockObserver
{

    protected StockMovementService $stockMovementService;

    public function __construct(StockMovementService $stockMovementService)
    {
        $this->stockMovementService = $stockMovementService;
    }
    /**
     * Handle the OpeningStock "created" event.
     */
    public function created(OpeningStock $openingStock): void
    {
        $this->stockMovementService->recordMovement([
            'product_id' => $openingStock->product_id,
            'movement_type' => 'in',
            'transaction_type' => 'opening_stock',
            'quantity' => $openingStock->quantity,
            'unit_cost' => $openingStock->unit_cost,
            'movement_date' => $openingStock->opening_date,
            'reference_type' => OpeningStock::class,
            'reference_id' => $openingStock->id,
            'reference_number' => 'OS-' . $openingStock->id,
            'notes' => 'رصيد أول المدة - ' . ($openingStock->notes ?? '')
        ]);
    }

    /**
     * Handle the OpeningStock "updated" event.
     */
    public function updated(OpeningStock $openingStock): void
    {
        $movement = $this->stockMovementService->findByReference(OpeningStock::class, $openingStock->id);

        if ($movement) {
            $this->stockMovementService->updateMovement($movement->id, [
                'quantity' => $openingStock->quantity,
                'unit_cost' => $openingStock->unit_cost,
                'movement_date' => $openingStock->opening_date,
                'notes' => 'رصيد أول المدة - ' . ($openingStock->notes ?? '')
            ]);
        }
    }

    /**
     * Handle the OpeningStock "deleted" event.
     */
    public function deleted(OpeningStock $openingStock): void
    {
        $movement = $this->stockMovementService->findByReference(OpeningStock::class, $openingStock->id);

        if ($movement) {
            $this->stockMovementService->deleteMovement($movement->id);
        }
    }

    /**
     * Handle the OpeningStock "restored" event.
     */
    public function restored(OpeningStock $openingStock): void
    {
        //
    }

    /**
     * Handle the OpeningStock "force deleted" event.
     */
    public function forceDeleted(OpeningStock $openingStock): void
    {
        //
    }
}
