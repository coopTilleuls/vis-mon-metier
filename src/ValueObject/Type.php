<?php

declare(strict_types=1);

namespace App\ValueObject;

final class Type
{
    public const EMPLOYEUR = 'employeur';
    public const PARTICIPANT = 'participant';

    /**
     * @return array<mixed>
     */
    public static function choiceOptions(): array
    {
        return [
            'Volontaire' => self::PARTICIPANT,
            'Professionnel·le' => self::EMPLOYEUR,
        ];
    }
}
