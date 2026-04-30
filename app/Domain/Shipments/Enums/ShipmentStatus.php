<?php

namespace App\Domain\Shipments\Enums;

enum ShipmentStatus: string
{
    case Pending   = 'pending';
    case Preparing = 'preparing';
    case Shipped   = 'shipped';
    case Delivered = 'delivered';
    case Returned  = 'returned';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pendiente',
            self::Preparing => 'Preparando',
            self::Shipped   => 'En camino',
            self::Delivered => 'Entregado',
            self::Returned  => 'Devuelto',
        };
    }
}
