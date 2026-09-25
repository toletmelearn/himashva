<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // The session claim (written when the order was placed, see CheckoutController::placeOrder
        // and PaymentController::verifyPayment) proves this browser is the one that placed this
        // specific order. Without it, order_number alone is guessable/enumerable and anyone could
        // otherwise claim someone else's guest order.
        if (! $order || (int) session('claimable_order_id') !== $order->id) {
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

        try {
            $user = DB::transaction(function () use ($order, $request) {
                $locked = Order::whereKey($order->id)->whereNull('user_id')->lockForUpdate()->first();

                if (! $locked) {
                    throw new \RuntimeException('order_already_claimed');
                }

                $user = User::create([
                    'name' => $locked->name,
                    'email' => $locked->email,
                    'password' => Hash::make($request->string('password')),
                ]);

                $locked->update(['user_id' => $user->id]);

                return $user;
            });
        } catch (UniqueConstraintViolationException) {
            return response()->json([
                'success' => false,
                'message' => 'An account with this email already exists. Please log in to view your orders.',
            ]);
        } catch (\RuntimeException) {
            return response()->json([
                'success' => false,
                'message' => 'We could not find that order, or it is already linked to an account.',
            ]);
        }

        session()->forget('claimable_order_id');

        event(new Registered($user));

        Auth::login($user);

        return response()->json(['success' => true]);
    }
}
