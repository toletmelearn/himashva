<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderInvoiceController extends Controller
{
    public function __invoke(Order $order)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $order->load('items');

        $pdf = Pdf::loadView('pdf.invoice', ['order' => $order]);

        return $pdf->stream("invoice-{$order->order_number}.pdf");
    }
}
