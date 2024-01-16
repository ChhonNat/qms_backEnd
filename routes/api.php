<?php

use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CounterController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {
    // user
    Route::post('changePass', [AuthController::class, 'changePass'])->name('changePass');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('createUser', [UserController::class, 'user_store'])->name('createUser');
    Route::put('updateUser/{id}', [UserController::class, 'update_user'])->name('updateUser');
    Route::put('deleteUser/{id}', [UserController::class, 'delete_user'])->name('deleteUser');
    Route::get('roles', [RolesController::class, 'list_roles'])->name('roles');
    Route::get('user', [UserController::class, 'list_user'])->name('user');
    Route::get('counters', [CounterController::class, 'list_counter'])->name('counters');
    Route::get('', [CounterController::class, 'list_counter'])->name('counters');
    Route::get('permission', [PermissionController::class, 'list_permission'])->name('permission');

    // service
    Route::post('service', [ServiceController::class, 'store_service'])->name('service');
    Route::get('service/list', [ServiceController::class, 'list_service'])->name('service');
    Route::put('service/{id}', [ServiceController::class, 'update_service']);
    Route::put('service/delete/{id}', [ServiceController::class, 'delete_service']);

    // ticket
    Route::post('/ticket', [TicketController::class, 'store_ticket']);
    Route::get('/ticket/list', [TicketController::class, 'list_ticket']);

});
