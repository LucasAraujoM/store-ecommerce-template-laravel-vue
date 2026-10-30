<?php

namespace App\Enums;

enum ShippingMethod: string
{
    case HomeDelivery = 'home_delivery';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::HomeDelivery => 'Envío a domicilio',
            self::Pickup => 'Retiro en el local',
        };
    }
}
