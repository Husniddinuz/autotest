<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestCaseResource\Pages;
use App\Filament\Resources\TestCaseResource\RelationManagers;
use App\Models\Project;
use App\Models\Request;
use App\Models\TestCase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TestCaseResource extends Resource
{
    protected static ?string $model = TestCase::class;

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->hint('Ticket name or case description')
                    ->required(),
                Forms\Components\Select::make('project_id')
                    ->label('Project')
                    ->searchable()
                    ->options(fn () => Project::all()->pluck('name', 'id'))
                    ->required(),
                Forms\Components\Repeater::make('testCaseRequests')
                    ->relationship('testCaseRequests')
                    ->reorderable()
                    ->orderColumn('order')
                    ->schema([
                        Forms\Components\Select::make('request_id')
                            ->label('Request')
                            ->searchable()
                            ->options(fn () => Request::all()->pluck('name', 'id')),
                        Forms\Components\TextInput::make('execution_count')
                            ->numeric()
                            ->default(1)
                            ->required()
                    ])->columns()
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestCases::route('/'),
            'create' => Pages\CreateTestCase::route('/create'),
            'view' => Pages\ViewTestCase::route('/{record}'),
            'edit' => Pages\EditTestCase::route('/{record}/edit'),
        ];
    }
}
