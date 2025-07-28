<x-filament::page>
    <form wire:submit.prevent="generateReport" class="space-y-4">
        {{ $this->form }}
        <x-filament::button type="submit">Фильтр</x-filament::button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-2">Статистика оборотов кассы {{ number_format( $nak + $terminal + $transfer, 0, ',', ' ') }} сум</h2>
            <canvas id="incomeChart" style="height: 300px;"></canvas>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-4">
                {{ \Carbon\Carbon::parse($startDate)->format('d.M.Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d.M.Y') }}
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border border-gray-200 rounded">
                    <tbody>
                        <tr>
                            <td class="py-3 px-4">Общий доход</td>
                            <td class="py-3 px-4 text-right">{{ number_format( $nak + $terminal + $transfer, 0, ',', ' ') }}</td>
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="bg-white shadow rounded p-4">
            <canvas id="amountChart" style="height: 300px;"></canvas>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-4">
                {{ \Carbon\Carbon::parse($startDate)->format('d.M.Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d.M.Y') }}
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border border-gray-200 rounded">
                    <tbody>
                        {{-- <tr>
                            <td class="py-3 px-4">Общий доход</td>
                            <td class="py-3 px-4 text-right">{{ number_format( $nak + $terminal + $transfer, 0, ',', ' ') }}</td>
                        </tr> --}}
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">Процедуы</td>
                            <td class="py-2 px-4 text-right">{{ number_format($procedureAmount, 0, ',', ' ') }}</td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">Анализы</td>
                            <td class="py-2 px-4 text-right">{{ number_format($labTestAmount, 0, ',', ' ') }}</td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">Койка</td>
                            <td class="py-2 px-4 text-right">{{ number_format($koykaAmount, 0, ',', ' ') }}</td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">Питание</td>
                            <td class="py-2 px-4 text-right">{{ number_format($pitanieAmount, 0, ',', ' ') }}</td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">Койка (Уход)</td>
                            <td class="py-2 px-4 text-right">{{ number_format($koykaUxodAmount, 0, ',', ' ') }}</td>
                        </tr>
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">Питание (Уход)</td>
                            <td class="py-2 px-4 text-right">{{ number_format($pitanieUxodAmount, 0, ',', ' ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <!-- Chart Section -->
        <div class="bg-white shadow rounded p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Статистика по услуга {{ number_format($totalIncome, 0, ',', ' ') }} сум
                </h2>
            </div>
            <div class="relative">
                <canvas id="servicesChart" style="height: 400px;"></canvas>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white shadow rounded p-4">
            <div class="overflow-x-auto" style="max-height: 400px; overflow-y: auto;">
                <table class="w-full text-sm border-collapse border border-gray-200 dark:border-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="w-50 border border-gray-200 dark:border-gray-600 px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">
                                Услуга ↕
                            </th>
                            <th class="w-25 border border-gray-200 dark:border-gray-600 px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">
                                Кол-во ↕
                            </th>
                            <th class="w-25 border border-gray-200 dark:border-gray-600 px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                Сумма
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @foreach($tableData as $index => $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white" style="max-width: 200px">
                                {{ $row['service'] }}
                            </td>
                            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-center text-gray-900 dark:text-white">
                                {{ $row['count'] }}
                            </td>
                            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                {{ number_format($row['amount'], 0, ',', ' ') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    Showing 1 to {{ count($tableData) }} of {{ count($tableData) }} entries
                </div>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <!-- Chart Section -->
        <div class="bg-white shadow rounded p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Статистика по анализы {{ number_format($totalIncome2, 0, ',', ' ') }} сум
                </h2>
            </div>
            <div class="relative">
                <canvas id="labTestsChart" style="height: 400px;"></canvas>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white shadow rounded p-4">
            <div class="overflow-x-auto" style="max-height: 400px; overflow-y: auto;">
                <table class="w-full text-sm border-collapse border border-gray-200 dark:border-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="w-50 border border-gray-200 dark:border-gray-600 px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">
                                Услуга ↕
                            </th>
                            <th class="w-25 border border-gray-200 dark:border-gray-600 px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">
                                Кол-во ↕
                            </th>
                            <th class="w-25 border border-gray-200 dark:border-gray-600 px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                Сумма
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @foreach($tableData2 as $index => $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-900 dark:text-white" style="max-width: 200px">
                                {{ $row['service'] }}
                            </td>
                            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-center text-gray-900 dark:text-white">
                                {{ $row['count'] }}
                            </td>
                            <td class="border border-gray-200 dark:border-gray-600 px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                {{ number_format($row['amount'], 0, ',', ' ') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    Showing 1 to {{ count($tableData2) }} of {{ count($tableData) }} entries
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let servicesChart = null;
        
        function initChart() {
            const ctx = document.getElementById('servicesChart');
            if (!ctx) return;
            
            // Destroy existing chart
            if (servicesChart) {
                servicesChart.destroy();
            }
            
            const chartData = {!! json_encode($chartData) !!};
            
            if (!chartData || chartData.length === 0) {
                return;
            }
            
            const labels = chartData.map(item => {
                const sessions = item.sessions || 0;
                return item.name + ' (' + sessions + ')';
            });
            const data = chartData.map(item => parseFloat(item.amount) || 0);
            
            // Generate colors for each service
            const backgroundColors = [
                'rgba(54, 162, 235, 0.8)',   // Blue
                'rgba(255, 99, 132, 0.8)',   // Red  
                'rgba(255, 205, 86, 0.8)',   // Yellow
                'rgba(75, 192, 192, 0.8)',   // Teal
                'rgba(153, 102, 255, 0.8)',  // Purple
                'rgba(255, 159, 64, 0.8)',   // Orange
                'rgba(199, 199, 199, 0.8)',  // Gray
                'rgba(83, 102, 255, 0.8)',   // Indigo
                'rgba(255, 193, 7, 0.8)',    // Amber
                'rgba(76, 175, 80, 0.8)',    // Green
                'rgba(233, 30, 99, 0.8)',    // Pink
                'rgba(156, 39, 176, 0.8)',   // Deep Purple
                'rgba(63, 81, 181, 0.8)',    // Indigo
                'rgba(0, 188, 212, 0.8)',    // Cyan
                'rgba(0, 150, 136, 0.8)',    // Teal
                'rgba(139, 195, 74, 0.8)',   // Light Green
                'rgba(205, 220, 57, 0.8)',   // Lime
                'rgba(255, 235, 59, 0.8)',   // Yellow
                'rgba(255, 152, 0, 0.8)',    // Orange
                'rgba(121, 85, 72, 0.8)',    // Brown
            ];

            servicesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Сумма',
                        data: data,
                        backgroundColor: backgroundColors.slice(0, data.length),
                        borderColor: backgroundColors.slice(0, data.length).map(color => color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return new Intl.NumberFormat('ru-RU').format(context.parsed.y) + ' сум';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('ru-RU').format(value);
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }
        let labTestsChart = null;
        
        function initLabTestChart() {
            const ctx = document.getElementById('labTestsChart');
            if (!ctx) return;
            
            // Destroy existing chart
            if (labTestsChart) {
                labTestsChart.destroy();
            }
            
            const chartData = {!! json_encode($chartData2) !!};
            
            if (!chartData || chartData.length === 0) {
                return;
            }
            
            const labels = chartData.map(item => {
                const sessions = item.sessions || 0;
                return item.name + ' (' + sessions + ')';
            });
            const data = chartData.map(item => parseFloat(item.amount) || 0);
            
            // Generate colors for each service
            const backgroundColors = [
                'rgba(54, 162, 235, 0.8)',   // Blue
                'rgba(255, 99, 132, 0.8)',   // Red  
                'rgba(255, 205, 86, 0.8)',   // Yellow
                'rgba(75, 192, 192, 0.8)',   // Teal
                'rgba(153, 102, 255, 0.8)',  // Purple
                'rgba(255, 159, 64, 0.8)',   // Orange
                'rgba(199, 199, 199, 0.8)',  // Gray
                'rgba(83, 102, 255, 0.8)',   // Indigo
                'rgba(255, 193, 7, 0.8)',    // Amber
                'rgba(76, 175, 80, 0.8)',    // Green
                'rgba(233, 30, 99, 0.8)',    // Pink
                'rgba(156, 39, 176, 0.8)',   // Deep Purple
                'rgba(63, 81, 181, 0.8)',    // Indigo
                'rgba(0, 188, 212, 0.8)',    // Cyan
                'rgba(0, 150, 136, 0.8)',    // Teal
                'rgba(139, 195, 74, 0.8)',   // Light Green
                'rgba(205, 220, 57, 0.8)',   // Lime
                'rgba(255, 235, 59, 0.8)',   // Yellow
                'rgba(255, 152, 0, 0.8)',    // Orange
                'rgba(121, 85, 72, 0.8)',    // Brown
            ];

            servicesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Сумма',
                        data: data,
                        backgroundColor: backgroundColors.slice(0, data.length),
                        borderColor: backgroundColors.slice(0, data.length).map(color => color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return new Intl.NumberFormat('ru-RU').format(context.parsed.y) + ' сум';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('ru-RU').format(value);
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }
        let amountChart = null;

        function initAmountChart() {
            const ctx = document.getElementById('amountChart');
            if (!ctx) return;
            
            // Destroy existing chart
            if (amountChart) {
                amountChart.destroy();
            }
            
            const chartData = {!! json_encode($chartDataAmount) !!};
            
            if (!chartData || chartData.length === 0) {
                return;
            }
            
            const labels = chartData.map(item => item.name);
            const data = chartData.map(item => parseFloat(item.amount) || 0);
            
            // Filter out zero values for logarithmic scale
            const filteredData = [];
            const filteredLabels = [];
            
            data.forEach((value, index) => {
                if (value > 0) {
                    filteredData.push(value);
                    filteredLabels.push(labels[index]);
                }
            });
            
            const backgroundColors = [
                'rgba(54, 162, 235, 0.8)',   // Blue
                'rgba(255, 99, 132, 0.8)',   // Red  
                'rgba(255, 205, 86, 0.8)',   // Yellow
                'rgba(75, 192, 192, 0.8)',   // Teal
                'rgba(153, 102, 255, 0.8)',  // Purple
                'rgba(255, 159, 64, 0.8)',   // Orange
            ];

            amountChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: filteredLabels,
                    datasets: [{
                        label: 'Сумма',
                        data: filteredData,
                        backgroundColor: backgroundColors.slice(0, filteredData.length),
                        borderColor: backgroundColors.slice(0, filteredData.length).map(color => color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return new Intl.NumberFormat('ru-RU').format(context.parsed.y) + ' сум';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'logarithmic',  // Logarithmic scale
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Сумма (Логарифмическая шкала)'
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }

        let incomesChart = null;
        function IncomeChart() {
            
            // Destroy existing chart
            if (incomesChart) {
                incomesChart.destroy();
            }
                const ctx = document.getElementById('incomeChart').getContext('2d');

                incomesChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json(array_keys($chartDataIncome)),
                        datasets: [{
                            label: 'Сумма',
                            data: @json(array_values($chartDataIncome)),
                            backgroundColor: 'rgba(54, 162, 235, 0.3)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            fill: true,
                            tension: 0.4,
                        }]
                    },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return new Intl.NumberFormat('ru-RU').format(context.parsed.y) + ' сум';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'logarithmic',  // Logarithmic scale
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Сумма (Логарифмическая шкала)'
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
                });
        }
        // Initialize chart when DOM is loaded
        document.addEventListener('DOMContentLoaded', function () {
            initChart();
            initAmountChart();
            initLabTestChart();
            IncomeChart();
        });

        // Print functionality
        document.addEventListener('livewire:navigated', function() {
            initAmountChart();
            initLabTestChart();
            initChart();
            IncomeChart();
        });

        // Livewire hooks for updating chart
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                if (component.name === 'app.filament.resources.income-resource.pages.income-report') {
                    setTimeout(() => {
                        initAmountChart();
                        initLabTestChart();
                        IncomeChart();
                        initChart();
                    }, 100);
                }
            });
        });

        // Print functionality
        window.addEventListener('print-report', function() {
            window.print();
        });
    </script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            .print-break {
                page-break-before: always;
            }
            
            body {
                font-size: 12px;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
            }
            
            th, td {
                border: 1px solid #000;
                padding: 4px;
            }
        }
    </style>
</x-filament::page>
