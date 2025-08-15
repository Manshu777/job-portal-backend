<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



use App\Http\Controllers\CandidateImportController;

Route::get('/candidates/template', [CandidateImportController::class, 'downloadTemplate'])->name('candidatetemp.template');

