<?php

namespace App\Filament\Resources\TestCaseResource\Pages;

use App\Filament\Resources\TestCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTestCases extends ListRecords
{
    protected static string $resource = TestCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('settings')
                ->icon('heroicon-o-shield-check')
                ->color('success')
                ->label('Deep test'),
            Actions\CreateAction::make(),
        ];
    }
}
