<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uid')
                    ->label('UID')
                    ->disabled()
                    ->dehydrated()
                    ->visible(fn ($record) => $record !== null)
                    ->maxLength(20),

                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(20),

                TextInput::make('company_name')
                    ->required()
                    ->label('Nama Perusahaan')
                    ->maxLength(50),

                Textarea::make('address')
                    ->label('Alamat')
                    ->required()
                    ->maxLength(100)
                    ->columnSpanFull(),

                TextInput::make('npwp')
                    ->label('NPWP')
                    ->required()
                    ->mask('9999 9999 9999 9999')
                    ->stripCharacters(' ')
                    // ->length(16) // Removed strict length check on frontend to allow mask typing, validation happens after stripCharacters
                    ->rule('digits:16') // Use rule instead of length to validate the stripped value
                    ->unique(ignoreRecord: true),
            ]);
    }
}
