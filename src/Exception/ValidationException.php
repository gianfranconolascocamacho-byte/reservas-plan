<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    /**
     * @param array<string, string> $errores  campo => mensaje
     */
    public function __construct(private readonly array $errores)
    {
        parent::__construct('Los datos enviados no son válidos.');
    }

    /**
     * @return array<string, string>
     */
    public function errores(): array
    {
        return $this->errores;
    }
}