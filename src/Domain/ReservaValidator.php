<?php

declare(strict_types=1);

namespace App\Domain;

use App\Exception\ValidationException;
use DateTimeImmutable;

final class ReservaValidator
{
    private const MAX_NOMBRE = 150;
    private const MAX_EMAIL = 254;
    private const MAX_NOTAS = 1000;
    private const PATRON_IMPORTE = '/^\d{1,8}(\.\d{1,2})?$/';

    private readonly DateTimeImmutable $hoy;

    public function __construct(?DateTimeImmutable $hoy = null)
    {
        $this->hoy = ($hoy ?? new DateTimeImmutable())->setTime(0, 0);
    }

    /**
     * Convierte datos externos (no confiables) en una Reserva válida.
     *
     * @param array<mixed> $datos
     * @throws ValidationException con todos los errores encontrados
     */
    public function validarNuevaReserva(array $datos): Reserva
    {
        $errores = [];

        $nombre      = $this->texto($datos, 'nombre_huesped', self::MAX_NOMBRE, $errores);
        $email       = $this->email($datos, $errores);
        $alojamiento = $this->texto($datos, 'nombre_alojamiento', self::MAX_NOMBRE, $errores);
        $entrada     = $this->fecha($datos, 'fecha_entrada', $errores);
        $salida      = $this->fecha($datos, 'fecha_salida', $errores);
        $importe     = $this->importe($datos, $errores);
        $notas       = $this->notas($datos, $errores);

        if ($entrada !== null && $entrada < $this->hoy) {
            $errores['fecha_entrada'] = 'La fecha de entrada no puede estar en el pasado.';
        }

        if ($entrada !== null && $salida !== null && $salida <= $entrada) {
            $errores['fecha_salida'] = 'La fecha de salida debe ser posterior a la de entrada.';
        }

        if ($errores !== []) {
            throw new ValidationException($errores);
        }

        return Reserva::nueva($nombre, $email, $alojamiento, $entrada, $salida, $importe, $notas);
    }

    /**
     * @throws ValidationException si el valor no es un estado conocido
     */
    public function validarEstado(mixed $valor): EstadoReserva
    {
        $estado = is_string($valor) ? EstadoReserva::tryFrom($valor) : null;

        if ($estado === null) {
            $permitidos = implode(', ', array_map(fn (EstadoReserva $e) => $e->value, EstadoReserva::cases()));
            throw new ValidationException(['estado' => "Estado no válido. Valores permitidos: {$permitidos}."]);
        }

        return $estado;
    }

    /** @param array<string, string> $errores */
    private function texto(array $datos, string $campo, int $max, array &$errores): ?string
    {
        $valor = $datos[$campo] ?? null;

        if (!is_string($valor) || trim($valor) === '') {
            $errores[$campo] = 'Este campo es obligatorio.';
            return null;
        }

        $valor = trim($valor);

        if (mb_strlen($valor) > $max) {
            $errores[$campo] = "No puede superar los {$max} caracteres.";
            return null;
        }

        return $valor;
    }

    /** @param array<string, string> $errores */
    private function email(array $datos, array &$errores): ?string
    {
        $email = $this->texto($datos, 'email_huesped', self::MAX_EMAIL, $errores);

        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errores['email_huesped'] = 'El email no tiene un formato válido.';
            return null;
        }

        return $email;
    }

    /** @param array<string, string> $errores */
    private function fecha(array $datos, string $campo, array &$errores): ?DateTimeImmutable
    {
        $valor = $datos[$campo] ?? null;

        if (!is_string($valor) || $valor === '') {
            $errores[$campo] = 'Este campo es obligatorio (formato AAAA-MM-DD).';
            return null;
        }

        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

        if ($fecha === false || $fecha->format('Y-m-d') !== $valor) {
            $errores[$campo] = 'La fecha no es válida (formato AAAA-MM-DD).';
            return null;
        }

        return $fecha;
    }

    /** @param array<string, string> $errores */
    private function importe(array $datos, array &$errores): ?string
    {
        $valor = $datos['importe'] ?? null;

        if (is_int($valor) || is_float($valor)) {
            $valor = (string) $valor;
        }

        if (!is_string($valor) || preg_match(self::PATRON_IMPORTE, $valor) !== 1) {
            $errores['importe'] = 'El importe debe ser un número mayor o igual que 0, con hasta 2 decimales.';
            return null;
        }

        [$enteros, $decimales] = array_pad(explode('.', $valor, 2), 2, '');

        return (int) $enteros . '.' . str_pad($decimales, 2, '0');
    }

    /** @param array<string, string> $errores */
    private function notas(array $datos, array &$errores): ?string
    {
        $valor = $datos['notas'] ?? null;

        if ($valor === null || (is_string($valor) && trim($valor) === '')) {
            return null;
        }

        if (!is_string($valor)) {
            $errores['notas'] = 'Las notas deben ser texto.';
            return null;
        }

        $valor = trim($valor);

        if (mb_strlen($valor) > self::MAX_NOTAS) {
            $errores['notas'] = 'Las notas no pueden superar los ' . self::MAX_NOTAS . ' caracteres.';
            return null;
        }

        return $valor;
    }
}