<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CommercialAllyController;
use App\Http\Controllers\Admin\PromotionController;

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
Route::get('/admin/users/{user}', [AdminController::class, 'show'])->name('users.show');
Route::get('/admin/users/{user}/edit', [AdminController::class, 'edit'])->name('users.edit');
Route::put('/admin/users/{user}', [AdminController::class, 'update'])->name('users.update');
Route::delete('/admin/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');

// RUTAS PARA LA VISTA DE ALIADOS
Route::get('/aliados', [AdminController::class, 'indexAllies'])->name('aliados.index');
Route::get('/aliados/crear', [AdminController::class, 'aliadosCreate'])->name('aliados.create');
Route::post('/aliados', [AdminController::class, 'storeAlly'])->name('aliados.store');
Route::get('/aliados/{ally}/editar', [AdminController::class, 'alliesEdit'])->name('aliado.edit');
Route::put('/aliados/{ally}', [AdminController::class, 'updateAlly'])->name('aliados.update');
Route::delete('/aliados/{ally}', [AdminController::class, 'destroyAlly'])->name('aliados.destroy');
Route::get('/get-subcategories', [AdminController::class, 'getSubcategories'])->name('get.subcategories');


// Rutas para reportes de ventas
Route::get('/reports/sales', [AdminController::class, 'reports'])->name('reports.sales');
//Route::get('/reports/sales/pdf', [SalesController::class, 'downloadPdf'])->name('reports.sales.pdf');

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

require __DIR__ . '/auth.php';
