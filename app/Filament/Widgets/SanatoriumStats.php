<?php

namespace App\Filament\Widgets;

use App\Models\Accommodation;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Assets\Js;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
class SanatoriumStats extends ChartWidget
{
    protected static ?string $heading = 'Динамика больных';
    
    protected static ?string $description = 'График поступления и выписки больных';
    
    protected static string $color = 'info';
    
    protected static ?string $maxHeight = '350px';
    
    protected int | string | array $columnSpan = 'full';

    public ?string $filter = '30_days';

    protected function getFilters(): ?array
    {
        return [
            '7_days' => '1 неделя',
            '30_days' => '1 месяц',
            '180_days' => '6 месяц',
            '365_days' => '1 год',
        ];
    }

    protected function getData(): array
    {
        $period = $this->filter ?? '30_days';
        [$startDate, $endDate, $periodName] = $this->getDateRangeByFilter($period);
        
        $dates = [];
        $admittedData = [];
        $dischargedData = [];
        $currentlyAdmittedData = [];
        
        $currentDate = $startDate->copy();
        $interval = $this->getChartInterval($startDate, $endDate);
        
        while ($currentDate <= $endDate) {
            $periodStart = $currentDate->copy()->startOfDay();
            $periodEnd = $currentDate->copy()->add($interval['value'], $interval['unit'])->endOfDay();
            
            // Sana formatlash
            $dateLabel = $this->formatDateLabel($currentDate, $interval);
            $dates[] = $dateLabel;
            
            // Kelgan bemorlar
            $admittedCount = Accommodation::whereBetween('admission_date', [$periodStart, $periodEnd])
                ->whereNotNull('admission_date')
                ->count();
                
            // Chiqqan bemorlar
            $dischargedCount = Accommodation::whereBetween('discharge_date', [$periodStart, $periodEnd])
                ->whereNotNull('discharge_date')
                ->count();
                
            // Hozirda yotgan bemorlar
            $currentlyAdmittedCount = Accommodation::whereNotNull('admission_date')
                ->whereNull('discharge_date')
                ->where('admission_date', '<=', $periodEnd)
                ->count();
                
            $admittedData[] = $admittedCount;
            $dischargedData[] = $dischargedCount;
            $currentlyAdmittedData[] = $currentlyAdmittedCount;
            
            $currentDate->add($interval['value'], $interval['unit']);
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'прибывшие больные',
                    'data' => $admittedData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgba(34, 197, 94, 1)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 8,
                ],
                [
                    'label' => 'Выписанные пациенты',
                    'data' => $dischargedData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgba(239, 68, 68, 1)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 8,
                ],
                [
                    'label' => 'Сейчас лежит',
                    'data' => $currentlyAdmittedData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 3,
                    'fill' => false,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgba(59, 130, 246, 1)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 8,
                    'borderDash' => [5, 5],
                ],
            ],
            'labels' => $dates,
        ];
    }

    private function getDateRangeByFilter(string $filter): array
    {
        $endDate = Carbon::now();
        
        switch ($filter) {
            case '7_days':
                $startDate = Carbon::now()->subDays(7);
                $periodName = "Последняя 1 неделя";
                break;
            case '30_days':
                $startDate = Carbon::now()->subDays(30);
                $periodName = "Последний 1 месяц";
                break;
            case '180_days':
                $startDate = Carbon::now()->subDays(180);
                $periodName = "Последний 6 месяц";
                break;
            case '365_days':
                $startDate = Carbon::now()->subDays(365);
                $periodName = "Последние 1 год";
                break;
            default:
                $startDate = Carbon::now()->subDays(30);
                $periodName = "Последний 1 месяц";
        }
        
        return [$startDate, $endDate, $periodName];
    }

    private function getChartInterval(Carbon $startDate, Carbon $endDate): array
    {
        $diffInDays = $startDate->diffInDays($endDate);
        
        if ($diffInDays <= 7) {
            return ['value' => 1, 'unit' => 'day'];
        } elseif ($diffInDays <= 30) {
            return ['value' => 1, 'unit' => 'day'];
        } elseif ($diffInDays <= 180) {
            return ['value' => 1, 'unit' => 'week'];
        } else {
            return ['value' => 1, 'unit' => 'month'];
        }
    }

    private function formatDateLabel(Carbon $date, array $interval): string
    {
        if ($interval['unit'] === 'day') {
            return $date->format('d.m');
        } elseif ($interval['unit'] === 'week') {
            return $date->format('d.m') . ' (hafta)';
        } else {
            return $date->format('M Y');
        }
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                        'drawBorder' => false,
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                        'color' => 'rgba(0, 0, 0, 0.7)',
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Количество больных',
                        'color' => 'rgba(0, 0, 0, 0.8)',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                        ],
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'color' => 'rgba(0, 0, 0, 0.7)',
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Дата',
                        'color' => 'rgba(0, 0, 0, 0.8)',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                        ],
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                        'color' => 'rgba(0, 0, 0, 0.8)',
                        'font' => [
                            'size' => 13,
                        ],
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => '#fff',
                    'bodyColor' => '#fff',
                    'borderColor' => 'rgba(255, 255, 255, 0.1)',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'displayColors' => true,
                    'callbacks' => [
                        'label' => Js::make(<<<'JS'
                            function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y;
                                return label + ': ' + value + ' bemor';
                            }
                        JS),
                    ],
                ],

            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
            'elements' => [
                'point' => [
                    'hoverBackgroundColor' => '#fff',
                ],
            ],
        ];
    }
    public static function canView(): bool
    {
        return auth()->user()?->hasRole('dada');
    }

}
