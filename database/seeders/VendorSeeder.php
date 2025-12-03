<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        // Prepare vendor data (without vendor_code)
        $vendors = [
            [
                'name' => 'PT Sumber Makmur Abadi',
                'address' => 'Jl. Raya Cilandak No. 45, Jakarta Selatan',
                'phone' => '021-7654321',
                'email' => 'info@sumbermakmur.co.id',
                'contact_person' => 'Budi Santoso',
                'contact_phone' => '081234567890',
                'tax_number' => '01.234.567.8-091.000',
                'bank_name' => 'BCA',
                'bank_account' => '1234567890',
                'bank_account_name' => 'PT Sumber Makmur Abadi',
                'is_active' => true,
                'notes' => 'Supplier ATK & kebutuhan kantor',
            ],
            [
                'name' => 'PT Teknologi Nusantara',
                'address' => 'Kawasan Mega Kuningan, Jakarta Selatan',
                'phone' => '021-55667788',
                'email' => 'sales@teknus.co.id',
                'contact_person' => 'Andi Pratama',
                'contact_phone' => '081298765432',
                'tax_number' => '02.345.678.9-102.000',
                'bank_name' => 'Mandiri',
                'bank_account' => '1400012345678',
                'bank_account_name' => 'PT Teknologi Nusantara',
                'is_active' => true,
                'notes' => 'Vendor komputer, laptop & IT equipment',
            ],
            [
                'name' => 'CV Jaya Printer Solution',
                'address' => 'Jl. Gatot Subroto No. 12, Bandung',
                'phone' => '022-7654321',
                'email' => 'support@jayaprinter.co.id',
                'contact_person' => 'Siti Rahma',
                'contact_phone' => '085712345678',
                'tax_number' => '03.567.890.1-222.000',
                'bank_name' => 'BNI',
                'bank_account' => '9876543210',
                'bank_account_name' => 'CV Jaya Printer Solution',
                'is_active' => true,
                'notes' => 'Supplier printer & toner',
            ],
            [
                'name' => 'PT Surya Bangun Mandiri',
                'address' => 'Jl. Diponegoro No. 88, Surabaya',
                'phone' => '031-4455667',
                'email' => 'admin@suryabangunmandiri.com',
                'contact_person' => 'Dewi Lestari',
                'contact_phone' => '089612345678',
                'tax_number' => '04.678.901.2-333.000',
                'bank_name' => 'BRI',
                'bank_account' => '009812345678900',
                'bank_account_name' => 'PT Surya Bangun Mandiri',
                'is_active' => true,
                'notes' => 'Vendor material bangunan & perbaikan fasilitas',
            ],
            [
                'name' => 'Warung Catering Sehat',
                'address' => 'Jl. Kelapa Dua No. 5, Depok',
                'phone' => '0857-1234-5678',
                'email' => 'catering@sehatku.id',
                'contact_person' => 'Maya Wulandari',
                'contact_phone' => '081234000999',
                'tax_number' => '05.789.012.3-444.000',
                'bank_name' => 'BCA',
                'bank_account' => '5222334455',
                'bank_account_name' => 'Warung Catering Sehat',
                'is_active' => true,
                'notes' => 'Vendor konsumsi acara kantor',
            ],
            [
                'name' => 'PT Bersih Sentosa',
                'address' => 'Jl. Pluit Raya No. 10, Jakarta Utara',
                'phone' => '021-77889900',
                'email' => 'sales@bersihsentosa.com',
                'contact_person' => 'Rio Purnomo',
                'contact_phone' => '082233445566',
                'tax_number' => '06.890.123.4-555.000',
                'bank_name' => 'Mandiri',
                'bank_account' => '1450087654321',
                'bank_account_name' => 'PT Bersih Sentosa',
                'is_active' => true,
                'notes' => 'Cleaning service & alat kebersihan',
            ],
            [
                'name' => 'PT Multimedia Elektronik',
                'address' => 'Jl. Asia Afrika No. 88, Bandung',
                'phone' => '022-33445566',
                'email' => 'marketing@multielek.co.id',
                'contact_person' => 'Hendra Wijaya',
                'contact_phone' => '087712345678',
                'tax_number' => '07.901.234.5-666.000',
                'bank_name' => 'BCA',
                'bank_account' => '3334445556',
                'bank_account_name' => 'PT Multimedia Elektronik',
                'is_active' => true,
                'notes' => 'Vendor elektronik dan perlengkapan meeting',
            ],
        ];

        // Determine starting sequence safely (read max sequence from existing vendor_codes)
        // vendor_code format: VND0001 (prefix VND + 4 digit sequence)
        $maxSequence = 0;
        try {
            $maxCode = DB::table('vendors')
                ->selectRaw("MAX(CAST(SUBSTRING(vendor_code, 4) AS UNSIGNED)) as seq")
                ->value('seq');

            $maxSequence = (int) $maxCode;
        } catch (\Throwable $e) {
            // If the query fails (table doesn't exist yet), fallback to 0
            $maxSequence = 0;
        }

        $sequence = $maxSequence;

        foreach ($vendors as $data) {
            $sequence++;
            $data['vendor_code'] = sprintf('VND%04d', $sequence);

            // Use create; if vendor_code unique constraint still conflicts, skip or update
            try {
                Vendor::create($data);
            } catch (\Exception $ex) {
                // if unique constraint still occurs (very unlikely), generate a random suffix and retry once
                $data['vendor_code'] = 'VND' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
                Vendor::create($data);
            }
        }
    }
}
