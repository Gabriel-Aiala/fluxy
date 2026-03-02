<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $dono = Role::firstOrCreate([
            'name' => 'dono',
            'guard_name' => 'web',
        ]);

        $gerente = Role::firstOrCreate([
            'name' => 'gerente',
            'guard_name' => 'web',
        ]);

        $assistente = Role::firstOrCreate([
            'name' => 'assistente',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions([
            'criar_usuarios',
            'editar_usuarios',
            'deletar_usuario',
            'ver_saidas',
            'inserir_saidas',
            'ver_entradas',
            'inserir_entradas',
        ]);

        $dono->syncPermissions([
            'criar_usuarios',
            'editar_usuarios',
            'deletar_usuario',
            'ver_saidas',
            'inserir_saidas',
            'ver_entradas',
            'inserir_entradas',
        ]);

        $gerente->syncPermissions([
            'ver_saidas',
            'inserir_saidas',
            'ver_entradas',
            'inserir_entradas',
        ]);

        $assistente->syncPermissions([
            'ver_saidas',
            'ver_entradas',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
