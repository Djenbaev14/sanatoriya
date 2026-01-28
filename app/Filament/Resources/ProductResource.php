<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\AgeCategory;
use App\Models\Product;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\ComingProduct;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Склад';
    protected static ?int $navigationSort = 1;
    
    public static function canAccess(): bool
    {
        return auth()->user()?->can('склад');
    }

    public static function form(Form $form): Form
    {
        $ageCategories = AgeCategory::all();
        $tabs = [];

        foreach ($ageCategories as $age) {
            $tabs[] = Tab::make($age->name)
                ->schema([
                    Forms\Components\TextInput::make("ageCategoryProducts.{$age->id}.quantity")
                        ->label("Kunlik miqdor")
                        ->numeric()
                        ->suffix(fn (callable $get) => $get('unit_name')) // product tanlansa suffix sifatida chiqadi
                        ->default(fn ($record) => $record?->ageCategoryProducts
                            ->firstWhere('age_category_id', $age->id)?->quantity),
                ]);
        }
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название товара')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('union_id')
                            ->label('Единница измерения')
                            ->relationship('union', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $union = \App\Models\Union::find($state);
                                    $set('unit_name', $union?->name ?? '');
                                } else {
                                    $set('unit_name', '');
                                }
                            }),
                        Hidden::make('unit_name')->reactive(),
                        
                        Forms\Components\TextInput::make('warehouse_quan')
                            ->label('Количество склада')
                            ->numeric()
                            ->required()
                            ->visibleOn('create')   // faqat create paytida ko‘rsatiladi
                            ->disabledOn('edit'),   // edit paytida bloklanadi
                        Tabs::make('Age Categories')->tabs($tabs),
                    ])
                    ->columnSpan(['sm' => 12, 'md' => 12, 'lg' => 12]),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('comingProduct')
                    ->label('Приход товара')
                    ->modalHeading('Приход товара')
                    ->modalWidth('3xl')
                    ->form([
                        // 🔹 1. Yetkazib beruvchi
                        Select::make('supplier_id')
                            ->label('Поставщик')
                            ->options(fn () => Supplier::pluck('name', 'id')->toArray())
                            ->required()
                            ->searchable()
                            ->preload(),

                        // 🔹 2. Yetkazib berish sanasi
                        Forms\Components\DatePicker::make('delivery_date')
                            ->label('Дата поставки')
                            ->default(now())
                            ->required(),

                        // 🔹 3. Bir nechta mahsulotlar uchun Repeater
                        Forms\Components\Repeater::make('products')
                            ->label('Товары')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Товар')
                                    ->options(fn () => Product::pluck('name', 'id')->toArray())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $product = \App\Models\Product::with('union')->find($state);
                                            $set('unit_name', $product?->union?->name ?? '');
                                        } else {
                                            $set('unit_name', '');
                                        }
                                    }),

                                Hidden::make('unit_name')->reactive(),

                                TextInput::make('quantity')
                                    ->label('Количество')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->suffix(fn (callable $get) => $get('unit_name'))
                                    ->required(),

                                TextInput::make('price')
                                    ->label('Цена')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->minItems(1)
                            ->columns(4)
                            ->createItemButtonLabel('➕ Добавить товар'),
                    ])
                    ->action(function (array $data) {
                        DB::transaction(function () use ($data) {

                            // 1️⃣ Supplier delivery
                            $delivery = \App\Models\SupplierDelivery::create([
                                'supplier_id' => $data['supplier_id'],
                                'delivery_date' => $data['delivery_date'],
                            ]);

                            // 2️⃣ ComingProduct — bulk insert
                            $rows = collect($data['products'] ?? [])
                                ->map(fn ($item) => [
                                    'supplier_delivery_id' => $delivery->id,
                                    'product_id' => $item['product_id'],
                                    'quantity' => $item['quantity'],
                                    'price' => $item['price'],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ])
                                ->values()
                                ->all();

                            if (!empty($rows)) {
                                \App\Models\ComingProduct::insert($rows); // ✅ 1 ta query
                            }
                        });
                    })

            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Количество')
                    ->formatStateUsing(function ($record) {
                        return $record->total_quantity . ' ' . ($record->union?->name ?? '');
                    }),
            ])
            ->defaultSort('id','desc')
            ->actions([
                Tables\Actions\EditAction::make()
            ]);
    }
    

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Продукы'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Продукы'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Продукы'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
