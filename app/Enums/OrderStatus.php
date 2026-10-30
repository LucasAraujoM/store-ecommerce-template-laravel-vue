<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente de pago',
            self::Paid => 'Pagado',
            self::Processing => 'En preparación',
            self::Shipped => 'Enviado',
            self::Delivered => 'Entregado',
            self::Canceled => 'Cancelado',
        };
    }

    public function isCanceled(): bool
    {
        return $this === self::Canceled;
    }

    /**
     * Estados que puede elegir el administrador al actualizar un pedido.
     *
     * @return array<int, self>
     */
    public static function manageable(): array
    {
        return [
            self::Pending,
            self::Paid,
            self::Processing,
            self::Shipped,
            self::Delivered,
            self::Canceled,
        ];
    }
}
