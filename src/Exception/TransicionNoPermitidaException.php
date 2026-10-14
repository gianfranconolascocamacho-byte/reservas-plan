<?php

declare(strict_types=1);

namespace App\Exception;

use App\Domain\EstadoReserva;
use RuntimeException;

final class TransicionNoPermitidaException extends RuntimeException
{
    public function __construct(EstadoReserva $actual, EstadoReserva $nuevo)
    {
        parent::__construct(sprintf(
            'No se puede pasar una reserva de "%s" a "%s".',
            $actual->value,
            $nuevo->value,
        ));
    }
}