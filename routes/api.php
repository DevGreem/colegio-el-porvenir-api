<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return json_encode(array('Welcome' => 'Lol'));
});

Route::get('/health', function () {
    return json_encode(array('status' => 'OK'));
});

Route::get('/students', function () {
    DB::table('students')->get();
});

?>
