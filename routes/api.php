<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'loginApi']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/students', [StudentController::class, 'getStudentApi'])->name('api.students.getStudents');
Route::middleware('auth:sanctum')->get('/students', [StudentController::class, 'getStudentApi'])->name('api.students.getStudents');
Route::middleware('auth:sanctum')->get('/students/{id}', [StudentController::class, 'getStudentDetailsApi'])->name('api.students.getDetails');
Route::middleware('auth:sanctum')->post('/students', [StudentController::class, 'storeStudentApi']);
Route::put('students/{student}', [StudentController::class, 'updateStudentDetailsApi']);
Route::middleware('auth:sanctum')->delete('/students/{student}', [StudentController::class, 'destroyStudentApi']);

Route::get('/subjects', [SubjectController::class, 'getSubjectApi'])->name('api.subjects.getSubjects');
Route::middleware('auth:sanctum')->get('/subjects', [SubjectController::class, 'getSubjectApi'])->name('api.subjects.getSubjects');
Route::middleware('auth:sanctum')->get('/subjects/{id}', [SubjectController::class, 'getSubjectDetailsApi'])->name('api.subjects.getDetails');
Route::middleware('auth:sanctum')->post('/subjects', [SubjectController::class, 'storeSubjectApi']);
Route::put('subjects/{subject}', [SubjectController::class, 'updateSubjectDetailsApi']);
Route::middleware('auth:sanctum')->delete('/subjects/{subject}', [SubjectController::class, 'destroySubjectApi']);

Route::get('/sections', [SectionController::class, 'getSectionApi'])->name('api.sections.getSections');
Route::middleware('auth:sanctum')->get('/sections', [SectionController::class, 'getSectionApi'])->name('api.sections.getSections');
Route::middleware('auth:sanctum')->get('/sections/{id}', [SectionController::class, 'getSectionDetailsApi'])->name('api.sections.getDetails');
Route::middleware('auth:sanctum')->post('/sections', [SectionController::class, 'storeSectionApi']);
Route::put('sections/{section}', [SectionController::class, 'updateSectionDetailsApi']);
Route::middleware('auth:sanctum')->delete('/sections/{section}', [SectionController::class, 'destroySectionApi']);

Route::post('password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmailApi']);
Route::post('password/reset', [ResetPasswordController::class, 'resetPasswordApi']);