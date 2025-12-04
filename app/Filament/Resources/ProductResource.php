<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Brand;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\SubCategory;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Grouping\Group as GroupingGroup;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $activeNavigationIcon = 'heroicon-s-squares-2x2';
    protected static ?string $navigationGroup = 'Product Management';
    protected static ?int $navigationSort = 4;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'barcode', 'brand.name', 'category.name', 'sub_category.name'];
    }
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Name' => $record->name ?? 'N/A',
            'SKU' => $record->sku ?? 'N/A',
            'Barcode' => $record->barcode ?? 'N/A',
            'Brand' => $record->brand?->name ?? 'N/A',
            'Category' => $record->category?->name ?? 'N/A',
            'Sub Category' => $record->sub_category?->name ?? 'N/A',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'The number of products that are currently active.';
    }
    public static function generateSku(Get $get, Set $set)
    {
        $brand = Brand::find($get('brand_id'));
        $category = Category::find($get('category_id'));
        $subCategory = SubCategory::find($get('sub_category_id'));

        if (!$brand || !$category || !$subCategory) {
            return;
        }

        //ambil 3 huruf pertama dari masing-masing
        $brandCode = strtoupper(substr($brand->name, 0, 3));
        $categoryCode = strtoupper(substr($category->name, 0, 3));
        $subCategoryCode = strtoupper(substr($subCategory->name, 0, 3));

        $lastSku = Product::where('category_id', $category->id)
            ->where('brand_id', $brand->id)
            ->where('sub_category_id', $subCategory->id)
            ->orderBy('id', 'desc')
            ->value('sku');

        $nextNumber = 1;
        if ($lastSku) {
            $part = explode('-', $lastSku);
            $lastNumber = intval(end($part));
            $nextNumber = $lastNumber + 1;
        }
        $sku = sprintf('%s-%s-%s-%04d', $brandCode, $categoryCode, $subCategoryCode, $nextNumber);
        $set('sku', $sku);
    }
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
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            static::generateSku($get, $set);
                        })
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required(),
                            FileUpload::make('image')
                                ->image()
                                ->maxFiles(2048),
                            Forms\Components\Toggle::make('is_active')
                                ->required(),
                        ]),
                    Select::make('category_id')
                        ->relationship('category', 'name', fn($query) => $query->where('is_active', true))
                        ->default(null)
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            static::generateSku($get, $set);
                        })
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required(),
                            FileUpload::make('image')
                                ->image(),
                            Forms\Components\Toggle::make('is_active'),
                        ]),
                    Select::make('sub_category_id')
                        ->label('Sub Category')
                        ->options(function (Get $get) {
                            $categoryId = $get('category_id');
                            if (!$categoryId) return [];

                            return SubCategory::where('category_id', $categoryId)
                                ->pluck('name', 'id');
                        })->reactive()
                        ->disabled(fn(callable $get) => $get('category_id') === null)
                        ->dehydrated()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            static::generateSku($get, $set);
                        })
                        ->createOptionForm([
                            Select::make('category_id')
                                ->options(Category::pluck('name', 'id'))
                                ->required(),
                            TextInput::make('name')
                                ->required(),
                            FileUpload::make('image')
                                ->image(),
                            Forms\Components\Toggle::make('is_active'),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return SubCategory::create($data)->getKey('id');
                        }),
                    FileUpload::make('image')
                        ->image()
                        ->maxFiles(2048),
                ])->columnSpan(1)
                    ->description('Assosiation')
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('base_price')
                    ->money('idr', true)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('price')
                    ->money('idr', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sku')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('barcode')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sub_category.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('in_stock')
                    ->boolean(),
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
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),

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
