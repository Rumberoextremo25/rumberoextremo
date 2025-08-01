<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ally;
use App\Models\User;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\BusinessType;

class AllySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        /**
         * Data Initial
         */

        $userId = User::first() ?? User::factory()->create(); 
        $categoryId = Category::first() ?? Category::factory()->create(); 
        $subCategoryId = SubCategory::first() ?? SubCategory::factory()->create(); 
        $businessTypeId = BusinessType::first() ?? BusinessType::factory()->create(); 
        //

        Ally::create(([
            'user_id' => $userId->id,
            'company_name' => 'Empresa Soluciones Globales C.A.',
            'company_rif' => 'J-12345678-0',
            'contact_person_name' => 'Ana Martínez',
            'contact_email' => 'contacto@solucionesglobales.com',
            'contact_phone' => '+584121234567',
            'contact_phone_alt' => '+582129876543',
            'company_address' => 'Av. Francisco de Miranda, Edif. Centro Plaza, Caracas',
            'website_url' => 'https://www.solucionesglobales.com',
            'discount' => 15.00,
            'notes' => 'Aliado estratégico para servicios de consultoría.',
            'registered_at' => now(),
            'status' => 'active',
            'category_id' => $categoryId->id,
            'sub_category_id' => $subCategoryId->id,
            'business_type_id' => $businessTypeId->id,
            // 'bank_name' => 'Banco Nacional de Crédito',
            // 'account_number' => '01050000123456789012',
            // 'account_type' => 'Corriente',
            // 'id_number' => 'J-12345678-0',
            // 'account_holder_name' => 'Empresa Soluciones Globales C.A.',
        ]));

    }
}
