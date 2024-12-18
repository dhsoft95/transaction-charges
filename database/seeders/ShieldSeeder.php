<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create Roles
        $roles = [
            'team_member' => 'Team Member',
            'finance_approver' => 'Finance Approver',
            'ceo' => 'CEO',
            'super_admin' => 'Super Admin'
        ];

        foreach ($roles as $key => $role) {
            Role::create(['name' => $key]);
        }

        // Create Permissions
        $permissions = [
            // Charge Range Permissions
            'view_charge_range' => 'View Charge Range',
            'create_charge_range' => 'Create Charge Range',
            'submit_for_approval' => 'Submit for Approval',
            'approve_finance' => 'Approve as Finance',
            'approve_ceo' => 'Approve as CEO',
            'reject_charge_range' => 'Reject Charge Range',

            // Transaction Type Permissions
            'view_transaction_type' => 'View Transaction Type',
            'create_transaction_type' => 'Create Transaction Type',
            'edit_transaction_type' => 'Edit Transaction Type',
            'delete_transaction_type' => 'Delete Transaction Type',
        ];

        foreach ($permissions as $key => $permission) {
            Permission::create(['name' => $key]);
        }

        // Assign Permissions to Roles
        $rolePermissions = [
            'team_member' => [
                'view_charge_range',
                'create_charge_range',
                'submit_for_approval',
                'view_transaction_type'
            ],
            'finance_approver' => [
                'view_charge_range',
                'approve_finance',
                'reject_charge_range',
                'view_transaction_type'
            ],
            'ceo' => [
                'view_charge_range',
                'approve_ceo',
                'reject_charge_range',
                'view_transaction_type'
            ],
            'super_admin' => [
                '*' // All permissions
            ]
        ];

        foreach ($rolePermissions as $role => $permissions) {
            $role = Role::findByName($role);
            if ($permissions[0] === '*') {
                $role->givePermissionTo(Permission::all());
            } else {
                $role->givePermissionTo($permissions);
            }
        }
    }
}
