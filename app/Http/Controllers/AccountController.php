<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $recentOrders = $user->orders()->latest()->limit(5)->get();
        $addressCount = $user->addresses()->count();

        return view('account.dashboard', compact('user', 'recentOrders', 'addressCount'));
    }

    public function profile()
    {
        return view('account.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function orders()
    {
        $orders = Auth::user()->orders()->latest()->paginate(10);

        return view('account.orders', compact('orders'));
    }

    public function orderDetail(string $orderNumber)
    {
        $order = Order::with(['items', 'statusHistory', 'returns'])
            ->where('user_id', Auth::id())
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $canRequestReturn = $order->order_status === 'delivered'
            && $order->delivered_at
            && $order->delivered_at->diffInDays(now()) <= 7
            && $order->returns->isEmpty();

        return view('account.order-detail', compact('order', 'canRequestReturn'));
    }

    public function reviews()
    {
        $reviews = Auth::user()->reviews()->with('product')->latest()->get();

        return view('account.reviews', compact('reviews'));
    }
}
