<?php

namespace App\Filament\Widgets;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\Widget;

class QuickAccessWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-access';

    protected static ?int $sort = -9;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{label: string, icon: string, url: string, badge: string, from: string, to: string}>
     */
    protected function getViewData(): array
    {
        return [
            'cards' => [
                [
                    'label' => 'Products',
                    'icon' => 'heroicon-o-cube',
                    'url' => '/admin/products',
                    'badge' => (string) Product::count(),
                    'from' => '#667eea',
                    'to' => '#764ba2',
                ],
                [
                    'label' => 'Orders',
                    'icon' => 'heroicon-o-shopping-bag',
                    'url' => '/admin/orders',
                    'badge' => (string) Order::whereDate('created_at', today())->count(),
                    'from' => '#11998e',
                    'to' => '#38ef7d',
                ],
                [
                    'label' => 'Customers',
                    'icon' => 'heroicon-o-user-group',
                    'url' => '/admin/users',
                    'badge' => (string) User::where('is_admin', false)->count(),
                    'from' => '#00b4db',
                    'to' => '#0083b0',
                ],
                [
                    'label' => 'Revenue',
                    'icon' => 'heroicon-o-banknotes',
                    'url' => '/admin/orders',
                    'badge' => '₹'.number_format((float) Order::whereDate('created_at', today())->sum('total'), 0),
                    'from' => '#f2994a',
                    'to' => '#f2c94c',
                ],
                [
                    'label' => 'Returns',
                    'icon' => 'heroicon-o-arrow-uturn-left',
                    'url' => '/admin/returns',
                    'badge' => (string) ReturnRequest::where('status', 'requested')->count(),
                    'from' => '#eb3349',
                    'to' => '#f45c43',
                ],
                [
                    'label' => 'Inventory',
                    'icon' => 'heroicon-o-archive-box',
                    'url' => '/admin/inventory-movements',
                    'badge' => (string) Product::whereColumn('stock', '<=', 'low_stock_threshold')->count(),
                    'from' => '#8e2de2',
                    'to' => '#4a00e0',
                ],
                [
                    'label' => 'Coupons',
                    'icon' => 'heroicon-o-ticket',
                    'url' => '/admin/coupons',
                    'badge' => (string) Coupon::where('is_active', true)->count(),
                    'from' => '#11998e',
                    'to' => '#38ef7d',
                ],
                [
                    'label' => 'Reviews',
                    'icon' => 'heroicon-o-star',
                    'url' => '/admin/reviews',
                    'badge' => (string) Review::where('is_approved', false)->count(),
                    'from' => '#ee0979',
                    'to' => '#ff6a00',
                ],
            ],
        ];
    }
}
