<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StockMovement;
use App\Models\StockProduct;
use Illuminate\Support\Facades\DB;

class OrderStockService
{
    /**
     * À la livraison d'une commande : déduire le stock pour chaque article de menu lié à un produit stock.
     */
    public function deductStockForOrder(Order $order): void
    {
        $order->load(['orderItems.menuItem.stockProduct']);

        foreach ($order->orderItems as $orderItem) {
            $menuItem = $orderItem->menuItem;
            if (!$menuItem || !$menuItem->stock_product_id || !$menuItem->stockProduct) {
                continue;
            }

            $product = $menuItem->stockProduct;
            if ($product->enterprise_id !== $order->enterprise_id) {
                continue;
            }
            $quantityToDeduct = $orderItem->quantity * (float) $menuItem->stock_quantity_per_portion;
            if ($quantityToDeduct <= 0) {
                continue;
            }

            DB::transaction(function () use ($order, $orderItem, $product, $quantityToDeduct) {
                StockMovement::create([
                    'enterprise_id' => $order->enterprise_id,
                    'stock_product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $quantityToDeduct,
                    'unit_cost' => $product->unit_cost,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'user_id' => auth()->id(),
                    'notes' => 'Commande ' . $order->order_number . ' — ' . $orderItem->item_name . ' x' . $orderItem->quantity,
                ]);

                $product->decrement('quantity_current', $quantityToDeduct);
            });
        }
    }
}
