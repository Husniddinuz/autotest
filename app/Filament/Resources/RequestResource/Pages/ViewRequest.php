<?php
namespace App\Filament\Resources\RequestResource\Pages;

use App\Filament\Resources\RequestResource;
use App\Models\Variable;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                    $route = $this->replacePlaceholders($req->route);
                    $body = $this->replacePlaceholders(json_decode($req->body, true));

                    $start = microtime(true);


                    $response = Http::withHeaders([
                        'Accept' => 'application/json'
                    ])->{$method}("{$url}/{$version}/{$route}", $body);

                    $validations = $req->validations;

                    $resBody = json_decode($response);

                    $validationResults = [];
                    $validationChecks = [
                        'string' => 'is_string',
                        'integer' => 'is_int',
                        'array' => 'is_array',
                        'object' => 'is_object',
                        'boolean' => 'is_bool',
                    ];

                    foreach ($validations as $validation) {
                        $type = $validation->type;
                        $key = $validation->key;
                        $value = $resBody->{$key};

                        if (isset($validationChecks[$type]) && $validationChecks[$type]($value)) {
                            $validationResults[$key] = "Passed: '$key' is valid $type.";
                        } else {
                            $validationResults[$key] = "Failed: '$key' should be $type, but a " . gettype($value) . " was provided.";
                        }
                    }


                    $duration = floor((microtime(true) - $start) * 1000);
                    $status = ($response->status() === $req->status_code) ? 1 : 0;

                    $req->responses()->create([
                        'status_code' => $response->status(),
                        'response' => $response->json(),
                        'body' => $body,
                        'headers' => $response->headers(),
                        'status' => $status,
                        'response_time' => $duration,
                        'validation_issues' => $validationResults,
                        'environment' => ($data['environment'] === 'production_url') ? 1 : 0,
                        'message' => $status ? 'Test passed successfully' : 'Test failed due to status code mismatch.'
                    ]);

                    if ($req->save_token) {
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
            Actions\Action::make('generate_validation')
                ->icon('heroicon-o-bolt')
                ->color('info')
                ->form([
                    Textarea::make('response')
                        ->hint('The response body to generate validations from. This should be a valid JSON response data.')
                        ->required()

                ])
                ->action(function (array $data) {
                    $response = json_decode($data['response']);

                    $validations = [];
                    foreach ($response as $key => $value) {
                        $type = gettype($value);
                        $validations[] = [
                            'key' => $key,
                            'type' => match ($type) {
                                'string' => 'string',
                                'integer' => 'integer',
                                'boolean' => 'boolean',
                                'array' => 'array',
                                'object' => 'object',
                                default => 'string',
                            }
                        ];
                    }
                    $this->record->validations()->delete();
                    $this->record->validations()->createMany($validations);

                    Notification::make()
                        ->title('Validations generated')
                        ->body('The validations have been generated successfully.')
                        ->success()
                        ->send();
                }),


            Actions\EditAction::make(),
        ];
    }

    private function replacePlaceholders($input): array|string
    {
        $placeholders = $this->getPlaceholders($input);
        $variables = Variable::query()->whereIn('name', $placeholders)->pluck('value', 'name')->toArray();
        $replacePairs = array_map(fn($name) => '{' . $name . '}', array_keys($variables));
        return str_replace($replacePairs, array_values($variables), $input);
    }

    private function getPlaceholders($input): array
    {
        preg_match_all('/\{(\w+)\}/', is_array($input) ? json_encode($input) : $input, $matches);
        return $matches[1];
    }
}
