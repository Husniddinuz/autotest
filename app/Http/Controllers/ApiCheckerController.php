<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ApiCheckerController extends Controller
{
    public function index(): JsonResponse
    {
         return response()->json([
             'requests' => Request::all(),
             'response' => Http::get('https://jsonplaceholder.typicode.com/posts/1')
         ]);
    }
}
