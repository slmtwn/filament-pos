<?php

namespace App\Filament\Resources\BaseUnitResource\Pages;

use App\Filament\Resources\BaseUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBaseUnit extends CreateRecord
{
    protected static string $resource = BaseUnitResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
