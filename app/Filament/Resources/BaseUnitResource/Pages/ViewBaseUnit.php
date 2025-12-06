<?php

namespace App\Filament\Resources\BaseUnitResource\Pages;

use App\Filament\Resources\BaseUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBaseUnit extends ViewRecord
{
    protected static string $resource = BaseUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
