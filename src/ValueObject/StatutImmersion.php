<?php

declare(strict_types=1);

namespace App\ValueObject;

enum StatutImmersion: string
{
    case Proposee = 'proposee';
    case Confirmee = 'confirmee';
    case Realisee = 'realisee';
    case Annulee = 'annulee';

    public function label(): string
    {
        return match ($this) {
            self::Proposee => 'Proposée',
            self::Confirmee => 'Confirmée',
            self::Realisee => 'Réalisée',
            self::Annulee => 'Annulée',
        };
    }

    /**
     * Classe de couleur Bootstrap (badge bg-*).
     */
    public function couleur(): string
    {
        return match ($this) {
            self::Proposee => 'secondary',
            self::Confirmee => 'warning',
            self::Realisee => 'success',
            self::Annulee => 'danger',
        };
    }

    /**
     * @return array<string, string> libellé => valeur
     */
    public static function choiceOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->label()] = $case->value;
        }

        return $options;
    }
}
