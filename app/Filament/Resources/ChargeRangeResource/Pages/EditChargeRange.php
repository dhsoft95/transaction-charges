<?php

namespace App\Filament\Resources\ChargeRangeResource\Pages;

use App\Filament\Resources\ChargeRangeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChargeRange extends EditRecord
{
    protected static string $resource = ChargeRangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
