<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadViewController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

Auth::routes();

// Redirect /home to /dashboard
Route::get('/home', function() {
    return redirect('/dashboard');
});

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Lead routes
Route::get('/leads', [LeadViewController::class, 'index'])->name('leads.index');
Route::get('/leads/create', [LeadViewController::class, 'create'])->name('leads.create');
Route::post('/leads', [LeadViewController::class, 'store'])->name('leads.store');
Route::get('/leads/{lead}', [LeadViewController::class, 'show'])->name('leads.show');
Route::get('/leads/{lead}/edit', [LeadViewController::class, 'edit'])->name('leads.edit');
Route::put('/leads/{lead}', [LeadViewController::class, 'update'])->name('leads.update');
Route::delete('/leads/{lead}', [LeadViewController::class, 'destroy'])->name('leads.destroy');

// Document routes
use App\Http\Controllers\DocumentViewController;
Route::get('/leads/{lead}/documents/create', [DocumentViewController::class, 'createForLead'])->name('documents.create');
Route::get('/leads/{lead}/activities/{activity}/documents/create', [DocumentViewController::class, 'createForActivity'])->name('documents.create.activity');
Route::post('/documents', [DocumentViewController::class, 'store'])->name('documents.store');
Route::get('/documents/{document}/download', [DocumentViewController::class, 'download'])->name('documents.download');
Route::delete('/documents/{document}', [DocumentViewController::class, 'destroy'])->name('documents.destroy');

// Activity routes
use App\Http\Controllers\ActivityViewController;
Route::get('/activities', [ActivityViewController::class, 'index'])->name('activities.index');
Route::get('/leads/{lead}/activities/create', [ActivityViewController::class, 'create'])->name('activities.create');
Route::post('/activities', [ActivityViewController::class, 'store'])->name('activities.store');
Route::get('/activities/{activity}/edit', [ActivityViewController::class, 'edit'])->name('activities.edit');
Route::put('/activities/{activity}', [ActivityViewController::class, 'update'])->name('activities.update');
Route::delete('/activities/{activity}', [ActivityViewController::class, 'destroy'])->name('activities.destroy');

// Agent routes (Legacy - kept for backward compatibility)
use App\Http\Controllers\AgentController;
Route::resource('agents', AgentController::class);

// User Management routes
use App\Http\Controllers\UserManagementController;
Route::resource('users', UserManagementController::class);

// Permission Management routes
use App\Http\Controllers\PermissionManagementController;
Route::get('/permissions', [PermissionManagementController::class, 'index'])->name('permissions.index');
Route::post('/permissions/roles/{role}', [PermissionManagementController::class, 'updateRolePermissions'])->name('permissions.updateRole');

// Report routes
use App\Http\Controllers\ReportController;
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

// EPC routes
use App\Http\Controllers\EpcController;
Route::post('/leads/{lead}/epc/fetch', [EpcController::class, 'fetchForLead'])->name('epc.fetch');
Route::post('/leads/{lead}/epc/save', [EpcController::class, 'saveSelectedCertificate'])->name('epc.save');
Route::delete('/leads/{lead}/epc', [EpcController::class, 'clearForLead'])->name('epc.clear');
Route::post('/leads/{lead}/epc/recommendations', [EpcController::class, 'fetchRecommendations'])->name('epc.recommendations');

// ECO4 Calculator routes (web-based, uses session auth)
use App\Http\Controllers\Api\Eco4CalculatorController;
Route::prefix('eco4')->middleware('auth')->group(function () {
    Route::get('/metadata', [Eco4CalculatorController::class, 'metadata'])->name('eco4.metadata');
    Route::post('/calculate', [Eco4CalculatorController::class, 'calculate'])->name('eco4.calculate');
    Route::post('/leads/{lead}/save', [Eco4CalculatorController::class, 'save'])->name('eco4.save');
    Route::get('/leads/{lead}', [Eco4CalculatorController::class, 'getByLead'])->name('eco4.getByLead');
    Route::delete('/calculations/{calculation}', [Eco4CalculatorController::class, 'delete'])->name('eco4.delete');
});

// Settings routes (admin only)
use App\Http\Controllers\SettingsController;

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/{setting}', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/api/update', [SettingsController::class, 'apiUpdate'])->name('settings.api.update');


Route::middleware('auth')->group(function () {
    Route::get('/settings/api', [SettingsController::class, 'api'])->name('settings.api');
});

// Import routes
use App\Http\Controllers\ImportController;
Route::middleware('auth')->prefix('import')->group(function () {
    Route::get('/', [ImportController::class, 'index'])->name('import.index');
    Route::post('/sheets/list', [ImportController::class, 'listSheets'])->name('import.sheets.list');
    Route::post('/sheets/info', [ImportController::class, 'getSheetInfo'])->name('import.sheets.info');
    Route::post('/sheets/preview', [ImportController::class, 'previewSheet'])->name('import.sheets.preview');
    Route::post('/leads', [ImportController::class, 'importLeads'])->name('import.leads');
});
