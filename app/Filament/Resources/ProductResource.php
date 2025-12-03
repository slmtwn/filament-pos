<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group as GroupingGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    Section::make([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('base_price')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('stock')
                            ->required()
                            ->numeric(),
                        TextInput::make('sku'),
                        TextInput::make('barcode'),
                        Group::make([
                            Forms\Components\Toggle::make('is_active')
                                ->required(),
                            Forms\Components\Toggle::make('in_stock')
                                ->required(),
                        ]),

                        RichEditor::make('description')
                            ->columnSpanFull(),
                    ])->columns(3)
                        ->description('Product Detail')
                ])->columnSpan(2),
                Section::make([
                    Select::make('brand_id')
                        ->relationship('brand', 'name', fn($query) => $query->where('is_active', true))
                        ->default(null),
                    Select::make('category_id')
                        ->relationship('category', 'name', fn($query) => $query->where('is_active', true))
                        ->default(null)
                        ->reactive(),
                    Select::make('sub_category_id')
                        ->label('Sub Category')
                        ->options(function (Get $get) {
                            $categoryId = $get('category_id');
                            if (!$categoryId) return [];

                            return SubCategory::where('category_id', $categoryId)
                                ->pluck('name', 'id');
                        })->reactive()
                        ->disabled(fn(callable $get) => $get('category_id') === null),
                    FileUpload::make('image')
                        ->image()
                ])->columnSpan(1)
                    ->description('Assosiation')
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->prefix('Rp')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sub_category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('in_stock')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
