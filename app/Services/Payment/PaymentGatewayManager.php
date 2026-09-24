<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use App\Services\Payment\Drivers\CodDriver;
use App\Services\Payment\Drivers\RazorpayDriver;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /**
     * @var array<string, class-string<PaymentGatewayInterface>>
     */
    protected array $drivers = [
        'razorpay' => RazorpayDriver::class,
        'cod' => CodDriver::class,
    ];

    public function driver(string $name): PaymentGatewayInterface
    {
        if (! isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Unsupported payment driver [{$name}].");
        }

        return app($this->drivers[$name]);
    }

    public function activeGateways(): Collection
    {
        return PaymentGateway::active()->orderBy('display_order')->get();
    }

    public function getGateway(string $name): ?PaymentGateway
    {
        return PaymentGateway::where('name', $name)->first();
    }
}
