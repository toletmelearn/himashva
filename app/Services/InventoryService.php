<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class InventoryService
{
    /**
     * Apply a stock delta to a product (or one of its variants) and log it
     * as an inventory movement. Positive deltas increase stock, negative
     * deltas decrease it.
     */
    public function recordMovement(
        Product $product,
        string $type,
        int $quantityDelta,
        ?string $reason = null,
        ?int $actorId = null,
        ?Model $reference = null,
        ?ProductVariant $variant = null,
    ): InventoryMovement {
        if ($variant) {
            $variant->increment('stock', $quantityDelta);
        } else {
            $product->increment('stock', $quantityDelta);
            $product->refresh();
            $product->syncStockStatus();
            if ($product->isDirty('status')) {
                $product->save();
            }
        }

        return InventoryMovement::create([
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'type' => $type,
            'quantity_delta' => $quantityDelta,
            'reason' => $reason,
            'actor_id' => $actorId,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
        ]);
    }

    public function getStockHistory(int $productId): Collection
    {
        return InventoryMovement::where('product_id', $productId)->latest()->get();
    }

    public function adjustStock(int $productId, int $delta, ?string $reason = null, ?int $actorId = null): InventoryMovement
    {
        $product = Product::findOrFail($productId);

        return $this->recordMovement($product, 'adjustment', $delta, $reason, $actorId);
    }
}
