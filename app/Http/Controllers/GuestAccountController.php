<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class GuestAccountController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'order_number' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $order = Order::whereNull('user_id')
            ->where('order_number', $request->string('order_number'))
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'We could not find that order, or it is already linked to an account.',
            ]);
        }

        if (User::where('email', $order->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'An account with this email already exists. Please log in to view your orders.',
            ]);
        }

        $user = User::create([
            'name' => $order->name,
            'email' => $order->email,
            'password' => Hash::make($request->string('password')),
        ]);

        $order->update(['user_id' => $user->id]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json(['success' => true]);
    }
}
