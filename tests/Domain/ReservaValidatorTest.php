<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\EstadoReserva;
use App\Domain\ReservaValidator;
use App\Exception\ValidationException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ReservaValidatorTest extends TestCase
{
    private ReservaValidator $validator;

    protected function setUp(): void
    {
        // Fecha fija: los tests no dependen del día en que se ejecutan
        $this->validator = new ReservaValidator(new DateTimeImmutable('2026-10-14'));
    }

    public function test_con_datos_validos_crea_una_reserva_pendiente_y_limpia(): void
    {
        $reserva = $this->validator->validarNuevaReserva($this->datosValidos([
            'nombre_huesped' => '  Lucía Fernández  ',
            'importe' => 220.5,
        ]));

        $this->assertSame('Lucía Fernández', $reserva->nombreHuesped);
        $this->assertSame('220.50', $reserva->importe);
        $this->assertSame(EstadoReserva::Pendiente, $reserva->estado());
    }

    public function test_devuelve_todos_los_errores_a_la_vez(): void
    {
        $errores = $this->erroresDe([]);

        $this->assertSame(
            ['nombre_huesped', 'email_huesped', 'nombre_alojamiento', 'fecha_entrada', 'fecha_salida', 'importe'],
            array_keys($errores),
        );
    }

    public function test_rechaza_un_email_invalido(): void
    {
        $errores = $this->erroresDe($this->datosValidos(['email_huesped' => 'lucia@']));

        $this->assertArrayHasKey('email_huesped', $errores);
    }

    public function test_rechaza_una_fecha_que_no_existe(): void
    {
        $errores = $this->erroresDe($this->datosValidos(['fecha_salida' => '2027-02-31']));

        $this->assertArrayHasKey('fecha_salida', $errores);
    }

    public function test_rechaza_salida_igual_o_anterior_a_entrada(): void
    {
        $errores = $this->erroresDe($this->datosValidos([
            'fecha_entrada' => '2026-11-10',
            'fecha_salida' => '2026-11-10',
        ]));

        $this->assertArrayHasKey('fecha_salida', $errores);
    }

    public function test_rechaza_una_entrada_en_el_pasado(): void
    {
        $errores = $this->erroresDe($this->datosValidos(['fecha_entrada' => '2026-10-13']));

        $this->assertArrayHasKey('fecha_entrada', $errores);
    }

    public function test_rechaza_tipos_inesperados(): void
    {
        $errores = $this->erroresDe($this->datosValidos([
            'nombre_huesped' => ['no', 'soy', 'texto'],
            'importe' => true,
        ]));

        $this->assertArrayHasKey('nombre_huesped', $errores);
        $this->assertArrayHasKey('importe', $errores);
    }

    public function test_ignora_el_estado_enviado_por_el_cliente(): void
    {
        $reserva = $this->validator->validarNuevaReserva($this->datosValidos(['estado' => 'confirmada']));

        $this->assertSame(EstadoReserva::Pendiente, $reserva->estado());
    }

    public function test_rechaza_un_estado_desconocido(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validarEstado('confirmado');
    }

    /** @return array<string, mixed> */
    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'nombre_huesped' => 'Lucía Fernández',
            'email_huesped' => 'lucia@example.com',
            'nombre_alojamiento' => 'Hotel Mirador del Mar',
            'fecha_entrada' => '2026-11-06',
            'fecha_salida' => '2026-11-08',
            'importe' => '220.00',
            'notas' => null,
        ], $cambios);
    }

    /** @return array<string, string> */
    private function erroresDe(array $datos): array
    {
        try {
            $this->validator->validarNuevaReserva($datos);
        } catch (ValidationException $e) {
            return $e->errores();
        }

        $this->fail('Se esperaba una ValidationException.');
    }
}