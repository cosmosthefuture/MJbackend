<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use App\Models\PermissionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Admin Management' => [
                'admin_view' => 'View Admins',
                'admin_create' => 'Create Admin',
                'admin_update' => 'Update Admin',
                'admin_delete' => 'Delete Admin',
            ],
            'User Management' => [
                'user_view' => 'View Users',
                'user_create' => 'Create User',
                'user_update' => 'Update User',
                'user_delete' => 'Delete User',
            ],
            // 'Payment Method Management' => [
            //     'payment_method_view' => 'View Payment Methods',
            //     'payment_method_create' => 'Create Payment Method',
            //     'payment_method_update' => 'Update Payment Method',
            //     'payment_method_delete' => 'Delete Payment Method',
            // ],
            // 'User Deposit Management' => [
            //     'user_deposit_request_view' => 'View User Deposit Requests',
            //     'manual_create_user_deposit' => 'Manual Create User Deposit',
            //     'user_deposit_request_action' => 'Approve or Reject User Deposit Request',
            // ],
            // 'User Withdraw Management' => [
            //     'user_withdraw_request_view' => 'View User Withdraw Requests',
            //     'manual_create_user_withdraw' => 'Manual Create User Withdraw',
            //     'user_withdraw_request_action' => 'Approve or Reject User Withdraw Request',
            // ],
            // 'Agent Withdraw Management' => [
            //     'agent_withdraw_request_view' => 'View Agent Withdraw Requests',
            //     'manual_create_agent_withdraw' => 'Manual Create Agent Withdraw',
            //     'agent_withdraw_request_action' => 'Approve or Reject Agent Withdraw Request',
            // ],
            // 'Master Withdraw Management' => [
            //     'master_withdraw_request_view' => 'View Master Withdraw Requests',
            //     'manual_create_master_withdraw' => 'Manual Create Master Withdraw',
            //     'master_withdraw_request_action' => 'Approve or Reject Master Withdraw Request',
            // ],
            'Global Commission Setting Management' => [
                'global_commission_setting_view' => 'View Global Commission Settings',
                'global_commission_setting_update' => 'Update Global Commission Setting',
            ],
            'Game Management' => [
                'game_view' => 'View Games',
                'game_update' => 'Update Game',
            ],
            'Game Rule Management' => [
                'game_rule_view' => 'View Game Rules',
                'game_rule_create' => 'Create Game Rule',
                'game_rule_update' => 'Update Game Rule',
                'game_rule_delete' => 'Delete Game Rule',
            ],
            'Game Room Management' => [
                'game_room_view' => 'View Game Rooms',
                'game_room_create' => 'Create Game Room',
                'game_room_update' => 'Update Game Room',
                'game_room_delete' => 'Delete Game Room',
            ],
            'Master Management' => [
                'master_view' => 'View Masters',
                'master_create' => 'Create Master',
                'master_update' => 'Update Master',
                'master_delete' => 'Delete Master',
            ],
            'Agent Management' => [
                'agent_view' => 'View Agents',
            ],
            // 'User Money Transfer Management' => [
            //     'money_transfer_record_view' => 'View User Money Transfer Records',
            // ],
            'Report' => [
                'report_view' => 'View Reports',
            ],
        ];

        foreach ($data as $groupLabel => $permissions) {
            $group = PermissionGroup::firstOrCreate([
                'name' => \Str::slug($groupLabel, '_'),
            ], [
                'label' => $groupLabel,
            ]);

            foreach ($permissions as $name => $label) {
                PermissionType::firstOrCreate([
                    'name' => $name,
                ], [
                    'label' => $label,
                    'permission_group_id' => $group->id,
                ]);
            }
        }
    }
}
