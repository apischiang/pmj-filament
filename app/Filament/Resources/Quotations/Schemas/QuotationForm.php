<?php

namespace App\Filament\Resources\Quotations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class QuotationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('General Information')
                            ->description('Basic details about the quotation')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Select::make('customer_uid')
                                    ->label('Customer')
                                    ->relationship('customer')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => new HtmlString(
                                        "<div class='flex flex-col gap-1'>
                                            <span class='font-bold'>{$record->name}</span><br>
                                            <span class='text-xs text-gray-500'>{$record->company_name}</span>
                                        </div>"
                                    ))
                                    ->allowHtml()
                                    ->searchable(['name', 'company_name'])
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
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $paymentTerms = $get('payment_terms');
                                        if ($paymentTerms && $state) {
                                            $issueDate = \Carbon\Carbon::parse($state);
                                            $dueDate = match ($paymentTerms) {
                                                'net_30' => $issueDate->copy()->addDays(30),
                                                'net_60' => $issueDate->copy()->addDays(60),
                                                'net_14' => $issueDate->copy()->addDays(14),
                                                'net_7' => $issueDate->copy()->addDays(7),
                                                'cash' => $issueDate->copy(),
                                                default => null,
                                            };
                                            
                                            if ($dueDate) {
                                                $set('due_date', $dueDate->format('Y-m-d'));
                                            }
                                        }
                                    })
                                    ->prefixIcon('heroicon-o-calendar'),

                                Select::make('payment_terms')
                                    ->label('Termin Pembayaran')
                                    ->options([
                                        'cash' => 'Cash / Tunai',
                                        'net_7' => 'NET 7 Days',
                                        'net_14' => 'NET 14 Days',
                                        'net_30' => 'NET 30 Days',
                                        'net_60' => 'NET 60 Days',
                                        'custom' => 'Custom',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $issueDateStr = $get('date');
                                        $issueDate = $issueDateStr ? \Carbon\Carbon::parse($issueDateStr) : now();
                                        
                                        $dueDate = match ($state) {
                                            'net_30' => $issueDate->copy()->addDays(30),
                                            'net_60' => $issueDate->copy()->addDays(60),
                                            'net_14' => $issueDate->copy()->addDays(14),
                                            'net_7' => $issueDate->copy()->addDays(7),
                                            'cash' => $issueDate->copy(),
                                            default => null,
                                        };

                                        if ($dueDate) {
                                            $set('due_date', $dueDate->format('Y-m-d'));
                                        }
                                    })
                                    ->prefixIcon('heroicon-o-clock'),

                                DatePicker::make('due_date')
                                    ->label('Jatuh Tempo')
                                    ->required()
                                    ->prefixIcon('heroicon-o-calendar-days'),

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
                                        Grid::make(5)
                                            ->schema([
                                                TextInput::make('product_name')
                                                    ->required()
                                                    ->label('Product / Service')
                                                    ->columnSpan(5),

                                                TextInput::make('quantity')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        $unitPrice = $get('unit_price') ?? 0;
                                                        $discount = $get('discount') ?? 0;
                                                        $discountAmount = ($unitPrice * $discount) / 100;
                                                        $priceAfterDiscount = $unitPrice - $discountAmount;
                                                        $totalPrice = $state * $priceAfterDiscount;
                                                        
                                                        $set('price_after_discount', $priceAfterDiscount);
                                                        $set('total_price', $totalPrice);

                                                        // Update global totals
                                                        $items = $get('../../items') ?? [];
                                                        $subtotal = collect($items)->sum(function ($item) {
                                                            $q = $item['quantity'] ?? 1;
                                                            $p = $item['unit_price'] ?? 0;
                                                            $d = $item['discount'] ?? 0;
                                                            return $q * ($p - ($p * $d / 100));
                                                        });
                                                        
                                                        $hasPpn = $get('../../has_ppn');
                                                        $ppnAmount = $hasPpn ? ($subtotal * 0.11) : 0;
                                                        
                                                        $set('../../subtotal_amount', $subtotal);
                                                        $set('../../ppn_amount', $ppnAmount);
                                                        $set('../../total_amount', $subtotal + $ppnAmount);
                                                    })
                                                    ->columnSpan(1),

                                                TextInput::make('uom')
                                                    ->label('Satuan')
                                                    ->placeholder('Pcs/Kg/Set')
                                                    ->required()
                                                    ->columnSpan(1),

                                                TextInput::make('unit_price')
                                                    ->label('Unit Price')
                                                    ->numeric()
                                                    ->required()
                                                    ->prefix('Rp')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        $quantity = $get('quantity') ?? 1;
                                                        $discount = $get('discount') ?? 0;
                                                        $discountAmount = ($state * $discount) / 100;
                                                        $priceAfterDiscount = $state - $discountAmount;
                                                        $totalPrice = $quantity * $priceAfterDiscount;
                                                        
                                                        $set('price_after_discount', $priceAfterDiscount);
                                                        $set('total_price', $totalPrice);

                                                        // Update global totals
                                                        $items = $get('../../items') ?? [];
                                                        $subtotal = collect($items)->sum(function ($item) {
                                                            $q = $item['quantity'] ?? 1;
                                                            $p = $item['unit_price'] ?? 0;
                                                            $d = $item['discount'] ?? 0;
                                                            return $q * ($p - ($p * $d / 100));
                                                        });
                                                        
                                                        $hasPpn = $get('../../has_ppn');
                                                        $ppnAmount = $hasPpn ? ($subtotal * 0.11) : 0;
                                                        
                                                        $set('../../subtotal_amount', $subtotal);
                                                        $set('../../ppn_amount', $ppnAmount);
                                                        $set('../../total_amount', $subtotal + $ppnAmount);
                                                    })
                                                    ->columnSpan(2),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('discount')
                                                    ->label('Discount (%)')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->maxValue(100)
                                                    ->suffix('%')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        $quantity = $get('quantity') ?? 1;
                                                        $unitPrice = $get('unit_price') ?? 0;
                                                        $discountAmount = ($unitPrice * $state) / 100;
                                                        $priceAfterDiscount = $unitPrice - $discountAmount;
                                                        $totalPrice = $quantity * $priceAfterDiscount;
                                                        
                                                        $set('price_after_discount', $priceAfterDiscount);
                                                        $set('total_price', $totalPrice);

                                                        // Update global totals
                                                        $items = $get('../../items') ?? [];
                                                        $subtotal = collect($items)->sum(function ($item) {
                                                            $q = $item['quantity'] ?? 1;
                                                            $p = $item['unit_price'] ?? 0;
                                                            $d = $item['discount'] ?? 0;
                                                            return $q * ($p - ($p * $d / 100));
                                                        });
                                                        
                                                        $hasPpn = $get('../../has_ppn');
                                                        $ppnAmount = $hasPpn ? ($subtotal * 0.11) : 0;
                                                        
                                                        $set('../../subtotal_amount', $subtotal);
                                                        $set('../../ppn_amount', $ppnAmount);
                                                        $set('../../total_amount', $subtotal + $ppnAmount);
                                                    }),

                                                TextInput::make('price_after_discount')
                                                    ->label('Price after Disc.')
                                                    ->numeric()
                                                    ->readOnly()
                                                    ->prefix('Rp')
                                                    ->dehydrated(false), // Don't save to DB, just for display

                                                TextInput::make('total_price')
                                                    ->label('Subtotal')
                                                    ->numeric()
                                                    ->readOnly()
                                                    ->prefix('Rp')
                                                    ->dehydrated()
                                                    ->extraInputAttributes(['style' => 'text-align: right; font-weight: bold;']),
                                            ]),

                                        Textarea::make('description')
                                            ->label('Description (Optional)')
                                            ->rows(1)
                                            ->columnSpanFull(),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? null)
                                    ->collapsible()
                                    ->cloneable()
                                    ->defaultItems(1)
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, callable $set) {
                                        $items = $get('items') ?? [];
                                        $subtotal = collect($items)->sum(function ($item) {
                                            $q = $item['quantity'] ?? 1;
                                            $p = $item['unit_price'] ?? 0;
                                            $d = $item['discount'] ?? 0;
                                            return $q * ($p - ($p * $d / 100));
                                        });
                                        
                                        $hasPpn = $get('has_ppn');
                                        $ppnAmount = $hasPpn ? ($subtotal * 0.11) : 0;
                                        
                                        $set('subtotal_amount', $subtotal);
                                        $set('ppn_amount', $ppnAmount);
                                        $set('total_amount', $subtotal + $ppnAmount);
                                    })
                                    ->columns(1),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Summary')
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                Toggle::make('has_ppn')
                                    ->label('Include VAT (11%)')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $subtotal = $get('subtotal_amount') ?? 0;
                                        $ppnAmount = $state ? ($subtotal * 0.11) : 0;
                                        
                                        $set('ppn_amount', $ppnAmount);
                                        $set('total_amount', $subtotal + $ppnAmount);
                                    }),

                                TextInput::make('subtotal_amount')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->dehydrated(),

                                TextInput::make('ppn_amount')
                                    ->label('VAT (11%)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->dehydrated()
                                    ->visible(fn ($get) => $get('has_ppn')),

                                TextInput::make('total_amount')
                                    ->label('Grand Total')
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
