<?php

namespace App\Filament\Resources\Devotionals\Pages;

use App\Filament\Resources\Devotionals\DevotionalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDevotionals extends ListRecords
{
    protected static ?string $title = 'Devocionais';

    protected ?string $subheading = 'Lista dos resumos já cadastrados';

    protected static string $resource = DevotionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
