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
            <h2 class="text-lg font-semibold mb-2">
                {{ \Carbon\Carbon::parse($startDate)->format('d.M.Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d.M.Y') }}
            </h2>
            <div class="space-y-2">
                <div class="flex justify-between font-bold bg-blue-100 p-2 rounded">
                    <span>Общий доход</span>
                    <span>{{ number_format($totalIncome, 0, ',', ' ') }}</span>
                </div>
                <div class="flex justify-between"><span>Нак</span><span>{{ number_format($nak, 0, ',', ' ') }}</span></div>
                <div class="flex justify-between"><span>Терминал</span><span>{{ number_format($terminal, 0, ',', ' ') }}</span></div>
                <div class="flex justify-between"><span>Перечисление</span><span>{{ number_format($transfer, 0, ',', ' ') }}</span></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
            const ctx = document.getElementById('incomeChart').getContext('2d');
            const chart = new Chart(ctx, {
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
        });
    </script>
</x-filament::page>
