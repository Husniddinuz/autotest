<?php

namespace App\Filament\Resources\TestCaseResource\Pages;

use App\Filament\Resources\TestCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTestCase extends CreateRecord
{
    protected static string $resource = TestCaseResource::class;
}
