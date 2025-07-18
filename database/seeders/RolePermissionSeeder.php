<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// database/seeders/RolePermissionSeeder.php
class RolePermissionSeeder extends Seeder
{
    public function run()
    {
          $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrator']);
        $editorRole = Role::create(['name' => 'editor', 'description' => 'Content Editor']);
        $stateEditorRole = Role::create(['name' => 'state_editor', 'description' => 'State-Level Editor']);
        $lgaEditorRole = Role::create(['name' => 'lga_editor', 'description' => 'LGA-Level Editor']);

        $permissions = [
            'manage_users' => 'Manage Users',
            'manage_roles' => 'Manage Roles',
            'view_records' => 'View Records',
            'create_records' => 'Create Records',
            'edit_records' => 'Edit Records',
            'delete_records' => 'Delete Records',
            'manage_state_records' => 'Manage State Records',
            'manage_lga_records' => 'Manage LGA Records',
            'view_project_outlooks' => 'View Project Outlooks',
            'create_project_outlooks' => 'Create Project Outlooks',
            'edit_project_outlooks' => 'Edit Project Outlooks',
            'delete_project_outlooks' => 'Delete Project Outlooks',
        ];

        foreach ($permissions as $name => $desc) {
            Permission::create(['name' => $name, 'description' => $desc]);
        }

        // Assign permissions to roles
        $adminRole->permissions()->attach(Permission::all());
        
        $editorRole->permissions()->attach(Permission::whereIn('name', [
            'view_records', 'create_records', 'edit_records', 'delete_records'
        ])->get());
        
        $stateEditorRole->permissions()->attach(Permission::whereIn('name', [
            'view_records', 'create_records', 'edit_records', 'manage_state_records',
             'view_project_outlooks',
            'create_project_outlooks',
            'edit_project_outlooks'
        ])->get());
        
        $lgaEditorRole->permissions()->attach(Permission::whereIn('name', [
            'view_records', 'create_records', 'edit_records', 'manage_lga_records'
        ])->get());

      //  $adminRole->permissions()->attach(Permission::where('name', 'like', '%project_outlooks%')->get());
    

        // Assign admin role to first user
        $adminUser->roles()->attach($adminRole);
    }
}