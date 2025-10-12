<?php

use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\Eco4CalculatorController;
use App\Http\Controllers\Api\LeadActionController;
use App\Http\Controllers\Api\LeadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Protected routes - require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Lead Routes
    Route::prefix('leads')->group(function () {
        // Basic CRUD operations
        Route::get('/', [LeadController::class, 'index'])->middleware('permission:lead.view');
        Route::post('/', [LeadController::class, 'store'])->middleware('permission:lead.create');
        Route::get('/{id}', [LeadController::class, 'show'])->middleware('permission:lead.view');
        Route::put('/{id}', [LeadController::class, 'update'])->middleware('permission:lead.edit');
        Route::delete('/{id}', [LeadController::class, 'destroy'])->middleware('permission:lead.delete');
        
        // Lead actions
        Route::post('/{id}/notes', [LeadActionController::class, 'addNote'])->middleware('permission:activity.create');
        Route::post('/{id}/status', [LeadActionController::class, 'changeStatus'])->middleware('permission:lead.edit');
        Route::post('/{id}/stage', [LeadActionController::class, 'changeStage'])->middleware('permission:lead.edit');
        Route::post('/{id}/book-installation', [LeadActionController::class, 'bookInstallation'])->middleware('permission:lead.edit');
        Route::post('/{id}/complete-installation', [LeadActionController::class, 'completeInstallation'])->middleware('permission:lead.edit');
        
        // Documents
        Route::get('/{leadId}/documents', [DocumentController::class, 'index'])->middleware('permission:document.view');
        Route::post('/{leadId}/documents/presigned-url', [DocumentController::class, 'getPresignedUrl'])->middleware('permission:document.upload');
        Route::post('/{leadId}/documents', [DocumentController::class, 'store'])->middleware('permission:document.upload');
    });
    
    // Document Routes
    Route::prefix('documents')->group(function () {
        Route::get('/{id}/download', [DocumentController::class, 'getDownloadUrl'])->middleware('permission:document.view');
        Route::delete('/{id}', [DocumentController::class, 'destroy'])->middleware('permission:document.delete');
    });
    
    // ECO4 Calculator Routes
    Route::prefix('eco4')->group(function () {
        Route::get('/metadata', [Eco4CalculatorController::class, 'metadata']);
        Route::post('/calculate', [Eco4CalculatorController::class, 'calculate']);
        Route::post('/leads/{lead}/save', [Eco4CalculatorController::class, 'save'])->middleware('permission:lead.edit');
        Route::get('/leads/{lead}', [Eco4CalculatorController::class, 'getByLead'])->middleware('permission:lead.view');
        Route::delete('/calculations/{calculation}', [Eco4CalculatorController::class, 'delete'])->middleware('permission:lead.edit');
    });
    
    // Admin Routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::post('/dedupe', function (Request $request) {
            \Artisan::call('app:deduplicate-leads');
            return response()->json(['message' => 'Deduplication process started']);
        });
        
        Route::post('/import', function (Request $request) {
            // Validate file upload
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
            ]);
            
            // Store uploaded file
            $path = $request->file('file')->store('imports');
            $fullPath = storage_path('app/' . $path);
            
            // Run import command
            $exitCode = \Artisan::call('app:import-leads', [
                'file' => $fullPath,
                '--user-id' => $request->user()->id,
            ]);
            
            $output = \Artisan::output();
            
            return response()->json([
                'message' => 'Import completed',
                'output' => $output,
                'exit_code' => $exitCode,
            ]);
        });
    });
});
