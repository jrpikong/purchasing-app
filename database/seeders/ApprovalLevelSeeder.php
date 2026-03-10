<?php

namespace Database\Seeders;

use App\Models\ApprovalFlow;
use App\Models\ApprovalLevel;
use Illuminate\Database\Seeder;

class ApprovalLevelSeeder extends Seeder
{
    public function run(): void
    {
        $standard   = ApprovalFlow::where('name', 'Standard Approval')->first();
        $management = ApprovalFlow::where('name', 'Management Approval')->first();
        $executive  = ApprovalFlow::where('name', 'Executive Approval')->first();

        if ($standard) {
            ApprovalLevel::firstOrCreate(
                ['approval_flow_id' => $standard->id, 'level_order' => 1],
                [
                    'role_type'   => ApprovalLevel::ROLE_TYPE_SECTION_HEAD,
                    'description' => 'Persetujuan Section Head',
                ]
            );
        }

        if ($management) {
            ApprovalLevel::firstOrCreate(
                ['approval_flow_id' => $management->id, 'level_order' => 1],
                [
                    'role_type'   => ApprovalLevel::ROLE_TYPE_SECTION_HEAD,
                    'description' => 'Persetujuan Section Head',
                ]
            );
            ApprovalLevel::firstOrCreate(
                ['approval_flow_id' => $management->id, 'level_order' => 2],
                [
                    'role_type'   => ApprovalLevel::ROLE_TYPE_DIVISION_HEAD,
                    'description' => 'Persetujuan Division Head',
                ]
            );
        }

        if ($executive) {
            ApprovalLevel::firstOrCreate(
                ['approval_flow_id' => $executive->id, 'level_order' => 1],
                [
                    'role_type'   => ApprovalLevel::ROLE_TYPE_SECTION_HEAD,
                    'description' => 'Persetujuan Section Head',
                ]
            );
            ApprovalLevel::firstOrCreate(
                ['approval_flow_id' => $executive->id, 'level_order' => 2],
                [
                    'role_type'   => ApprovalLevel::ROLE_TYPE_DIVISION_HEAD,
                    'description' => 'Persetujuan Division Head',
                ]
            );
            ApprovalLevel::firstOrCreate(
                ['approval_flow_id' => $executive->id, 'level_order' => 3],
                [
                    'role_type'   => ApprovalLevel::ROLE_TYPE_FINANCE_ADMIN,
                    'description' => 'Persetujuan Finance Admin',
                ]
            );
            ApprovalLevel::firstOrCreate(
                ['approval_flow_id' => $executive->id, 'level_order' => 4],
                [
                    'role_type'   => ApprovalLevel::ROLE_TYPE_TREASURER,
                    'description' => 'Persetujuan Treasurer',
                ]
            );
        }
    }
}
