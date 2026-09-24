<?php

namespace App\Http\Controllers;

use App\Services\Shipping\DTOs\ShippingRateRequest;
use App\Services\Shipping\ShippingManager;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(protected ShippingManager $shipping) {}

    public function checkPincode(Request $request)
    {
        $data = $request->validate([
            'pincode' => 'required|digits:6',
            'weight' => 'nullable|integer',
        ]);

        $provider = $this->shipping->activeProvider();

        if (! $provider) {
            return response()->json(['serviceable' => false, 'message' => 'Shipping is not configured.'], 422);
        }

        $driver = $this->shipping->driver($provider->driver);
        $pincode = $driver->checkPincode($data['pincode'], $provider);

        $rate = $driver->calculateRate(
            new ShippingRateRequest(pincode: $data['pincode'], weightGrams: $data['weight'] ?? 500),
            $provider
        );

        return response()->json([
            'serviceable' => $pincode->available,
            'rate' => $rate->rate,
            'estimated_days' => $rate->estimatedDays,
            'message' => $rate->rate > 0 ? 'Delivery available. Shipping: ₹'.number_format($rate->rate, 2) : 'Free delivery available to this pincode!',
        ]);
    }
}
