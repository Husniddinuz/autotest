<?php

namespace App\Filament\Resources\RequestResource\Pages;

use App\Filament\Resources\RequestResource;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Http;

class ViewRequest extends ViewRecord
{
    protected static string $resource = RequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Test this request')
                ->icon('heroicon-o-bolt')
                ->color('info')
                ->form([
                    Select::make('environment')
                        ->options([
                            'production_url' => 'Production',
                            'development_url' => 'Development',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $req = $this->record;
                    $project = $req->project;
                    $url = $project->{$data['environment']};
                    $version = $project->version;
                    $method = $req->method;
                    $route = $req->route;
                    $body = json_decode($req->body, true);
                    $start = microtime(true);
                    $response = Http::withHeaders([
                        'Accept' => 'application/json'
                    ])->{$method}("{$url}/{$version}/{$route}", $body);

                    $duration = floor((microtime(true) - $start) * 1000);
                    $status = ($response->status() === $req->status_code) ? 1 : 0;
                    $req->responses()->create([
                        'status_code' => $response->status(),
                        'response' => $response->json(),
                        'body' => $req->body,
                        'headers' => json_encode($response->headers()),
                        'status' => $status,
                        'response_time' => $duration,
                        'environment' => ($data['environment'] === 'production_url') ? 1 : 0,
                        'message' => $status ? 'Test passed successfully' : 'Test failed due to status code mismatch.'
                    ]);

                    if($req->save_token){
                        $token = $response->json($req->token_path);
                        $project->update([
                            'token' => $token
                        ]);
                    }

                    if($status){
                        Notification::make()
                            ->title('Test successful')
                            ->body('The response has been saved successfully.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Test failed')
                            ->body('The response has been saved successfully.')
                            ->danger()
                            ->send();
                    }
                }),
            Actions\EditAction::make(),
        ];
    }
}
