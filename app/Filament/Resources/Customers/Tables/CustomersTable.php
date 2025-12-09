<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Avatar')
                    ->formatStateUsing(fn (string $state) => strtoupper(substr($state, 0, 1)))
                    ->color('primary')
                    ->extraAttributes(['class' => 'w-10 text-center font-bold bg-gray-100 rounded-full'])
                    ->alignCenter()
                    ->width(50), // Kolom avatar inisial
                
                TextColumn::make('details') // Kolom dummy untuk grouping nama & perusahaan
                    ->label('Detail Pelanggan')
                    ->default(fn ($record) => $record->name)
                    ->description(fn ($record) => $record->company_name)
                    ->searchable(['name', 'company_name'])
                    ->sortable(['name']),
                
                TextColumn::make('uid')
                    ->label('UID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('npwp')
                    ->label('NPWP')
                    ->formatStateUsing(function (string $state): string {
                        // Assuming state is 16 digits: 1234567890123456
                        // Format to: 1234 5678 9012 3456
                        return implode(' ', str_split($state, 4));
                    })
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
