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
        DB::table('clinics')->insert([
            'cl_ucode' => 'MqYLvR6QWFS8NmLcKO8N',
            'cl_code' => 'K20001',
            'cl_code_bpjs' => 'K2I',
            'cl_name' => 'K 2 I A - PAGI',
            'cl_order' => 1,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'HWoRhz5Vu5TLkhrE81p9',
            'cl_code' => 'KK002',
            'cl_code_bpjs' => 'K2I',
            'cl_name' => 'K 2 I A - SORE',
            'cl_order' => 2,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '6lgpBT5r3zHuckFKrvUr',
            'cl_code' => 'KLI001',
            'cl_code_bpjs' => 'ORT',
            'cl_name' => 'KLINIK B.ORTHO - PAGI',
            'cl_order' => 3,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'noMQdpxtCFmYWTk0gWWu',
            'cl_code' => 'KLI020',
            'cl_code_bpjs' => 'ORT',
            'cl_name' => 'KLINIK B.ORTHO - SORE',
            'cl_order' => 4,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '6qmi3bC8X5AwKptQj0dX',
            'cl_code' => 'BED002',
            'cl_code_bpjs' => 'BED',
            'cl_name' => 'KLINIK BEDAH UMUM - PAGI',
            'cl_order' => 5,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'eao3mhR7r9hJamFHEeyU',
            'cl_code' => 'BED004',
            'cl_code_bpjs' => 'BED',
            'cl_name' => 'KLINIK BEDAH UMUM - SORE',
            'cl_order' => 6,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'wakIYShvAECd4MlD9r3G',
            'cl_code' => 'BED001',
            'cl_code_bpjs' => 'BDA',
            'cl_name' => 'KLINIK BEDAH ANAK - PAGI',
            'cl_order' => 7,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'wbkkUHK1U6PiJ6I6MpE9',
            'cl_code' => 'BED003',
            'cl_code_bpjs' => 'BDA',
            'cl_name' => 'KLINIK BEDAH ANAK - SORE',
            'cl_order' => 8,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'r4UIdMGXSEHK33FlMfBF',
            'cl_code' => 'KLI009',
            'cl_code_bpjs' => 'BDM',
            'cl_name' => 'KLINIK BEDAH MULUT - PAGI',
            'cl_order' => 9,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'Nl5DgOlm0oUtQfmPxjo9',
            'cl_code' => 'KLI027',
            'cl_code_bpjs' => 'BDM',
            'cl_name' => 'KLINIK BEDAH MULUT - SORE',
            'cl_order' => 10,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'ccPd9feAE9rOhrxp9UJG',
            'cl_code' => 'KLI002',
            'cl_code_bpjs' => 'BSY',
            'cl_name' => 'KLINIK BEDAH SYARAF - PAGI',
            'cl_order' => 11,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'pFxVMrqQtVcvn5VbfEEl',
            'cl_code' => 'KLI021',
            'cl_code_bpjs' => 'BSY',
            'cl_name' => 'KLINIK BEDAH SYARAF - SORE',
            'cl_order' => 12,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'bfPmdaacmPMHxrIFNM7u',
            'cl_code' => 'KLI010',
            'cl_code_bpjs' => 'URO',
            'cl_name' => 'KLINIK BEDAH UROLOGI - PAGI',
            'cl_order' => 13,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'KwNCtjLXwsnuy0uez1Qu',
            'cl_code' => 'KLI028',
            'cl_code_bpjs' => 'URO',
            'cl_name' => 'KLINIK BEDAH UROLOGI - SORE',
            'cl_order' => 14,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '3dsPAbzIyFPf0pENxOsJ',
            'cl_code' => 'KLI015',
            'cl_code_bpjs' => 'DOT',
            'cl_name' => 'KLINIK DOTS',
            'cl_order' => 15,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'dqT906NKRuFKIv8Wh2pT',
            'cl_code' => 'FIS001',
            'cl_code_bpjs' => 'FSK',
            'cl_name' => 'KLINIK FISIOLOGI KLINIS - PAGI',
            'cl_order' => 16,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'cLdEznyKnTAkcFqTEEWg',
            'cl_code' => 'FIS003',
            'cl_code_bpjs' => 'FSK',
            'cl_name' => 'KLINIK FISIOLOGI KLINIS - SORE',
            'cl_order' => 17,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'EwbZCv5L6gytxivESHSG',
            'cl_code' => 'GIG001',
            'cl_code_bpjs' => 'GIG',
            'cl_name' => 'KLINIK GIGI - PAGI',
            'cl_order' => 18,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'LFljxtPQC1b7Q0VZ4rU8',
            'cl_code' => 'GIG003',
            'cl_code_bpjs' => 'GIG',
            'cl_name' => 'KLINIK GIGI - SORE',
            'cl_order' => 19,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'xw3eHk0vO9l9IfNwUUKN',
            'cl_code' => 'GIG002',
            'cl_code_bpjs' => 'GIG',
            'cl_name' => 'KLINIK GIGI ANAK - PAGI',
            'cl_order' => 20,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'ztZmz5h9XZmhknMHsaIs',
            'cl_code' => 'GIG004',
            'cl_code_bpjs' => 'GIG',
            'cl_name' => 'KLINIK GIGI ANAK - SORE',
            'cl_order' => 21,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'E8cSzCgeOfsE2iocg0e0',
            'cl_code' => 'KLI005',
            'cl_code_bpjs' => 'GIZ',
            'cl_name' => 'KLINIK GIZI - PAGI',
            'cl_order' => 22,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'bChZiLF5MYmB9sgAJWTL',
            'cl_code' => 'KLI032',
            'cl_code_bpjs' => 'GIZ',
            'cl_name' => 'KLINIK GIZI - SORE',
            'cl_order' => 23,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'ezbrkkZtogXnbceVPFNM',
            'cl_code' => 'KLI006',
            'cl_code_bpjs' => 'HCE',
            'cl_name' => 'KLINIK HOMECARE',
            'cl_order' => 24,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '5PoMGAq8R7SVdIj0OP7h',
            'cl_code' => 'KLI012',
            'cl_code_bpjs' => 'JAN',
            'cl_name' => 'KLINIK JANTUNG - PAGI',
            'cl_order' => 25,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'KWIqV3Oe8aIBqwkNnNPe',
            'cl_code' => 'KLI029',
            'cl_code_bpjs' => 'JAN',
            'cl_name' => 'KLINIK JANTUNG - SORE',
            'cl_order' => 26,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'czFGlMdHN12Cr5i4yMGP',
            'cl_code' => 'KES001',
            'cl_code_bpjs' => 'ANA',
            'cl_name' => 'KLINIK KESEHATAN ANAK - PAGI',
            'cl_order' => 27,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'uCoctPWnapkhNn3b3FLv',
            'cl_code' => 'KES002',
            'cl_code_bpjs' => 'ANA',
            'cl_name' => 'KLINIK KESEHATAN ANAK - SORE',
            'cl_order' => 28,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'SRijbhOXz0brFYNrf1rz',
            'cl_code' => 'KLI016',
            'cl_code_bpjs' => 'PSI',
            'cl_name' => 'KLINIK KESEHATAN JIWA',
            'cl_order' => 29,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'Mbh8CqfynB58L937CzNo',
            'cl_code' => 'OKU001',
            'cl_code_bpjs' => 'KEK',
            'cl_name' => 'KLINIK KESEHATAN KERJA',
            'cl_order' => 30,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'fK68nIgWXdCPYQBTOlmR',
            'cl_code' => 'KUL001',
            'cl_code_bpjs' => 'KLT',
            'cl_name' => 'KLINIK DERMATOLOGI V & E - PAGI',
            'cl_order' => 31,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '1jpUmklJ264V0Zbgu0fN',
            'cl_code' => 'KLI024',
            'cl_code_bpjs' => 'KLT',
            'cl_name' => 'KLINIK DERMATOLOGI V & E - SORE',
            'cl_order' => 32,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '0ujYNwePqcKv2FtJRdWj',
            'cl_code' => 'KLI007',
            'cl_code_bpjs' => 'MAT',
            'cl_name' => 'KLINIK MATA - PAGI',
            'cl_order' => 33,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'AufKrPzGc4HEBKxD77te',
            'cl_code' => 'KLI025',
            'cl_code_bpjs' => 'MAT',
            'cl_name' => 'KLINIK MATA - SORE',
            'cl_order' => 34,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'Zsctz778vhXzg7W5szqW',
            'cl_code' => 'KLI011',
            'cl_code_bpjs' => 'MCU',
            'cl_name' => 'KLINIK MCU',
            'cl_order' => 35,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'osPYsYef1Vb1V5QCDynn',
            'cl_code' => 'OBS001',
            'cl_code_bpjs' => 'OBG',
            'cl_name' => 'KLINIK OBSTETRI & GINEKOLOGI - PAGI',
            'cl_order' => 36,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'ta7h939i6vX3Bish9Ub9',
            'cl_code' => 'OBS002',
            'cl_code_bpjs' => 'OBG',
            'cl_name' => 'KLINIK OBSTETRI & GINEKOLOGI - SORE',
            'cl_order' => 37,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'LsefcAr7OrDJUDXPC33y',
            'cl_code' => 'KLI014',
            'cl_code_bpjs' => 'PAR',
            'cl_name' => 'KLINIK PARU - PAGI',
            'cl_order' => 38,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'sFJbDFjzjIdwRouHoWDJ',
            'cl_code' => 'KLI031',
            'cl_code_bpjs' => 'PAR',
            'cl_name' => 'KLINIK PARU - SORE',
            'cl_order' => 39,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'H5pBuuyOrRN0fxshkXno',
            'cl_code' => 'PEN001',
            'cl_code_bpjs' => 'INT',
            'cl_name' => 'KLINIK PENYAKIT DALAM - PAGI',
            'cl_order' => 40,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'vwNlO7GyGjQ827hlHlGP',
            'cl_code' => 'PEN002',
            'cl_code_bpjs' => 'INT',
            'cl_name' => 'KLINIK PENYAKIT DALAM - SORE',
            'cl_order' => 41,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'kox7JMuaTZ3JzeyJmSyz',
            'cl_code' => 'KLI008',
            'cl_code_bpjs' => 'JIW',
            'cl_name' => 'KLINIK PSIKIATRI - PAGI',
            'cl_order' => 42,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '6P7Z6XJqckKeCXcHc8VC',
            'cl_code' => 'KLI026',
            'cl_code_bpjs' => 'JIW',
            'cl_name' => 'KLINIK PSIKIATRI - SORE',
            'cl_order' => 43,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'tWxwMPAad6PQwG79nV2k',
            'cl_code' => 'KLI013',
            'cl_code_bpjs' => 'IRM',
            'cl_name' => 'KLINIK REHABILITASI MEDIK - PAGI (BPJS)',
            'cl_order' => 44,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'mrJtxpaKQjIJaOxmqR3Y',
            'cl_code' => 'KLI030',
            'cl_code_bpjs' => 'IRM',
            'cl_name' => 'KLINIK REHABILITASI MEDIK - SORE',
            'cl_order' => 45,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'IYZhz8dkW1V7xhFpeYNd',
            'cl_code' => 'SPO001',
            'cl_code_bpjs' => 'PTD',
            'cl_name' => 'KLINIK SPESIALIS PROSTODONSIA',
            'cl_order' => 46,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'D1MNOLZLxK35xkfFMiWZ',
            'cl_code' => 'KLI003',
            'cl_code_bpjs' => 'SAR',
            'cl_name' => 'KLINIK SYARAF - PAGI',
            'cl_order' => 47,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'gkr4oXNKwfxPFnhnfE8R',
            'cl_code' => 'KLI022',
            'cl_code_bpjs' => 'SAR',
            'cl_name' => 'KLINIK SYARAF - SORE',
            'cl_order' => 48,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'zHVe8LU6qVq5vduh4hD8',
            'cl_code' => 'KLI004',
            'cl_code_bpjs' => 'THT',
            'cl_name' => 'KLINIK THT - PAGI',
            'cl_order' => 49,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '9WGO50q3sTMYJUiZvTA7',
            'cl_code' => 'KLI023',
            'cl_code_bpjs' => 'THT',
            'cl_name' => 'KLINIK THT - SORE',
            'cl_order' => 50,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'jWPcRQCTCSYtGI7l8RXi',
            'cl_code' => 'UMU001',
            'cl_code_bpjs' => 'UMU',
            'cl_name' => 'KLINIK UMUM - PAGI',
            'cl_order' => 51,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'nF9i6UbpwVOqmTbooCX9',
            'cl_code' => 'UMU002',
            'cl_code_bpjs' => 'UMU',
            'cl_name' => 'KLINIK UMUM - SORE',
            'cl_order' => 52,
            'cl_active' => 0,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'xSNCrgEFWky82hXoQZz7',
            'cl_code' => 'KLI033',
            'cl_code_bpjs' => 'GIZ',
            'cl_name' => 'KLINIK SPESIALIS GIZI KLINIK - SORE',
            'cl_order' => 53,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'iL1OURgPdS',
            'cl_code' => 'KLI035',
            'cl_code_bpjs' => 'PSI',
            'cl_name' => 'KLINIK PSIKOLOGI ANAK DAN REMAJA',
            'cl_order' => 54,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'hkdMn6uTt7',
            'cl_code' => 'PENKAM1',
            'cl_code_bpjs' => 'INT',
            'cl_name' => 'KLINIK PENYAKIT DALAM - KAMILUS PAGI',
            'cl_order' => 55,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '0JKlSDfTah',
            'cl_code' => 'BEDKAM01',
            'cl_code_bpjs' => 'BED',
            'cl_name' => 'KLINIK BEDAH UMUM - KAMILUS PAGI',
            'cl_order' => 56,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'AtACEvx1NJ',
            'cl_code' => 'BURKAM01',
            'cl_code_bpjs' => 'URO',
            'cl_name' => 'KLINIK BEDAH UROLOGI - KAMILUS PAGI',
            'cl_order' => 57,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'RkpOWk0jSf',
            'cl_code' => 'KULKAM01',
            'cl_code_bpjs' => 'KLT',
            'cl_name' => 'KLINIK DERMATOLOGI V & E - KAMILUS PAGI',
            'cl_order' => 58,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '9tVEc3Ac40',
            'cl_code' => 'SYAKAM1',
            'cl_code_bpjs' => 'SAR',
            'cl_name' => 'KLINIK SYARAF - KAMILUS PAGI',
            'cl_order' => 59,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'fJJeMxBjAQ',
            'cl_code' => 'RHBKAM001',
            'cl_code_bpjs' => 'IRM',
            'cl_name' => 'KLINIK REHAB MEDIK - KAMILUS PAGI (UMUM)',
            'cl_order' => 60,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'lgdfvGOOu2',
            'cl_code' => 'ORTKAM1',
            'cl_code_bpjs' => 'ORT',
            'cl_name' => 'KLINIK BEDAH ORTHO - KAMILUS PAGI',
            'cl_order' => 61,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'B8wbE0l37s',
            'cl_code' => 'SYSKAM1',
            'cl_code_bpjs' => 'SYA',
            'cl_name' => 'KLINIK SYARAF - KAMILUS SORE',
            'cl_order' => 62,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'q4MtGNz7IX',
            'cl_code' => 'MATKAM01',
            'cl_code_bpjs' => 'MAT',
            'cl_name' => 'KLINIK MATA - KAMILUS PAGI',
            'cl_order' => 63,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '8TH0DBx5vU',
            'cl_code' => 'THTKAM01',
            'cl_code_bpjs' => 'THT',
            'cl_name' => 'KLINIK THT - KAMILUS PAGI',
            'cl_order' => 64,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'Cr7wf5z2rT',
            'cl_code' => 'BEDKAM02',
            'cl_code_bpjs' => 'BED',
            'cl_name' => 'KLINIK BEDAH UMUM - KAMILUS SORE',
            'cl_order' => 65,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'zFl267cKJO',
            'cl_code' => 'MATKAM02',
            'cl_code_bpjs' => 'MAT',
            'cl_name' => 'KLINIK MATA - KAMILUS SORE',
            'cl_order' => 66,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => '3GFj4KQ2ZM',
            'cl_code' => 'PENKAM2',
            'cl_code_bpjs' => 'INT',
            'cl_name' => 'KLINIK PENYAKIT DALAM - KAMILUS SORE',
            'cl_order' => 67,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('clinics')->insert([
            'cl_ucode' => 'lSjZxNcqV2',
            'cl_code' => 'THTKAM02',
            'cl_code_bpjs' => 'THT',
            'cl_name' => 'KLINIK THT - KAMILUS SORE',
            'cl_order' => 68,
            'cl_active' => 1,
            'created_by' => 'administrator',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
