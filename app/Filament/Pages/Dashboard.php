<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\UnitEnum|null $navigationGroup = 'General';

    protected static ?int $navigationSort = -2;

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
