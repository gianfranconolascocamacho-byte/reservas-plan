<?php

declare(strict_types=1);

namespace App\Domain;

enum EstadoReserva: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';

    /**
     * Estados a los que se puede pasar desde el estado actual.
     *
     * @return list<self>
     */
    public function transicionesPermitidas(): array
    {
        return match ($this) {
            self::Pendiente => [self::Confirmada, self::Cancelada],
            self::Confirmada => [self::Cancelada],
            self::Cancelada => [],
        };
    }

    public function puedeCambiarA(self $nuevo): bool
    {
        return in_array($nuevo, $this->transicionesPermitidas(), true);
    }
}