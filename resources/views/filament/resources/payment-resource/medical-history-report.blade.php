<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}
        
        <div class="filament-stats-overview-widgets-container">
            @foreach ($this->getHeaderWidgets() as $widget)
                @livewire(\Livewire\Livewire::getAlias($widget), ['filters' => $this->getFilters()])
            @endforeach
        </div>
    </div>
</x-filament-panels::page>