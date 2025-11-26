<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RoleEnum: string implements HasLabel, HasColor, HasIcon
{
    case REQUESTER = 'requester';
    case ADMIN = 'admin';
    case APPROVER = 'approver';
    case SECTION_HEAD = 'section_head';
    case DIVISION_HEAD = 'division_head';
    case FINANCE_ADMIN = 'finance_admin';
    case TREASURER = 'treasurer';
    case SUPER_ADMIN = 'super_admin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::REQUESTER     => 'Requester',
            self::ADMIN         => 'Admin',
            self::APPROVER      => 'Approver',
            self::SECTION_HEAD  => 'Section Head',
            self::DIVISION_HEAD => 'Division Head',
            self::FINANCE_ADMIN => 'Finance Admin',
            self::TREASURER     => 'Treasurer',
            self::SUPER_ADMIN   => 'Super Admin',
        };
    }

    /**
     * Return Filament color keywords:
     * allowed examples: 'gray','primary','secondary','success','danger','warning','info'
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::REQUESTER     => 'gray',
            self::ADMIN         => 'primary',
            self::APPROVER      => 'warning',
            self::SECTION_HEAD  => 'secondary',
            self::DIVISION_HEAD => 'info',
            self::FINANCE_ADMIN, self::TREASURER => 'success',
            self::SUPER_ADMIN   => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::REQUESTER     => 'heroicon-o-user',
            self::ADMIN         => 'heroicon-o-shield-check',
            self::APPROVER      => 'heroicon-o-check-circle',
            self::SECTION_HEAD  => 'heroicon-o-briefcase',
            self::DIVISION_HEAD => 'heroicon-o-building-office',
            self::FINANCE_ADMIN => 'heroicon-o-calculator',
            self::TREASURER     => 'heroicon-o-banknotes',
            self::SUPER_ADMIN   => 'heroicon-o-cog-6-tooth',
        };
    }

    /**
     * Helper: mapping value => color (usable for BadgeColumn::colors(...))
     * Example: BadgeColumn::make('role')->colors(RoleEnum::colorMap())
     */
    public static function colorMap(): array
    {
        return array_map(
            fn(RoleEnum $e) => $e->getColor(),
            self::cases()
        );
    }

    /**
     * Helper: mapping value => icon (usable for BadgeColumn::icons(...) or when rendering)
     * Example: BadgeColumn::make('role')->icons(RoleEnum::iconMap())
     */
    public static function iconMap(): array
    {
        return array_combine(
            array_map(fn(RoleEnum $e) => $e->value, self::cases()),
            array_map(fn(RoleEnum $e) => $e->getIcon(), self::cases())
        );
    }

    /**
     * Optional: mapping value => label (useful for enum display)
     */
    public static function labelMap(): array
    {
        return array_combine(
            array_map(fn(RoleEnum $e) => $e->value, self::cases()),
            array_map(fn(RoleEnum $e) => $e->getLabel(), self::cases())
        );
    }
}
