<?php

namespace App\Filament\Resources\Devotionals\Pages;

use App\Filament\Resources\Devotionals\DevotionalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDevotional extends CreateRecord
{
    protected static string $resource = DevotionalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
