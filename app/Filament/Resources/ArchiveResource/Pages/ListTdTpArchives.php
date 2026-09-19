<?php

namespace App\Filament\Resources\ArchiveResource\Pages;

use App\Filament\Resources\ArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTdTpArchives extends ListRecords
{
    protected static string $resource = ArchiveResource::class;

    protected static ?string $title = 'TD / TP';

    protected static ?string $navigationLabel = 'TD / TP';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationParentItem = 'Archives';

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ?->where('type', 'td_tp');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->url(
                    fn () => ArchiveResource::getUrl(
                        'create',
                        ['type' => 'td_tp']
                    )
                ),
        ];
    }
}
