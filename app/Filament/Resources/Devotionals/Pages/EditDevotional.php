<?php

namespace App\Filament\Resources\Devotionals\Pages;

use App\Filament\Resources\Devotionals\DevotionalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDevotional extends EditRecord
{
    protected static string $resource = DevotionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
