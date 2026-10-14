<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\EstadoReserva;
use App\Domain\Reserva;
use App\Exception\TransicionNoPermitidaException;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ReservaTest extends TestCase
{
    public function test_una_reserva_nueva_empieza_pendiente_y_sin_id(): void
    {
        $reserva = $this->crearReserva();

        $this->assertNull($reserva->id);
        $this->assertSame(EstadoReserva::Pendiente, $reserva->estado());
    }

    public function test_una_reserva_pendiente_se_puede_confirmar(): void
    {
        $reserva = $this->crearReserva();

        $reserva->cambiarEstado(EstadoReserva::Confirmada);

        $this->assertSame(EstadoReserva::Confirmada, $reserva->estado());
    }

    public function test_una_reserva_cancelada_no_se_puede_confirmar(): void
    {
        $reserva = $this->crearReserva();
        $reserva->cambiarEstado(EstadoReserva::Cancelada);

        $this->expectException(TransicionNoPermitidaException::class);

        $reserva->cambiarEstado(EstadoReserva::Confirmada);
    }

    public function test_no_se_puede_crear_con_salida_anterior_a_entrada(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->crearReserva(entrada: '2026-11-10', salida: '2026-11-05');
    }

    public function test_calcula_las_noches_de_estancia(): void
    {
        $reserva = $this->crearReserva(entrada: '2026-12-27', salida: '2027-01-02');

        $this->assertSame(6, $reserva->noches());
    }

    private function crearReserva(string $entrada = '2026-11-06', string $salida = '2026-11-08'): Reserva
    {
        return Reserva::nueva(
            nombreHuesped: 'Lucía Fernández',
            emailHuesped: 'lucia@example.com',
            nombreAlojamiento: 'Hotel Mirador del Mar',
            fechaEntrada: new DateTimeImmutable($entrada),
            fechaSalida: new DateTimeImmutable($salida),
            importe: '220.00',
            notas: null,
        );
    }
}