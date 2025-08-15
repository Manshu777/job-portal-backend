<?php

use App\Http\Controllers\AllCandidateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\JobDescriptionController;

use App\Http\Controllers\API\CandidateController;
use App\Http\Controllers\API\CandidateLanguageController;
use App\Http\Controllers\API\CandidateEducationController;
use App\Http\Controllers\API\CandidateExperienceController;
use App\Http\Controllers\API\CandidateSkillController;

use App\Http\Controllers\API\AuthController;

use App\Http\Controllers\API\JobPostController;
use App\Http\Controllers\API\EmployerAuthController;
use App\Http\Controllers\API\JobTitleController;

use App\Http\Controllers\API\CitiesController;
use App\Http\Controllers\API\QualificationsController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::prefix('v1')->group(function () {
    Route::apiResource('candidate', CandidateController::class);
    Route::get('/candidateprofile', [AuthController::class, 'profile']);

    Route::apiResource('candidate/lan', CandidateLanguageController::class);
    Route::apiResource('candidate/skills', CandidateSkillController::class);
    Route::apiResource('candidate/exp', CandidateExperienceController::class);
    Route::apiResource('candidate/edu', CandidateEducationController::class);
    Route::get('/filter', [CandidateController::class, 'filter']);
      Route::post('/reveal-number', [CandidateController::class, 'revealNumber']);
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('employer/signup', [EmployerAuthController::class, 'signup']);
    Route::post('employer/send-otp', [EmployerAuthController::class, 'sendOtp']);

     Route::get('/job-titles/search', [JobTitleController::class, 'search']);
    Route::post('employer/verify-otp', [EmployerAuthController::class, 'verifyOtp']);
    Route::post('employer/login', [EmployerAuthController::class, 'login']);
    Route::get('/jobs/employer/{id}', [JobPostController::class, 'getByEmployer']);
    Route::get('/jobs', [JobPostController::class, 'index']);
    Route::get('/job-titles', [JobPostController::class, 'getJobTitles']);
    Route::post('/add-companies', [EmployerAuthController::class, 'addCompany']);
    Route::get('/companies', [EmployerAuthController::class, 'listCompanies']);
    Route::middleware('auth:sanctum')->post('employer/update', [EmployerAuthController::class, 'updateEmployer']);
    Route::middleware('auth:sanctum')->get('employer/profile', [EmployerAuthController::class, 'profile']);
    Route::middleware('auth:employer-api')->post('job-posts', [JobPostController::class, 'store']);


    Route::put('/jobs/{jobId}/refresh', [JobPostController::class, 'refreshJob']);


    Route::put('/jobs/{jobId}', [JobPostController::class, 'update']);


    Route::delete('/jobs/{jobId}', [JobPostController::class, 'destroy']);


    
});

Route::prefix('v1/cities')->group(function () {
   Route::get('/', [CitiesController::class, 'index']);
    // Route::get('/{id}', [CitiesController::class, 'show']);
    Route::post('/', [CitiesController::class, 'store']);
    Route::put('/{id}', [CitiesController::class, 'update']);
    Route::delete('/{id}', [CitiesController::class, 'destroy']);

    Route::get('/search', [CitiesController::class, 'search']);

        Route::get('/{cityId}/locations', [CitiesController::class, 'searchLocations']);
});

Route::prefix('v1/qualifications')->group(function () {
    Route::get('/', [QualificationsController::class, 'index']);
    // Route::get('/{id}', [QualificationsController::class, 'show']);
    Route::post('/', [QualificationsController::class, 'store']);
  Route::get('/education-level/{level}', [QualificationsController::class, 'getByEducationLevel']);

    Route::put('/{id}', [QualificationsController::class, 'update']);
    Route::delete('/{id}', [QualificationsController::class, 'destroy']);
    Route::get('/search', [QualificationsController::class, 'search']);
    Route::get('/{qualificationId}/specializations', [QualificationsController::class, 'specializations']);
});
Route::post('v1/generate-job-description', [JobDescriptionController::class, 'generateJobDescription']);
Route::post("v1/createcandidate", [AllCandidateController::class, "CreateCandidate"]);

Route::post("v1/updatecandidate/{token}",[AllCandidateController::class,"AddCandidateInfo"]);
Route::get("v1/candidateinfo/{token}",[AllCandidateController::class,"getCandidateinfo"]);
Route::post("v1/candidate/login",[AllCandidateController::class,"loginbypasswod"]);
