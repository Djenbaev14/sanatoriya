<?php

namespace App\Filament\Widgets;

use App\Models\Accommodation;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Forms\Components\Select;
class SanatoriumStats extends BaseWidget
{
//     protected ?string $heading = 'Sanatoriya statistikasi';
//     protected function hasFilterForm(): bool
// {
//     return true;
// }

//     // 👇 Form uchun filter select
//     protected function getFormSchema(): array
//     {
//         return [
//             Select::make('period')
//                 ->label('Vaqt oralig‘ini tanlang')
//                 ->options([
//                     '7_days' => 'So‘nggi 1 hafta',
//                     '1_month' => '1 oy',
//                     '2_months' => '2 oy',
//                     '6_months' => '6 oy',
//                     '1_year' => '1 yil',
//                 ])
//                 ->default('7_days') // Dastlabki holatda
//                 ->reactive(),
//         ];
//     }

//     protected function getCards(): array
//     {
//         $periodKey = $this->filterFormData['period'] ?? '7_days';

//         $fromDate = match ($periodKey) {
//             '7_days' => Carbon::now()->subDays(7),
//             '1_month' => Carbon::now()->subMonth(),
//             '2_months' => Carbon::now()->subMonths(2),
//             '6_months' => Carbon::now()->subMonths(6),
//             '1_year' => Carbon::now()->subYear(),
//             default => Carbon::now()->subDays(7),
//         };

//         $kelgan = Accommodation::where('admission_date', '>=', $fromDate)->count();
//         $chiqqan = Accommodation::where('discharge_date', '>=', $fromDate)->count();

//         $cards = [
//             Card::make('🟢 Kelgan bemorlar', $kelgan)->color('success'),
//             Card::make('🔴 Chiqqan bemorlar', $chiqqan)->color('danger'),
//         ];

//         $averageDays = Accommodation::whereNotNull('discharge_date')
//             ->where('discharge_date', '>=', $fromDate)
//             ->selectRaw('AVG(DATEDIFF(discharge_date, admission_date)) as avg_days')
//             ->value('avg_days');

//         $cards[] = Card::make('📊 O‘rtacha davolanish muddati', round($averageDays) . ' kun')->color('gray');

//         return $cards;
//     }
}
