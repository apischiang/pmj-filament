<?php

namespace App\Filament\Resources\Quotations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class QuotationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('General Information')
                            ->description('Basic details about the quotation')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Select::make('customer_uid')
                                    ->label('Customer')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')->required(),
                                        TextInput::make('company_name'),
                                        TextInput::make('npwp'),
                                        Textarea::make('address'),
                                    ])
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('quotation_number')
                                    ->label('Quotation No.')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('PMJ-XYZ-001')
                                    ->prefixIcon('heroicon-o-hashtag'),

                                DatePicker::make('date')
                                    ->label('Issue Date')
                                    ->default(now())
                                    ->required()
                                    ->prefixIcon('heroicon-o-calendar'),

                                Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'sent' => 'Sent',
                                        'accepted' => 'Accepted',
                                        'rejected' => 'Rejected',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-check-circle'),
                            ])
                            ->columns(2),

                        Section::make('Quotation Items')
                            ->description('Add products or services')
                            ->icon('heroicon-o-shopping-cart')
                            ->schema([
                                Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextInput::make('product_name')
                                                    ->required()
                                                    ->label('Product / Service')
                                                    ->columnSpan(2),

                                                TextInput::make('quantity')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total_price', $state * $get('unit_price')))
                                                    ->columnSpan(1),

                                                TextInput::make('unit_price')
                                                    ->numeric()
                                                    ->required()
                                                    ->prefix('Rp')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total_price', $state * $get('quantity')))
                                                    ->columnSpan(1),
                                            ]),
                                        
                                        Grid::make(1)
                                            ->schema([
                                                Textarea::make('description')
                                                    ->label('Description (Optional)')
                                                    ->rows(1)
                                                    ->columnSpanFull(),
                                                
                                                TextInput::make('total_price')
                                                    ->label('Subtotal')
                                                    ->numeric()
                                                    ->readOnly()
                                                    ->prefix('Rp')
                                                    ->dehydrated()
                                                    ->extraInputAttributes(['style' => 'text-align: right; font-weight: bold;']),
                                            ]),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? null)
                                    ->collapsible()
                                    ->cloneable()
                                    ->defaultItems(1)
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, callable $set) {
                                        $items = $get('items');
                                        $total = collect($items)->sum('total_price');
                                        $set('total_amount', $total);
                                    }),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Summary')
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                TextInput::make('total_amount')
                                    ->label('Total Amount')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->required()
                                    ->extraInputAttributes(['style' => 'font-size: 1.5rem; font-weight: bold; color: var(--primary-600);']),

                                Textarea::make('terms_and_conditions')
                                    ->label('Terms & Conditions')
                                    ->placeholder('Enter terms here...')
                                    ->rows(4),

                                TextInput::make('created_by')
                                    ->label('Created By')
                                    ->default(Auth::user()?->name)
                                    ->readOnly()
                                    ->prefixIcon('heroicon-o-user'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
