<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\QrController;
use App\Http\Controllers\API\AuthController;

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
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',[AuthController::class , 'login']);

Route::post('/Qr',[QrController::class , 'store']);
Route::get('/Qr',[QrController::class , 'index']);
Route::put('/Qr/{value}',[QrController::class , 'update']); //the Qr value itself 

// Route::post('/Qr', function (Request $request) {
//     // Access the Qr data sent as form data
//     $QrData = $request->input('Qr');
    
//     if ($QrData) {
//         // Log it, save to database, etc.
//         \Illuminate\Support\Facades\Log::info('Qr received: ' . $QrData);

//         return response()->json([
//             'message' => 'Qr received successfully!',
//             'received_Qr' => $QrData
//         ], 200);
//     } else {
//         return response()->json(['error' => 'No Qr data received'], 400);
//     }
// });

Route::middleware('auth:sanctum')->group( function () {

  
});

