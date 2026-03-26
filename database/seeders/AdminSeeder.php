<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\PermissionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::create([
            'name' => "Super Admin",
            // 'email' => "admin@example.com",
            'password' => Hash::make('password123'),
            'username' => 'adminexample123',
            'phone_number' => '09123456789'
        ]);

        $allPermissionTypeIds = PermissionType::pluck('id');

        foreach ($allPermissionTypeIds as $typeId) {
            Permission::firstOrCreate([
                'admin_id' => $admin->id,
                'permission_type_id' => $typeId,
            ]);
        }
    }
}
