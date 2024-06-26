<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'body' => 'json',
        'response' => 'json',
        'headers' => 'json',
        'validation_issues' => 'json',
    ];
}
