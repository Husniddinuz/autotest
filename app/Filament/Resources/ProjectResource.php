<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('production_url')
                    ->required()
                    ->label('Production URL'),
                Forms\Components\TextInput::make('development_url')
                    ->required()
                    ->label('Development URL'),
                Forms\Components\Select::make('environment')
                    ->options([
                        0 => 'Development',
                        1 => 'Production'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('version'),
                Forms\Components\TextInput::make('username'),
                Forms\Components\TextInput::make('password')
                    ->password(),
                Forms\Components\TextInput::make('token'),


                Forms\Components\Repeater::make('variables')
                    ->relationship('variables')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('value')
                            ->required(),
                    ])->columns(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('environment')
                    ->badge()
                    ->getStateUsing( function (Model $record){
                        return $record->environment === 1 ? 'Production' : 'Development';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Development' => 'info',
                        'Production' => 'success',
                    })
                    ->searchable(),

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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
