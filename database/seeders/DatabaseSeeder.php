<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AllyTypeSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(BusinessTypeSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(SubCategorySeeder::class);
        $this->call(AllySeeder::class);
        //$this->call(PaymentTransactionSeeder::class);
        $this->call(CompletePaymentSeeder::class);
    }
}
