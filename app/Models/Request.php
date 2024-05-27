<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Request extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'body' => 'json'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function variables(): HasMany
    {
        return $this->hasMany(Variable::class);
    }
}
