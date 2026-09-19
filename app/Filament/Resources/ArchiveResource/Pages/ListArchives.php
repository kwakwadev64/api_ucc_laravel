<?php

namespace App\Filament\Resources\ArchiveResource\Pages;

use App\Filament\Resources\ArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListArchives extends ListRecords
{
    protected static string $resource = ArchiveResource::class;

    public function getTabs(): array
    {
        return [
            'exam' => Tab::make('Anciens examens')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('type', 'exam')
                ),

            'td_tp' => Tab::make('TD / TP')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('type', 'td_tp')
                ),

            'interro' => Tab::make('Interrogations')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('type', 'interro')
                ),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
