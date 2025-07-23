<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Puedes pasar datos resumidos al dashboard si lo deseas
        // $totalBanners = \App\Models\Banner::count();
        // $totalAllies = \App\Models\CommercialAlly::count();
        // $totalPromotions = \App\Models\Promotion::count();

        return view('admin.dashboard'); // Carga la vista 'resources/views/admin/dashboard.blade.php'
    }
}
