<?php

namespace App\Filament\Resources\RequestResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Novadaemon\FilamentPrettyJson\PrettyJson;

class ResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'responses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                PrettyJson::make('response'),
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->columns([
                Tables\Columns\TextColumn::make('message')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing( function (Model $record){
                        return $record->status === 1 ? 'Passed' : 'Failed';
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Passed' => 'heroicon-s-check-circle',
                        'Failed' => 'heroicon-s-x-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Passed' => 'success',
                        'Failed' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('environment')
                    ->badge()
                    ->getStateUsing( function (Model $record){
                        return $record->environment === 1 ? 'Production' : 'Development';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Development' => 'info',
                        'Production' => 'success',
                    }),
                Tables\Columns\TextColumn::make('status_code')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-s-stop'),
                Tables\Columns\TextColumn::make('response_time')
                    ->badge()
                    ->color( function (Model $record){
                        if($record->response_time < 200){
                            return 'success';
                        } elseif ($record->response_time < 1000){
                            return 'info';
                        } else {
                            return 'danger';
                        }
                    })
                    ->getStateUsing( function (Model $record){
                        return $record->response_time . ' ms';
                    })
                    ->icon('heroicon-s-clock'),
            ])
            ->filters([
                //
            ])
            ->headerActions([

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
