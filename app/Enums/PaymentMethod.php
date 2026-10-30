<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MercadoPago = 'mercadopago';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::MercadoPago => 'MercadoPago',
            self::Transfer => 'Transferencia bancaria',
        };
    }
}
