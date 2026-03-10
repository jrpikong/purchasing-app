<?php

namespace Database\Seeders;

use App\Models\ApprovalFlow;
use Illuminate\Database\Seeder;

class ApprovalFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates three global approval flows (department_id = null) that apply
     * to all departments based on purchase request amount:
     *   - Standard  : Rp 0 – 10.000.000   → 1 level (Section Head)
     *   - Management: Rp 10.000.001 – 50.000.000 → 2 levels (Section Head + Division Head)
     *   - Executive : Rp 50.000.001 – ∞   → 4 levels (Section + Division + Finance Admin + Treasurer)
     */
    public function run(): void
    {
        $flows = [
            [
                'name'          => 'Standard Approval',
                'department_id' => null,
                'min_amount'    => 0,
                'max_amount'    => 10_000_000,
                'description'   => 'Pengajuan s/d Rp 10 juta — disetujui Section Head',
                'is_active'     => true,
            ],
            [
                'name'          => 'Management Approval',
                'department_id' => null,
                'min_amount'    => 10_000_001,
                'max_amount'    => 50_000_000,
                'description'   => 'Pengajuan Rp 10 juta – Rp 50 juta — Section Head & Division Head',
                'is_active'     => true,
            ],
            [
                'name'          => 'Executive Approval',
                'department_id' => null,
                'min_amount'    => 50_000_001,
                'max_amount'    => 999_999_999_999,
                'description'   => 'Pengajuan > Rp 50 juta — semua level approval wajib',
                'is_active'     => true,
            ],
        ];

        foreach ($flows as $flow) {
            ApprovalFlow::firstOrCreate(
                ['name' => $flow['name'], 'department_id' => $flow['department_id']],
                $flow
            );
        }
    }
}
