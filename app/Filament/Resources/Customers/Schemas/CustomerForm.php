<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Profile')
                    ->description('Manage customer identity and billing information.')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        TextInput::make('uid')
                            ->label('UID')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn ($record) => $record !== null)
                            ->prefixIcon('heroicon-o-key')
                            ->maxLength(20)
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Contact Person')
                            ->required()
                            ->maxLength(20)
                            ->prefixIcon('heroicon-o-user')
                            ->placeholder('John Doe'),

                        TextInput::make('company_name')
                            ->required()
                            ->label('Company Name')
                            ->maxLength(50)
                            ->prefixIcon('heroicon-o-building-office')
                            ->placeholder('PT. Example Indonesia'),

                        TextInput::make('npwp')
                            ->label('NPWP')
                            ->required()
                            ->mask('9999 9999 9999 9999')
                            ->stripCharacters(' ')
                            ->rule('digits:16')
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-o-identification')
                            ->placeholder('0000 0000 0000 0000'),

                        Textarea::make('address')
                            ->label('Billing Address')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Enter complete address...'),
                    ])
                    ->columns(2),
            ]);
    }
}
