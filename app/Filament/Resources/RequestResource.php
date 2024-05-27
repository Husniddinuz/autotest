<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequestResource\Pages;
use App\Filament\Resources\RequestResource\RelationManagers;
use App\Models\Project;
use App\Models\Request;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Novadaemon\FilamentPrettyJson\PrettyJson;
use Filament\Notifications\Notification;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket-square';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('project_id')
                    ->label('Project')
                    ->options(fn () => Project::all()->pluck('name', 'id')),
                Forms\Components\Select::make('method')
                    ->options([
                        'GET' => 'GET',
                        'POST' => 'POST',
                        'PUT' => 'PUT',
                        'PATCH' => 'PATCH',
                        'DELETE' => 'DELETE',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('route')
                    ->required(),

                ($form->getOperation() !== 'view')
                    ?
                        Forms\Components\Textarea::make('body')
                    :
                        PrettyJson::make('body'),

                Forms\Components\TextInput::make('status_code')
                    ->numeric()
                    ->hint('Which kinda status code returns if this test case is successful?')
                    ->required(),
                Forms\Components\Toggle::make('token_needed'),
                Forms\Components\Toggle::make('save_token')->live(),
                Forms\Components\TextInput::make('token_path')
                    ->visible(fn(Get $get):bool => $get('save_token'))

                    ->hint('The path to the token in the response body'),
                Forms\Components\Repeater::make('validations')
                    ->relationship('validations')
                    ->schema([
                        Forms\Components\TextInput::make('key'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'string' => 'String',
                                'integer' => 'Integer',
                                'boolean' => 'Boolean',
                                'array' => 'Array',
                                'object' => 'Object',
                            ]),
                    ])->columns()
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('route')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ReplicateAction::make()
                    ->color('success')
                    ->modal(false)
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Request replicated')
                            ->body('The request has been replicated successfully.'),
                    ),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
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
            RelationManagers\ResponsesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
            'create' => Pages\CreateRequest::route('/create'),
            'view' => Pages\ViewRequest::route('/{record}'),
            'edit' => Pages\EditRequest::route('/{record}/edit'),
        ];
    }
}
