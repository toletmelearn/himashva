<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackController extends Controller
{
    public function form()
    {
        return view('track-order');
    }

    public function track(Request $request)
    {
        $data = $request->validate([
            'order_number' => 'required|string',
            'email' => 'required|email',
        ]);

        $order = Order::with(['items', 'statusHistory'])
            ->where('order_number', $data['order_number'])
            ->where('email', $data['email'])
            ->first();

        if (! $order) {
            return back()->with('error', 'No order found with those details.')->withInput();
        }

        return view('track-order', compact('order'));
    }
}
