<?php

namespace App\Filament\Resources\Quotations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuotationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quotation_number')
                    ->label('No. Quotation')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Issue Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('payment_terms')
                    ->label('Termin')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'net_30' => 'NET 30',
                        'net_60' => 'NET 60',
                        'net_14' => 'NET 14',
                        'net_7' => 'NET 7',
                        'cash' => 'Cash',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'net_7', 'net_14' => 'info',
                        'net_30', 'net_60' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
