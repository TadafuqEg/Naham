<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class PermissionRoleSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'show users',
            'create users',
            'edit users',
            'delete users',
            'create roles',
            'edit roles',
            'delete roles',
            'show sections',
            'create sections',
            'edit sections',
            'delete sections',
            'show groups',
            'create groups',
            'edit groups',
            'delete groups',
            'show users location'
            
            // Add more permissions as needed
        ];

        foreach ($permissions as $permission) {
            $existed_permission=Permission::where('name' , $permission)->first();
            if(!$existed_permission){
                Permission::create(['name' => $permission]);
            }
        }

        $roles = [
            'super super admin',
            'admin',
            'super admin',
            'user',
            // Add more roles as needed
        ];

        foreach ($roles as $role) {
            $existed_role=Role::where('name' , $role)->first();
            if(!$existed_role){
                Role::create(['name' => $role]);
            }
        }
        $permissions = Permission::pluck('id', 'id')->all();
        $admin_role = Role::where('name','super super admin')->first();
        $admin_role->syncPermissions($permissions);
    }
}