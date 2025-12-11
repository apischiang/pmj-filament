<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    // Customization here
    protected static ?string $title = 'Main Dashboard';

    // Sidebar Navigation
    protected static ?string $navigationLabel = 'Home'; // Label di sidebar
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home'; // Icon sidebar
    protected static ?int $navigationSort = -2; // Urutan (paling atas)
    // protected static ?string $navigationGroup = 'Menu Utama'; // Jika ingin dikelompokkan
}
