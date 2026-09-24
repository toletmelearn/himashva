<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function store(Request $request, string $orderNumber)
    {
        $order = Order::with('items')
            ->where('user_id', Auth::id())
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        abort_unless(
            $order->order_status === 'delivered'
                && $order->delivered_at
                && $order->delivered_at->diffInDays(now()) <= 7,
            403,
            'This order is not eligible for a return.'
        );

        $data = $request->validate([
            'reason_category' => 'required|in:defective,wrong_item,not_as_described,changed_mind,other',
            'reason_text' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:unopened,opened,damaged',
        ]);

        $orderItemIds = $order->items->pluck('id');

        DB::transaction(function () use ($order, $data, $orderItemIds) {
            $return = ReturnRequest::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'reason_category' => $data['reason_category'],
                'reason_text' => $data['reason_text'] ?? null,
                'status' => 'requested',
            ]);

            foreach ($data['items'] as $itemData) {
                if (! $orderItemIds->contains($itemData['order_item_id'])) {
                    continue;
                }

                $return->items()->create([
                    'order_item_id' => $itemData['order_item_id'],
                    'quantity' => $itemData['quantity'],
                    'condition' => $itemData['condition'],
                ]);
            }
        });

        return back()->with('success', 'Your return request has been submitted.');
    }
}
