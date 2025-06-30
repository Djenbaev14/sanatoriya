<?php

namespace App\Filament\Resources\ProfilResource\Pages;

use App\Filament\Resources\ProfilResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditProfil extends EditRecord
{
    protected static string $resource = ProfilResource::class;

    // protected function getRedirectUrl(): string
    // {
    //     return static::$resource::getUrl();
    // }

    // protected function resolveRecord($key): \Illuminate\Database\Eloquent\Model
    // {
    //     return \Auth::user();
    // }
    protected function authorizeAccess(): void
    {
        if (auth()->id() !== $this->record->id) {
            abort(403);
        }
    }





}
