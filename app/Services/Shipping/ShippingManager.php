<?php

namespace App\Services\Shipping;

use App\Models\ShippingProvider;
use App\Services\Shipping\Drivers\ManualDriver;
use App\Services\Shipping\Drivers\ShiprocketDriver;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ShippingManager
{
    /**
     * @var array<string, class-string<ShippingDriverInterface>>
     */
    protected array $drivers = [
        'shiprocket' => ShiprocketDriver::class,
        'manual' => ManualDriver::class,
    ];

    public function driver(string $name): ShippingDriverInterface
    {
        if (! isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Unsupported shipping driver [{$name}].");
        }

        return app($this->drivers[$name]);
    }

    public function activeProvider(): ?ShippingProvider
    {
        return ShippingProvider::active()->orderBy('display_order')->first();
    }

    public function allActiveProviders(): Collection
    {
        return ShippingProvider::active()->orderBy('display_order')->get();
    }
}
