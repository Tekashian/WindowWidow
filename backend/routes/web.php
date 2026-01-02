<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return [
        'message' => 'Window Factory API',
        'version' => '1.0.0',
        'docs' => '/api/documentation'
    ];
});
