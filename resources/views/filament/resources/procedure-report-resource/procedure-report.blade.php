<x-filament::page>
    <form wire:submit.prevent="generateReport" class="space-y-4">
        {{ $this->form }}
        <x-filament::button type="submit">Фильтр</x-filament::button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-2">Статистика оборотов кассы</h2>
            <canvas id="incomeChart" style="height: 300px;"></canvas>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-4">
                {{ \Carbon\Carbon::parse($startDate)->format('d.M.Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d.M.Y') }}
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border border-gray-200 rounded">
                    <tbody>
                            <td class="py-3 px-4">Общий доход</td>
                            <td class="py-3 px-4 text-right">{{ number_format($totalIncome, 0, ',', ' ') }}</td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">Наличные</td>
                            <td class="py-2 px-4 text-right">{{ number_format($nak, 0, ',', ' ') }}</td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">Клик</td>
                            <td class="py-2 px-4 text-right">{{ number_format($terminal, 0, ',', ' ') }}</td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">Перечисление</td>
                            <td class="py-2 px-4 text-right">{{ number_format($transfer, 0, ',', ' ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @livewireScripts
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>

            // document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('incomeChart').getContext('2d');
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json(array_keys($chartData)),
                        datasets: [{
                            label: 'Сумма',
                            data: @json(array_values($chartData)),
                            backgroundColor: 'rgba(54, 162, 235, 0.3)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            fill: true,
                            tension: 0.4,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            // });
        </script>
</x-filament::page>
