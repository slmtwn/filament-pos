<?php

namespace App\Filament\Resources\BaseUnitResource\Pages;

use App\Filament\Resources\BaseUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBaseUnit extends EditRecord
{
    protected static string $resource = BaseUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
