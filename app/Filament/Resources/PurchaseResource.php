<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FusedGroup;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('purchase_number')
                    ->hiddenLabel()
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('Purchase Number')
                    ->columnSpanFull()
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_id')
                    ->hiddenLabel()
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('Create By')
                    ->columnSpanFull()
                    ->required(),
                Group::make([
                    Fieldset::make('Purchase Summary')
                        ->schema([
                            Forms\Components\Select::make('supplier_id')
                                ->required()
                                ->label('Supplier Name'),
                            Forms\Components\DatePicker::make('purchase_date')
                                ->label('Purchase Date')
                                ->required(),
                            Forms\Components\DatePicker::make('received_date')
                                ->label('Received Date')
                                ->required(),
                        ])->columns(3),
                ])->columnSpan(2),
                Fieldset::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->columnSpanFull()
                            ->options([
                                'draft' => 'Draft',
                                'received' => 'Received',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft'),
                        Forms\Components\TextInput::make('subtotal')
                            ->columnSpanFull()
                            ->required()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('tax')
                            ->required()
                            ->suffix('%')
                            ->default(0),
                        Forms\Components\TextInput::make('tax_amount')
                            ->required()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('discount')
                            ->required()
                            ->suffix('%'),
                        Forms\Components\TextInput::make('discount_amount')
                            ->required()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('total_payment')
                            ->required()
                            ->prefix('Rp')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('payment_status')
                            ->required()
                            ->options([
                                'paid' => 'Paid',
                                'unpaid' => 'Unpaid',
                            ])
                            ->default('unpaid'),
                        Forms\Components\Select::make('payment_method')
                            ->required()
                            ->options([
                                'cash' => 'Cash',
                                'credit' => 'Credit',
                                'debit' => 'Debit',
                                'qris' => 'QRIS',
                            ])
                            ->default('cash'),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchase_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('received_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_payment')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('payment_status'),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'view' => Pages\ViewPurchase::route('/{record}'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
