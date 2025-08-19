<?php

namespace App\Services;

use App\Models\ScheduleDate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AppointmentDate {
    public function selectAppointmentDate(): ?string
    {
        // Temukan satu jadwal paling awal yang memenuhi semua kriteria
        $scheduleDate = ScheduleDate::query()
            // 1. Tanggalnya harus hari ini atau di masa depan
            ->where('sd_date', '>=', Carbon::today()->toDateString())

            // 2. Tidak boleh hari libur
            ->where('sd_is_holiday', false)

            // 3. Tidak boleh hari Minggu (menggunakan fungsi database)
            // DAYOFWEEK() adalah fungsi MySQL, 1 = Minggu.
            // Gunakan parameter binding (?) untuk keamanan.
            ->whereRaw('DAYOFWEEK(sd_date) != ?', [1])

            // 4. Urutkan berdasarkan tanggal agar mendapatkan yang paling awal
            ->orderBy('sd_date', 'asc')

            // 5. Ambil hanya satu record pertama yang cocok
            ->first();

        // Jika data ditemukan, kembalikan tanggalnya. Jika tidak, kembalikan null.
        return $scheduleDate?->sd_date;
    }

    public function selectNextSevenAppointmentDates(): Collection
    {
        // Temukan 7 jadwal paling awal yang memenuhi semua kriteria
        return $scheduleDates = ScheduleDate::query()
            // 1. Tanggalnya harus hari ini atau di masa depan
            ->where('sd_date', '>=', Carbon::today()->toDateString())

            // 2. Tidak boleh hari libur
            // ->where('sd_is_holiday', false)

            // 3. Tidak boleh hari Minggu (menggunakan fungsi database)
            ->whereRaw('DAYOFWEEK(sd_date) != ?', [1])

            // 4. Urutkan berdasarkan tanggal agar mendapatkan yang paling awal
            ->orderBy('sd_date', 'asc')

            // 5. Ambil 7 record pertama yang cocok
            ->limit(7)

            // 6. Eksekusi query dan ambil hasilnya
            ->get();
    }
}
