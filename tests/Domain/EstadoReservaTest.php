<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\EstadoReserva;
use PHPUnit\Framework\TestCase;

final class EstadoReservaTest extends TestCase
{
    public function test_pendiente_puede_confirmarse_o_cancelarse(): void
    {
        $this->assertTrue(EstadoReserva::Pendiente->puedeCambiarA(EstadoReserva::Confirmada));
        $this->assertTrue(EstadoReserva::Pendiente->puedeCambiarA(EstadoReserva::Cancelada));
    }

    public function test_confirmada_solo_puede_cancelarse(): void
    {
        $this->assertTrue(EstadoReserva::Confirmada->puedeCambiarA(EstadoReserva::Cancelada));
        $this->assertFalse(EstadoReserva::Confirmada->puedeCambiarA(EstadoReserva::Pendiente));
    }

    public function test_cancelada_es_un_estado_final(): void
    {
        $this->assertSame([], EstadoReserva::Cancelada->transicionesPermitidas());
    }

    public function test_no_se_puede_cambiar_al_mismo_estado(): void
    {
        foreach (EstadoReserva::cases() as $estado) {
            $this->assertFalse($estado->puedeCambiarA($estado));
        }
    }

    public function test_un_valor_desconocido_no_es_un_estado_valido(): void
    {
        $this->assertNull(EstadoReserva::tryFrom('confirmado'));
    }
}