<main class="background">
    <div class="receipt-card">
        <div class="receipt-header">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="RS Cahya Kawaluyan">
            </a>
            <h2>REGISTRASI PASIEN FISIOTERAPI</h2>
            <span class="receipt-subtitle">BUKTI PENDAFTARAN</span>
        </div>

        <div class="receipt-alert">
            <div class="alert-title">⚠️ PENGUMUMAN</div>
            <div class="alert-body">Pasien Fisioterapi Wajib Membawa <strong>SURAT PENGANTAR</strong> dari Dokter
                Rehabilitasi Medik.</div>
        </div>

        <div class="data-row">
            <span class="data-label">NORM</span>
            <span class="data-value">{{ $appointmentData['fap_norm'] }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">Nama Pasien</span>
            <span class="data-value">{{ $appointmentData['fap_name'] }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">Tanggal Pendaftaran</span>
            <span
                class="data-value text-uppercase">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $scheduleDateData['sd_date'])->isoFormat('dddd, DD MMMM YYYY') }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">Nama Klinik</span>
            <span class="data-value text-uppercase">
                @if ($appointmentData['fap_type'] == 'UMUM PAGI')
                    FISIOTERAPI UMUM PAGI
                @elseif($appointmentData['fap_type'] == 'UMUM SORE')
                    FISIOTERAPI UMUM SORE
                @elseif($appointmentData['fap_type'] == 'BPJS PAGI')
                    FISIOTERAPI BPJS PAGI
                @else
                    FISIOTERAPI BPJS SORE
                @endif
            </span>
        </div>
        <div class="data-row">
            <span class="data-label">No Antrian</span>
            <span class="data-value"><span class="receipt-badge">{{ $appointmentData['fap_queue'] }}</span></span>
        </div>
        <div class="data-row">
            <span class="data-label">Waktu Daftar Ulang</span>
            <span
                class="data-value">{{ \Carbon\Carbon::createFromFormat('H:i:s', $appointmentData['fap_registration_time'])->format('H:i') }}
                WIB</span>
        </div>

        <div class="receipt-notes">
            <p>* Mohon datang tepat waktu sesuai waktu pendaftaran ulang</p>
            <p>* Nomor antrian ini adalah nomor antrian tindakan di fisioterapi</p>
            <p>* Apabila pasien datang tidak sesuai jam daftar ulang, maka syarat dan ketentuan berlaku</p>
            <p>* Pasien fisioterapi wajib membawa surat pengantar dari dokter rehabilitasi medik</p>
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
