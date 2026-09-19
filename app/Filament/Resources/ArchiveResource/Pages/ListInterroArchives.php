<?php

namespace App\Filament\Resources\ArchiveResource\Pages;

use App\Filament\Resources\ArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInterroArchives extends ListRecords
{
    protected static string $resource = ArchiveResource::class;

    protected static ?string $title = 'Interrogations';

    protected static ?string $navigationLabel = 'Interrogations';

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationParentItem = 'Archives';

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ?->where('type', 'interro');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->url(
                    fn () => ArchiveResource::getUrl(
                        'create',
                        ['type' => 'interro']
                    )
                ),
        ];
    }
}
