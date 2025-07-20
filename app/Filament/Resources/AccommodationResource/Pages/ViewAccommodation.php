<?php

namespace App\Filament\Resources\AccommodationResource\Pages;

use App\Filament\Resources\AccommodationResource;
use App\Models\Accommodation;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Infolists;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;

class ViewAccommodation extends ViewRecord
{
    protected static string $resource = AccommodationResource::class;

    
    protected function getActions(): array
    {
        return [
            Action::make('delete')
                ->label('Удалить')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Удалить запись')
                ->modalDescription('Вы уверены, что хотите удалить эту запись? Это действие нельзя отменить.')
                ->modalSubmitActionLabel('Да, удалить')
                ->visible(fn () => $this->record->status_payment_id == 1)
                ->action(fn () => $this->deleteRecord()),

            Action::make('edit')
                ->label('Редактировать')
                ->icon('heroicon-o-pencil')
                ->color('warning')
                ->visible(fn () => $this->record->status_payment_id == 1)
                ->url(fn () => route('filament.admin.resources.accommodations.edit', $this->record)),

            Action::make('send_to_kassa')
                ->label('Отправить кассе')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Отправить в кассу')
                ->modalDescription('Отправить данные в кассу для оплаты?')
                ->modalSubmitActionLabel('Да, отправить')
                ->visible(fn () => $this->record->status_payment_id == 1)
                ->action(fn () => $this->sendToKassa()),
            Action::make('back')
                ->label('Назад')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->url(fn () => route('filament.admin.resources.medical-histories.view', $this->record->medical_history_id)),


            // Action::make('print')
            //     ->label('Печать')
            //     ->icon('heroicon-o-printer')
            //     ->color('info')
            //     ->action(fn () => $this->printRecord()),
        ];
    }
    public function sendToKassa()
    {
        // Kassaga yuborish logikasi
        $this->record->update([
            'status_payment_id' => '2',
        ]);
        // agar chu record id ga teng main_accommodation_id bo'lsa, status_payment_id ni 2 ga o'zgartirish
        Accommodation::where('main_accommodation_id', $this->record->id)
            ->update(['status_payment_id' => '2']);        
        \Filament\Notifications\Notification::make()
            ->title('Отправлено в кассу')
            ->body('Данные успешно отправлены в кассу')
            ->success()
            ->send();
            // agar role Доктор bo'lsa MedicalHistoryResource view ga qaytarish agar unday bolmasa MedicalPaymentResource view ga qaytarish
        // if (auth()->user()->hasRole('Доктор')) {
            return $this->redirect(route('filament.admin.resources.medical-histories.view', $this->record->medical_history_id));
        // } else {
        //     return $this->redirect(route('filament.admin.resources.medical-payments.view', $this->record->medical_history_id));
        // }
    }
    public function printRecord()
    {
        $this->dispatch('print-page');
        
        \Filament\Notifications\Notification::make()
            ->title('Подготовлено к печати')
            ->body('Документ готов к печати')
            ->success()
            ->send();
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Asosiy ma'lumotlar bo'limi
                Section::make('👤 Пациент и основная информация')
                    ->description('Персональные данные пациента и подробности госпитализации')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('patient.full_name')
                                ->label('Пациент ФИО')
                                ->icon('heroicon-o-user')
                                ->iconColor('success')
                                ->weight(FontWeight::Bold)
                                ->size(TextEntry\TextEntrySize::Large)
                                ->color('success'),

                            TextEntry::make('createdBy.name')
                                ->label('Зарегистрировано')
                                ->icon('heroicon-o-user-plus')
                                ->iconColor('info')
                                ->placeholder('Noma\'lum')
                                ->color('info'),

                            TextEntry::make('medicalHistory.number')
                                ->label('Медицинская история')
                                ->icon('heroicon-o-document-text')
                                ->iconColor('warning')
                                ->copyable()
                                ->copyMessage('Номер истории скопирован!')
                                ->color('warning'),
                        ]),
                    ])
                    ->columnSpan('full'),

                // Tarif va narxlar
                Section::make('')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Fieldset::make('🏷️ Tarif ma\'lumotlari')
                            ->schema([
                                Grid::make(2)->schema([

                                    TextEntry::make('tariff.daily_price')
                                        ->label('Дневная цена')
                                        ->icon('heroicon-o-currency-dollar')
                                        ->iconColor('success')
                                        ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум/кун')
                                        ->weight(FontWeight::Bold)
                                        ->color('success')
                                        ->size(TextEntry\TextEntrySize::Large),
                                ]),
                            ]),

                        Fieldset::make('🍽️ Ovqat tariflari')
                            ->schema([
                                Grid::make(2)->schema([

                                    TextEntry::make('mealType.daily_price')
                                        ->label('Стоимость еды')
                                        ->icon('heroicon-o-currency-dollar')
                                        ->iconColor('warning')
                                        ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум/kun')
                                        ->weight(FontWeight::Bold)
                                        ->color('warning')
                                        ->size(TextEntry\TextEntrySize::Large),
                                ]),
                            ]),
                        Fieldset::make('🏥 Информация о палате и койке')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('ward.name')
                                        ->label('Палата')
                                        ->icon('heroicon-o-home')
                                        ->iconColor('primary')
                                        ->badge()
                                        ->color('primary')
                                        ->size(TextEntry\TextEntrySize::Large),

                                    TextEntry::make('bed.number')
                                        ->label('Номер койки')
                                        ->icon('heroicon-o-rectangle-stack')
                                        ->iconColor('secondary')
                                        ->badge()
                                        ->color('secondary')
                                        ->size(TextEntry\TextEntrySize::Large),
                                ]),
                            ])
                    ])
                    ->columnSpan('full'),

                // Sanalar bo'limi
                Section::make('📅 Sanalar')
                    ->description('Qabul va chiqish sanalari')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('admission_date')
                                ->label('Дата поступления')
                                ->icon('heroicon-o-arrow-right-on-rectangle')
                                ->iconColor('success')
                                ->dateTime('d.m.Y H:i')
                                ->badge()
                                ->color('success')
                                ->size(TextEntry\TextEntrySize::Large),

                            TextEntry::make('discharge_date')
                                ->label('Дата выхода')
                                ->icon('heroicon-o-arrow-left-on-rectangle')
                                ->iconColor('danger')
                                ->date('d.m.Y')
                                ->badge()
                                ->color('danger')
                                ->size(TextEntry\TextEntrySize::Large)
                                ->placeholder('Chiqish sanasi aniq emas'),
                        ]),
                    ])
                    ->columnSpan('full'),

                // Hisob-kitob bo'limi
                // Section::make('🧮 Hisob-kitob')
                //     ->description('Batafsil moliyaviy hisob-kitob')
                //     ->icon('heroicon-o-calculator')
                //     ->schema([
                //         Grid::make(1)->schema([
                //             TextEntry::make('id')
                //                 ->label('')
                //                 ->formatStateUsing(function ($state, $record) {
                //                     $days= $record->calculateDays();
                //                     $tariff = $record->tariff?->daily_price ?? 0;
                //                     $meal = $record->mealType?->daily_price ?? 0;

                //                     $bedSum = $days * $tariff;
                //                     $mealSum = $days * $meal;
                //                     $total = $bedSum + $mealSum;

                //                     return "
                //                         <div class='space-y-4'>
                //                             <div class='grid grid-cols-1 md:grid-cols-3 gap-4'>
                //                                 <div class='bg-blue-50 p-4 rounded-lg border border-blue-200'>
                //                                     <div class='flex items-center space-x-2'>
                //                                         <svg class='w-5 h-5 text-blue-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                //                                             <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'></path>
                //                                         </svg>
                //                                         <span class='text-sm font-medium text-blue-600'>Kunlar soni</span>
                //                                     </div>
                //                                     <div class='text-2xl font-bold text-blue-900 mt-2'>{$record->calculateDays()} kun</div>
                //                                 </div>
                                                
                //                                 <div class='bg-green-50 p-4 rounded-lg border border-green-200'>
                //                                     <div class='flex items-center space-x-2'>
                //                                         <svg class='w-5 h-5 text-green-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                //                                             <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z'></path>
                //                                         </svg>
                //                                         <span class='text-sm font-medium text-green-600'>Koyka narxi</span>
                //                                     </div>
                //                                     <div class='text-lg font-bold text-green-900 mt-2'>" . number_format($record->tariff->daily_price, 0, '.', ' ') . " сум</div>
                //                                 </div>
                                                
                //                                 <div class='bg-orange-50 p-4 rounded-lg border border-orange-200'>
                //                                     <div class='flex items-center space-x-2'>
                //                                         <svg class='w-5 h-5 text-orange-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                //                                             <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zM3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z'></path>
                //                                         </svg>
                //                                         <span class='text-sm font-medium text-orange-600'>Ovqat narxi</span>
                //                                     </div>
                //                                     <div class='text-lg font-bold text-orange-900 mt-2'>" . number_format($record->mealType->daily_price, 0, '.', ' ') . " сум</div>
                //                                 </div>
                //                             </div>
                                            
                //                             <div class='grid grid-cols-1 md:grid-cols-2 gap-4'>
                //                                 <div class='bg-indigo-50 p-4 rounded-lg border border-indigo-200'>
                //                                     <div class='flex items-center space-x-2'>
                //                                         <svg class='w-5 h-5 text-indigo-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                //                                             <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z'></path>
                //                                         </svg>
                //                                         <span class='text-sm font-medium text-indigo-600'>Koyka uchun jami</span>
                //                                     </div>
                //                                     <div class='text-xl font-bold text-indigo-900 mt-2'>" . number_format($record->calculateBedCost(), 0, '.', ' ') . " сум</div>
                //                                 </div>
                                                
                //                                 <div class='bg-purple-50 p-4 rounded-lg border border-purple-200'>
                //                                     <div class='flex items-center space-x-2'>
                //                                         <svg class='w-5 h-5 text-purple-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                //                                             <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2-2v7h18zM3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z'></path>
                //                                         </svg>
                //                                         <span class='text-sm font-medium text-purple-600'>Ovqat uchun jami</span>
                //                                     </div>
                //                                     <div class='text-xl font-bold text-purple-900 mt-2'>" . number_format($record->calculateMealCost(), 0, '.', ' ') . " сум</div>
                //                                 </div>
                //                             </div>
                                            
                //                             <div class='bg-gradient-to-r from-green-400 to-blue-500 p-6 rounded-lg shadow-lg'>
                //                                 <div class='text-center'>
                //                                     <div class='flex items-center justify-center space-x-2 mb-2'>
                //                                         <svg class='w-8 h-8 ' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                //                                             <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path>
                //                                         </svg>
                //                                         <span class=' font-medium text-lg'>UMUMIY SUMMA</span>
                //                                     </div>
                //                                     <div class='text-4xl font-bold '>" . number_format($record->getTotalCost(), 0, '.', ' ') . " сум</div>
                //                                 </div>
                //                             </div>
                //                         </div>
                //                     ";
                //                 })
                //                 ->html()
                //                 ->columnSpan('full'),
                //         ])
                //     ])
                //     ->columnSpan('full'),
            ])
            ->columns(1);
    }
}