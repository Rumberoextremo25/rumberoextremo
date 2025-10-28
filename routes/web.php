<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CommercialAllyController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\AllyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\PayoutController;

//RUTAS DE LAS VISTAS DE LA LANDING UBICADAS EN EL CONTROLADOR DE PAGE
Route::get('/', [PageController::class, 'index'])->name('welcome');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/demo', [PageController::class, 'demo'])->name('demo');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/faq', [PageController::class, 'faqs'])->name('faq');

//Rutas para la vista de afiliados
Route::get('/demo-afiliado', [PageController::class, 'afiliado'])->name('demo.afiliado');
Route::post('/afiliados', [PageController::class, 'storeAffiliateApplication'])->name('affiliate.store');
Route::get('/demo-aliados', [PageController::class, 'aliado'])->name('demo.aliado');
Route::post('/contacto-aliados', [PageController::class, 'storeAllyContact'])->name('allies.store');
// RUTAS PARA LA VISTA DE CONTACTO
Route::get('/contact', [PageController::class, 'showContactForm'])->name('contact');
Route::post('/contact', [PageController::class, 'storeContactMessage'])->name('contact.store');
// Ruta para el Newsletter
Route::post('/newsletter/subscribe', [PageController::class, 'subscribeToNewsletter'])->name('newsletter.subscribe');




// RUTAS PARA EL APARTADO DEL ADMIN
Route::middleware(['auth', 'verified'])->group(function () {
    // Si quieres que el dashboard sea la página de inicio al loguearse
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
});

// Rutas para los Usuarios
Route::get('/admin/users', [AdminController::class, 'usersIndex'])->name('users');

// Ruta para mostrar el formulario de añadir nuevo usuario
Route::get('/admin/users/create', [AdminController::class, 'create'])->name('add-user');
Route::post('/admin/users', [AdminController::class, 'store'])->name('users.store');

// Rutas para ver, editar y eliminar un usuario específico
Route::get('/admin/users', [UserController::class, 'usersIndex'])->name('users'); // Note: 'users' is not a standard resource name for index
Route::get('/admin/users/create', [UserController::class, 'create'])->name('add-user');
Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');
Route::get('/admin/users/{user}', [UserController::class, 'show'])->name('users.show');
Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

// RUTAS PARA LA VISTA DE ALIADOS
Route::get('/admin/aliados', [AllyController::class, 'index'])->name('aliados.index');
Route::get('/admin/aliados/create', [AllyController::class, 'aliadosCreate'])->name('aliados.create');
Route::post('/admin/aliados', [AllyController::class, 'storeAlly'])->name('aliados.store');
Route::get('/admin/aliados/{ally}/edit', [AllyController::class, 'alliesEdit'])->name('aliado.edit');
Route::put('/admin/aliados/{ally}', [AllyController::class, 'updateAlly'])->name('aliados.update');
Route::delete('/admin/aliados/{ally}', [AllyController::class, 'destroyAlly'])->name('aliados.destroy');
Route::get('/get-subcategories', [AllyController::class, 'getSubcategories'])->name('get.subcategories');


// Rutas para reportes de ventas
Route::prefix('admin')->name('admin.')->group(function () {
    // Reportes
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/sales/data', [ReportController::class, 'salesData'])->name('reports.sales.data');
    Route::post('/reports/sales/export', [ReportController::class, 'exportSales'])->name('reports.sales.export');
});

// Rutas para el Perfil (asumiendo que es el perfil del admin logueado)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update'); // O Route::post
    Route::get('/password/change', [ProfileController::class, 'changePassword'])->name('password.change');
});

Route::middleware(['auth',])->prefix('admin')->group(function () {
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings/password', [AdminController::class, 'changePassword'])->name('admin.password.change');

});

//Rutas para carga de Aliados comerciales, Promocion y Banners
    // Asumo que ya tienes un sistema de autenticación de Laravel (ej. Breeze, Jetstream, o auth scaffolding)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard principal
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas para Banners
    Route::resource('banners', BannerController::class);

    // Rutas para Aliados Comerciales
    Route::resource('commercial-allies', CommercialAllyController::class);

    // Rutas para Promociones
    Route::resource('promotions', PromotionController::class);
});

// Rutas de administración para payouts
// Rutas de administración para payouts
Route::prefix('admin')->name('admin.')->group(function () {
    // Página principal de payouts
    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    
    // Payouts pendientes
    Route::get('/payouts/pending', [PayoutController::class, 'pending'])->name('payouts.pending');
    
    // Generar archivo BNC
    Route::post('/payouts/generate-bnc', [PayoutController::class, 'generateBncFile'])->name('payouts.generate_bnc');
    
    // Confirmar pagos
    Route::post('/payouts/confirm', [PayoutController::class, 'confirmPayouts'])->name('payouts.confirm');
    
    // Descargar archivo BNC
    Route::get('/payouts/download-bnc/{archivo}', [PayoutController::class, 'downloadBncFile'])->name('payouts.download_bnc');
    
    // Revertir pago
    Route::post('/payouts/revert/{payoutId}', [PayoutController::class, 'revertPayout'])->name('payouts.revert');
    
    // Eliminar archivo
    Route::delete('/payouts/files/{archivo}', [PayoutController::class, 'deleteFile'])->name('payouts.delete_file');
    
    // Rutas AJAX para datos
    Route::get('/payouts/stats', [PayoutController::class, 'getStats'])->name('payouts.stats');
    Route::get('/payouts/files', [PayoutController::class, 'getFiles'])->name('payouts.files');
});

// O si prefieres resource routes con rutas adicionales:
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('payouts', PayoutController::class)->only([
        'index', 'show', 'create', 'store'
    ]);
    
    // Rutas adicionales para payouts
    Route::get('payouts/pending', [PayoutController::class, 'pending'])->name('payouts.pending');
    Route::post('payouts/generate-bnc', [PayoutController::class, 'generateBncFile'])->name('payouts.generate_bnc');
    Route::post('payouts/confirm', [PayoutController::class, 'confirmPayouts'])->name('payouts.confirm');
    Route::get('payouts/download-bnc/{archivo}', [PayoutController::class, 'downloadBncFile'])->name('payouts.download_bnc');
    Route::post('payouts/revert/{payoutId}', [PayoutController::class, 'revertPayout'])->name('payouts.revert');
    Route::get('payouts/stats', [PayoutController::class, 'getStats'])->name('payouts.stats');
});

require __DIR__ . '/auth.php';
