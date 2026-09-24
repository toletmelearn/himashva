<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
    public function __construct(protected array $orderIds = []) {}

    public function collection()
    {
        $query = Order::query()->with('user');

        if (! empty($this->orderIds)) {
            $query->whereIn('id', $this->orderIds);
        }

        return $query->get()->map(fn (Order $order) => [
            $order->order_number,
            $order->name,
            $order->email,
            $order->phone,
            $order->total,
            $order->payment_method,
            $order->payment_status,
            $order->order_status,
            $order->created_at->format('Y-m-d H:i'),
        ]);
    }

    public function headings(): array
    {
        return ['Order #', 'Name', 'Email', 'Phone', 'Total', 'Payment Method', 'Payment Status', 'Order Status', 'Date'];
    }
}
