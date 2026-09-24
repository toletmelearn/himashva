<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;

class RecentOrders extends Widget
{
    protected static string $view = 'filament.widgets.recent-orders';

    protected static ?string $heading = 'Recent Orders';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    /**
     * Filament badge colors keyed by order_status value.
     *
     * @var array<string, string>
     */
    protected array $statusColors = [
        'pending' => 'warning',
        'confirmed' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'out_for_delivery' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger',
        'returned' => 'gray',
        'refunded' => 'gray',
    ];

    /**
     * Icons keyed by payment_method value.
     *
     * @var array<string, string>
     */
    protected array $paymentIcons = [
        'cod' => 'heroicon-o-banknotes',
        'razorpay' => 'heroicon-o-credit-card',
        'card' => 'heroicon-o-credit-card',
        'upi' => 'heroicon-o-device-phone-mobile',
        'wallet' => 'heroicon-o-wallet',
    ];

    /**
     * @return array{orders: array<int, array<string, mixed>>}
     */
    protected function getViewData(): array
    {
        $orders = Order::query()->latest()->limit(10)->get();

        return [
            'orders' => $orders->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => $order->name,
                'total' => '₹'.number_format((float) $order->total, 2),
                'status_label' => ucfirst(str_replace('_', ' ', $order->order_status)),
                'status_color' => $this->statusColors[$order->order_status] ?? 'gray',
                'time_ago' => $order->created_at->diffForHumans(),
                'payment_icon' => $this->paymentIcons[$order->payment_method] ?? 'heroicon-o-currency-rupee',
                'url' => "/admin/orders/{$order->id}/edit",
            ])->toArray(),
        ];
    }
}
