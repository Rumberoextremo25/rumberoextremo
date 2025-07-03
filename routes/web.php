<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

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


Route::get('/reportes', [AdminController::class, 'reports'])->name('reportes');
Route::get('/settings', [AdminController::class, 'settings'])->name('settings');

// Rutas para los Aliados
Route::get('/aliados', [AdminController::class, 'aliadosIndex'])->name('aliado');
Route::get('/aliados/crear', [AdminController::class, 'aliadosCreate'])->name('create');
// Para rutas de actualización, es buena práctica usar un parámetro en la URL
Route::get('/aliados/{id}/editar', [AdminController::class, 'aliadosUpdate'])->name('update');

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

// Rutas para la vista de Productos
Route::get('/products', [AdminController::class, 'productsIndex'])->name('products');
Route::get('/products/create', [AdminController::class, 'productsCreate'])->name('products.create');
Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
Route::get('/products/{product}/edit', [AdminController::class, 'editProductForm'])->name('products.edit');
Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
Route::delete('/products/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');

// RUTAS PARA LA VISTA DE ALIADOS

Route::get('/aliados', [AdminController::class, 'aliadosIndex'])->name(name: 'aliado');
Route::get('/aliado/crear', [AdminController::class, 'aliadosCreate'])->name(name: 'create');
Route::post('/aliados', [AdminController::class, 'storeAlly'])->name(name: 'aliado.store');
Route::get('/aliados/{ally}/edit', [AdminController::class, 'alliesEdit'])->name(name: 'aliado.edit');
Route::put('/aliados/{ally}', [AdminController::class, 'updateAlly'])->name(name: 'aliado.update');
Route::delete('/aliados/{ally}', [AdminController::class, 'destroyAlly'])->name(name: 'aliado.destroy');


// Rutas para reportes de ventas
Route::get('/reports/sales', [SalesController::class, 'index'])->name('reports.sales');
// Ruta para descargar el reporte de ventas en PDF
Route::get('/reports/sales/pdf', [SalesController::class, 'downloadPdf'])->name('reports.sales.pdf');

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

    // Rutas para Notificaciones y Modo Oscuro
    Route::post('/settings/toggle-notifications', [AdminController::class, 'toggleNotifications'])->name('admin.settings.toggle-notifications');
    Route::post('/settings/toggle-dark-mode', [AdminController::class, 'toggleDarkMode'])->name('admin.settings.toggle-dark-mode');
});

require __DIR__ . '/auth.php';
