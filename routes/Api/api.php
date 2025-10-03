<?php

use App\Http\Controllers\Api\ExampleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ExampleController::class, 'index']);
