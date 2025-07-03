<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; // Opcional, si también quieres sembrar permisos
use Illuminate\Support\Facades\DB; // Para limpiar tablas si es necesario

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opcional: Limpiar la caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Opcional: Eliminar roles y permisos existentes para evitar duplicados en cada ejecución
        // NOTA: Usa esto con precaución en entornos de producción, ya que eliminará datos.
        // Permission::query()->delete();
        // Role::query()->delete();
        // DB::table('model_has_roles')->truncate();
        // DB::table('model_has_permissions')->truncate();
        // DB::table('role_has_permissions')->truncate();


        // --- Creación de Roles ---

        // Rol de Administrador
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $this->command->info('Rol "admin" creado o ya existe.');

        // Rol de Aliado Estratégico
        $allyRole = Role::firstOrCreate(['name' => 'ally'], ['guard_name' => 'web']);
        $this->command->info('Rol "ally" creado o ya existe.');

        // Rol de Usuario Común (si lo necesitas, para asignación por defecto)
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);
        $this->command->info('Rol "user" creado o ya existe.');

        $this->command->info('Semilla de roles completada.');
    }
}
