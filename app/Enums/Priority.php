<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Priority: string implements HasLabel, HasColor
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::LOW => 'secondary',
            self::MEDIUM => 'primary',
            self::HIGH => 'warning',
            self::URGENT => 'danger',
        };
    }

    /**
     * Helper for Select options
     */
    public static function options(): array
    {
        return [
            self::LOW->value => self::LOW->getLabel(),
            self::MEDIUM->value => self::MEDIUM->getLabel(),
            self::HIGH->value => self::HIGH->getLabel(),
            self::URGENT->value => self::URGENT->getLabel(),
        ];
    }
}
