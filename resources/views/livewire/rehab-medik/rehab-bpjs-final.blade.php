<main class="background">
    <div class="receipt-card">
        <div class="receipt-header">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="RS Cahya Kawaluyan">
            </a>
            <h2>REGISTRASI KLINIK REHABILITASI MEDIK BPJS</h2>
            <span class="receipt-subtitle">BUKTI PENDAFTARAN</span>
        </div>

        <div class="data-row">
            <span class="data-label">Kode Appointment</span>
            <span class="data-value"><span class="receipt-badge">{{ $appointmentData['ap_no'] }}</span></span>
        </div>
        <div class="data-row">
            <span class="data-label">NORM</span>
            <span class="data-value">{{ $appointmentDetailData['uap_norm'] }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">Nama Pasien</span>
            <span class="data-value">{{ $appointmentDetailData['uap_name'] }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">Tanggal Pendaftaran</span>
            <span
                class="data-value text-uppercase">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $scheduleDateData['sd_date'])->isoFormat('dddd, DD MMMM YYYY') }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">Nama Klinik</span>
            <span class="data-value text-uppercase">{{ $scheduleData['sc_clinic_name'] }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">Nama Dokter</span>
            <span class="data-value text-uppercase">{{ $scheduleData['sc_doctor_name'] }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">Sesi</span>
            <span class="data-value">SESI {{ $scheduleDetailData['scd_session'] }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">No Antrian</span>
            <span class="data-value"><span class="receipt-badge">{{ $appointmentData['ap_queue'] }}</span></span>
        </div>
        <div class="data-row">
            <span class="data-label">Waktu Daftar Ulang</span>
            <span
                class="data-value">{{ \Carbon\Carbon::createFromFormat('H:i:s', $appointmentData['ap_registration_time'])->format('H:i') }}
                WIB</span>
        </div>

        <div class="receipt-notes">
            <p>* Untuk Konfirmasi Kepastian Jadwal Praktek Dokter / Terdapat Pertanyaan Dapat Menghubungi Customer
                Service di Nomor WhatsApp <b>0812 1111 8009</b></p>
        </div>

        <div class="receipt-actions">
            <button class="w-100 btn btn-primary mb-2" wire:click="downloadPdf" wire:loading.attr="disabled">
                <i class="ri-download-2-line me-1"></i> Download Bukti Pendaftaran
            </button>
            <a href="{{ route('home') }}" class="w-100 btn btn-danger">
                <i class="ri-arrow-left-line me-1"></i> Kembali Ke Halaman Utama
            </a>
        </div>
    </div>
</main>
