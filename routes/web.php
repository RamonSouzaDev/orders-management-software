<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Frontend da aplicação
|
*/

Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});
