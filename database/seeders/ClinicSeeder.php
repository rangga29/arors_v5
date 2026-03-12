<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function now;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        $serviceUnits = [
            ['OUTPATIENT', 'VT01', 'ORTKAM1', 'ORT', 'KLINIK BEDAH ORTHOPEDI - KAMILUS PAGI'],
            ['OUTPATIENT', 'VT01', 'BEDKAM01', 'BED', 'KLINIK BEDAH - KAMILUS PAGI'],
            ['OUTPATIENT', 'VT01', 'BEDKAM02', 'BED', 'KLINIK BEDAH - KAMILUS SORE'],
            ['OUTPATIENT', 'VT01', 'BURKAM01', 'URO', 'KLINIK BEDAH UROLOGI - KAMILUS PAGI'],
            ['OUTPATIENT', 'VT01', 'KULKAM01', 'KLT', 'KLINIK DERMATOLOGI V & E - KAMILUS PAGI'],
            ['OUTPATIENT', 'VT01', 'KULKAM02', 'KLT', 'KLINIK DERMATOLOGI V & E - KAMILUS SORE'],
            ['OUTPATIENT', 'VT01', 'GIZKAM001', 'GIZ', 'KLINIK GIZI - KAMILUS'],
            ['OUTPATIENT', 'VT01', 'ANAKAM01', 'ANA', 'KLINIK KESEHATAN ANAK - KAMILUS SORE'],
            ['OUTPATIENT', 'VT01', 'MATKAM01', 'MAT', 'KLINIK MATA - KAMILUS PAGI'],
            ['OUTPATIENT', 'VT01', 'MATKAM02', 'MAT', 'KLINIK MATA - KAMILUS SORE'],
            ['OUTPATIENT', 'VT01', 'PENKAM1', 'INT', 'KLINIK PENYAKIT DALAM - KAMILUS PAGI'],
            ['OUTPATIENT', 'VT01', 'PENKAM2', 'INT', 'KLINIK PENYAKIT DALAM - KAMILUS SORE'],
            ['OUTPATIENT', 'VT01', 'SYAKAM1', 'SAR', 'KLINIK SYARAF - KAMILUS PAGI'],
            ['OUTPATIENT', 'VT01', 'SYSKAM1', 'SAR', 'KLINIK SYARAF - KAMILUS SORE'],
            ['OUTPATIENT', 'VT01', 'THTKAM01', 'THT', 'KLINIK THT - KAMILUS PAGI'],
            ['OUTPATIENT', 'VT01', 'THTKAM02', 'THT', 'KLINIK THT - KAMILUS SORE'],
            ['OUTPATIENT', 'VT01', 'K20001', 'K2I', 'KLINIK KESEHATAN IBU & ANAK - PAGI'],
            ['OUTPATIENT', 'VT01', 'KK002', 'K2I', 'KLINIK KESEHATAN IBU & ANAK - SORE'],
            ['OUTPATIENT', 'VT01', 'HIV001', 'HIV', 'KLINIK ALOYSIUS'],
            ['OUTPATIENT', 'VT01', 'BED002', 'BED', 'KLINIK BEDAH - PAGI'],
            ['OUTPATIENT', 'VT01', 'BED004', 'BED', 'KLINIK BEDAH - SORE'],
            ['OUTPATIENT', 'VT01', 'BED001', 'BDA', 'KLINIK BEDAH ANAK - PAGI'],
            ['OUTPATIENT', 'VT01', 'BED003', 'BDA', 'KLINIK BEDAH ANAK - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI009', 'BDM', 'KLINIK BEDAH MULUT - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI027', 'BDM', 'KLINIK BEDAH MULUT - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI001', 'ORT', 'KLINIK BEDAH ORTHOPEDI - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI020', 'ORT', 'KLINIK BEDAH ORTHOPEDI - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI002', 'BSY', 'KLINIK BEDAH SYARAF - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI021', 'BSY', 'KLINIK BEDAH SYARAF - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI010', 'URO', 'KLINIK BEDAH UROLOGI - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI028', 'URO', 'KLINIK BEDAH UROLOGI - SORE'],
            ['OUTPATIENT', 'VT01', 'KUL001', 'KLT', 'KLINIK DERMATOLOGI V & E - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI024', 'KLT', 'KLINIK DERMATOLOGI V & E - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI015', 'DOT', 'KLINIK DOTS'],
            ['OUTPATIENT', 'VT01', 'FIS001', 'FSK', 'KLINIK FISIOLOGI KLINIS'],
            ['OUTPATIENT', 'VT01', 'GIG001', 'GIG', 'KLINIK GIGI - PAGI'],
            ['OUTPATIENT', 'VT01', 'GIG003', 'GIG', 'KLINIK GIGI - SORE'],
            ['OUTPATIENT', 'VT01', 'GIG002', 'GIG', 'KLINIK GIGI ANAK - PAGI'],
            ['OUTPATIENT', 'VT01', 'GIG004', 'GIG', 'KLINIK GIGI ANAK - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI005', 'GIZ', 'KLINIK GIZI - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI032', 'GIZ', 'KLINIK GIZI - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI006', 'HCE', 'KLINIK HOMECARE'],
            ['OUTPATIENT', 'VT01', 'KLI012', 'JAN', 'KLINIK JANTUNG - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI029', 'JAN', 'KLINIK JANTUNG - SORE'],
            ['OUTPATIENT', 'VT01', 'KES001', 'ANA', 'KLINIK KESEHATAN ANAK - PAGI'],
            ['OUTPATIENT', 'VT01', 'KES002', 'ANA', 'KLINIK KESEHATAN ANAK - SORE'],
            ['OUTPATIENT', 'VT01', 'OKU001', 'KEK', 'KLINIK KESEHATAN KERJA'],
            ['OUTPATIENT', 'VT01', 'SEHAT001', 'SEH', 'KLINIK KONSULTASI POLA HIDUP SEHAT - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI007', 'MAT', 'KLINIK MATA - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI025', 'MAT', 'KLINIK MATA - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI011', 'MCU', 'KLINIK MCU'],
            ['OUTPATIENT', 'VT01', 'KMEM01', 'MEM', 'KLINIK MEMORI'],
            ['OUTPATIENT', 'VT01', 'OBS001', 'OBG', 'KLINIK OBSTETRI & GINEKOLOGI - PAGI'],
            ['OUTPATIENT', 'VT01', 'OBS002', 'OBG', 'KLINIK OBSTETRI & GINEKOLOGI - SORE'],
            ['OUTPATIENT', 'VT01', 'ODS001', 'ODS', 'KLINIK ODS'],
            ['OUTPATIENT', 'VT01', 'KLI014', 'PAR', 'KLINIK PARU - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI031', 'PAR', 'KLINIK PARU - SORE'],
            ['OUTPATIENT', 'VT01', 'PEN001', 'INT', 'KLINIK PENYAKIT DALAM - PAGI'],
            ['OUTPATIENT', 'VT01', 'PEN002', 'INT', 'KLINIK PENYAKIT DALAM - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI008', 'JIW', 'KLINIK PSIKIATRI - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI026', 'JIW', 'KLINIK PSIKIATRI - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI035', 'PSI', 'KLINIK PSIKOLOGI ANAK DAN REMAJA'],
            ['OUTPATIENT', 'VT01', 'KLI033', 'GIZ', 'KLINIK SPESIALIS GIZI KLINIK - SORE'],
            ['OUTPATIENT', 'VT01', 'SPO001', 'SPO', 'KLINIK SPESIALIS PROSTODONSIA'],
            ['OUTPATIENT', 'VT01', 'KLI003', 'SYA', 'KLINIK SYARAF - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI022', 'SYA', 'KLINIK SYARAF - SORE'],
            ['OUTPATIENT', 'VT01', 'KLI004', 'THT', 'KLINIK THT - PAGI'],
            ['OUTPATIENT', 'VT01', 'KLI023', 'THT', 'KLINIK THT - SORE'],
            ['OUTPATIENT', 'VT01', 'UMU001', 'UMU', 'KLINIK UMUM - PAGI'],
            ['OUTPATIENT', 'VT01', 'UMU002', 'UMU', 'KLINIK UMUM - SORE'],
            ['OUTPATIENT', 'VT01', 'RHBKAM001', 'IRM', 'KLINIK REHABILITASI MEDIK - KAMILUS PAGI (UMUM)'],
            ['OUTPATIENT', 'VT01', 'KLI013', 'IRM', 'KLINIK REHABILITASI MEDIK - PAGI (BPJS)'],
            ['OUTPATIENT', 'VT01', 'KLI030', 'IRM', 'KLINIK REHABILITASI MEDIK - SORE'],
            ['DIAGNOSTIC', 'VT07', 'REH001', 'IRM', 'REHABILITASI MEDIK'],
        ];

        $insertData = [];
        $order = 1;

        foreach ($serviceUnits as $unit) {

            [$department, $visit_type, $code, $code_bpjs, $name] = $unit;

            $insertData[] = [
                'cl_ucode' => Str::random(20),
                'cl_code' => $code,
                'cl_code_bpjs' => $code_bpjs,
                'cl_name' => $name,
                'cl_order' => $order++,
                'cl_department' => $department,
                'cl_visit_type' => $visit_type,
                'created_by' => 'administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('clinics')->insert($insertData);
    }
}
