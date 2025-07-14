<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CasheBoxSessionResource\Pages;
use App\Filament\Resources\CasheBoxSessionResource\RelationManagers;
use App\Models\CashboxSession;
use App\Models\PaymentType;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CasheBoxSessionResource extends Resource
{
    protected static ?string $model = CashboxSession::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationGroup(): string
    {
        return 'Касса';
    }

    

    public static function getNavigationLabel(): string
    {
        return 'Кассовые смены'; // Rus tilidagi nom
    }
    
    public static function getModelLabel(): string
    {
        return 'Кассовые смены'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Кассовые смены'; // Rus tilidagi ko'plik shakli
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('date')->label('Дата')->required()->default(now()),
            Forms\Components\Select::make('payment_type_id')
                ->label('Тип платежа')
                ->options(PaymentType::where('name','!=','Перечисление')->pluck('name', 'id'))
                ->required(),
            Forms\Components\TextInput::make('opening_amount')
                ->label('Открытие кассы')
                ->numeric()
                ->default(0),
            Hidden::make('opened_by')->default(fn() => auth()->user()->id),
            Hidden::make('closed_by')->default(fn() => auth()->user()->id),
            Forms\Components\TextInput::make('closing_amount')
                ->label('Закрытие кассы')
                ->numeric()
                ->default(0)
                ->disabled(fn ($record) => $record?->closed_by !== null), // Agar yopilgan bo‘lsa tahrirlab bo‘lmaydi
        ]);
    }
    
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('date')->label('Дата')->date('d.m.Y'),
            Tables\Columns\TextColumn::make('paymentType.name')->label('Тип платежа'),
            Tables\Columns\TextColumn::make('opening_amount')->label('Открытие')->money('UZS'),
            Tables\Columns\TextColumn::make('closing_amount')->label('Закрытие')->money('UZS'),
            Tables\Columns\TextColumn::make('opened_date')->label('Открыл дата'),
            Tables\Columns\TextColumn::make('closed_date')->label('Закрыл дата')->default('-'),
        ])->filters([
            //
        ])
        ->defaultSort('created_at','desc')
        ->headerActions([
                Action::make('open_cashbox')
                    ->label('Открыть кассу')
                    ->icon('heroicon-o-plus-circle')
                    ->action(function (array $data) {
                        $lastClosing = CashboxSession::where('payment_type_id', $data['payment_type_id'])
                            ->whereDate('date', now()->subDay())
                            ->latest()
                            ->value('closing_amount') ?? 0;

                        CashboxSession::create([
                            'date' => now()->toDateString(),
                            'opened_date' => now(),
                            'payment_type_id' => $data['payment_type_id'],
                            'opened_by' => Auth::id(),
                            'opening_amount' =>$lastClosing,
                        ]);
                    })
                    ->form([
                        Select::make('payment_type_id')
                            ->label('Тип платежа')
                            ->options(function () {
                                // Bugungi kunda ochilgan (lekin hali yopilmagan) payment_type_id lar
                                $openedTypes = \App\Models\CashboxSession::whereDate('date', today())
                                    ->whereNull('closed_by')
                                    ->pluck('payment_type_id')
                                    ->toArray();

                                // "Перечисление" ni chiqarib tashlaymiz va ochilgan turlarni ham
                                return \App\Models\PaymentType::where('id', '=', 1)
                                    ->whereNotIn('id', $openedTypes)
                                    ->pluck('name', 'id');
                            })
                            ->required(),

                        // TextInput::make('opening_amount')
                        //     ->label('Сумма при открытии')
                        //     ->required()
                        //     ->numeric()
                        //     ->prefix('сум')
                        //     ->reactive()
                        //     ->afterStateHydrated(function (TextInput $component, $state, Get $get) {
                        //         $paymentTypeId = $get('payment_type_id');

                        //         if (!$paymentTypeId) return;

                        //         $lastSession = \App\Models\CashboxSession::query()
                        //             ->where('payment_type_id', $paymentTypeId)
                        //             ->orderByDesc('date')
                        //             ->first();

                        //         if ($lastSession) {
                        //             $component->state($lastSession->closing_amount);
                        //             $component->disabled(); // readonly qilib qo‘yiladi
                        //         } else {
                        //             $component->enabled(); // foydalanuvchi qo‘lda kiritadi
                        //         }
                        //     }),
                    ]),

                Action::make('close_cashbox')
                    ->label('Закрыть кассу')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->action(function (array $data) {
                        $session = CashboxSession::whereDate('date', now())
                            ->where('payment_type_id', $data['payment_type_id'])
                            ->whereNull('closed_by')
                            ->latest()
                            ->first();

                        if ($session) {
                            $session->update([
                                'closed_by' => Auth::id(),
                                // closing_amount already updated by Payments & BankTransfers
                            ]);
                        }
                    })
                    ->form([
                        Select::make('payment_type_id')
                            ->label('Тип платежа')
                            ->options(function () {
                                // Faqat bugungi ochiq kassalar
                                $ids = CashboxSession::whereDate('date', now())
                                    ->whereNull('closed_by')
                                    ->pluck('payment_type_id');

                                // Agar hech narsa topilmasa, bo‘sh array qaytariladi
                                if ($ids->isEmpty()) {
                                    return [];
                                }

                                return PaymentType::whereIn('id', $ids)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required(),

                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->can('остаток в кассе');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCasheBoxSessions::route('/'),
            // 'view' => Pages\ViewCasheBoxSession::route('/{record}'),
        ];
    }
}
