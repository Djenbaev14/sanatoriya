<?php

namespace App\Filament\Resources\CasheBoxSessionResource\Pages;

use App\Filament\Resources\CasheBoxSessionResource;
use App\Models\BankTransfer;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCasheBoxSessions extends ListRecords
{
    protected static string $resource = CasheBoxSessionResource::class;

}
