<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class UserGuide extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Panduan Pengguna';

    protected static ?string $title = 'Panduan Penggunaan Sistem';

    protected static ?int $navigationSort = 99;

    protected static string|null|\UnitEnum $navigationGroup = null;

    protected string $view = 'filament.pages.user-guide';
}
