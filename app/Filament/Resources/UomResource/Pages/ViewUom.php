<?php

namespace App\Filament\Resources\UomResource\Pages;

use App\Filament\Resources\UomResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUom extends ViewRecord
{
    protected static string $resource = UomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
