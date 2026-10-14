<?php

declare(strict_types=1);

namespace App\Domain;

use App\Exception\TransicionNoPermitidaException;
use DateTimeImmutable;
use InvalidArgumentException;

final class Reserva
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombreHuesped,
        public readonly string $emailHuesped,
        public readonly string $nombreAlojamiento,
        public readonly DateTimeImmutable $fechaEntrada,
        public readonly DateTimeImmutable $fechaSalida,
        private EstadoReserva $estado,
        public readonly string $importe,
        public readonly ?string $notas,
        public readonly ?DateTimeImmutable $fechaCreacion,
    ) {
        if ($fechaSalida <= $fechaEntrada) {
            throw new InvalidArgumentException('La fecha de salida debe ser posterior a la de entrada.');
        }
    }

    /**
     * Crea una reserva nueva (aún sin guardar): siempre empieza en "pendiente".
     */
    public static function nueva(
        string $nombreHuesped,
        string $emailHuesped,
        string $nombreAlojamiento,
        DateTimeImmutable $fechaEntrada,
        DateTimeImmutable $fechaSalida,
        string $importe,
        ?string $notas,
    ): self {
        return new self(
            id: null,
            nombreHuesped: $nombreHuesped,
            emailHuesped: $emailHuesped,
            nombreAlojamiento: $nombreAlojamiento,
            fechaEntrada: $fechaEntrada,
            fechaSalida: $fechaSalida,
            estado: EstadoReserva::Pendiente,
            importe: $importe,
            notas: $notas,
            fechaCreacion: null,
        );
    }

    public function estado(): EstadoReserva
    {
        return $this->estado;
    }

    public function cambiarEstado(EstadoReserva $nuevo): void
    {
        if (!$this->estado->puedeCambiarA($nuevo)) {
            throw new TransicionNoPermitidaException($this->estado, $nuevo);
        }

        $this->estado = $nuevo;
    }

    public function noches(): int
    {
        return (int) $this->fechaEntrada->diff($this->fechaSalida)->days;
    }
}