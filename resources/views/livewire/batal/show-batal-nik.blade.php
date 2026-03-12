<div>
    {{-- HEADER UTAMA: Logo + Judul (hanya 1x) --}}
    <div class="receipt-card">
        <div class="receipt-header">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="RS Cahya Kawaluyan">
            </a>
            <h2>PEMBATALAN ANTRIAN NIK</h2>
            <span class="receipt-subtitle">DAFTAR ANTRIAN AKTIF</span>
        </div>
    </div>

    @if ($newData->count() !== 0)
        @foreach ($newData as $index => $nap)
            @php
                $apt = $nap->appointment;
                $scd = $apt->scheduleDetail;
                $sc = $scd->schedule;
                $sd = $sc->scheduleDate;

                $clinicName = strtoupper($sc->sc_clinic_name ?? '');
                $startDate = $sd->sd_date ?? '';
                $bpCode = $nap->nap_business_partner_code ?? 'PERSONAL';

                // Detect type
                $isRehabMedik = str_contains($clinicName, 'REHAB');
                $isSundayClinic = false;
                if ($startDate) {
                    try {
                        $isSundayClinic = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->isSunday();
                    } catch (\Exception $e) {
                    }
                }

                // Detect payment type
                $isAsuransi = $bpCode !== 'PERSONAL';

                // Set fake isBpjs since it's used for deletion type. Actually for Pasien Baru usually BPJS isn't available online, but we can set $deleteType 'umum'.
                $deleteType = 'umum';

                // Determine header title
                if ($isRehabMedik) {
                    if ($isAsuransi) {
                        $headerTitle = 'REGISTRASI PASIEN BARU KLINIK REHABILITASI MEDIK ASURANSI / KONTRAKTOR';
                    } else {
                        $headerTitle = 'REGISTRASI PASIEN BARU KLINIK REHABILITASI MEDIK UMUM';
                    }
                } elseif ($isSundayClinic) {
                    if ($isAsuransi) {
                        $headerTitle = 'REGISTRASI PASIEN BARU SUNDAY CLINIC ASURANSI / KONTRAKTOR';
                    } else {
                        $headerTitle = 'REGISTRASI PASIEN BARU SUNDAY CLINIC UMUM';
                    }
                } else {
                    if ($isAsuransi) {
                        $headerTitle = 'REGISTRASI PASIEN BARU ASURANSI / KONTRAKTOR';
                    } else {
                        $headerTitle = 'REGISTRASI PASIEN BARU UMUM';
                    }
                }
            @endphp

            <div class="receipt-card" style="margin-top: 1rem;">
                <div class="receipt-header" style="padding: 0.5rem 0 0.75rem;">
                    <h2 style="font-size: 1rem; margin: 0;">{{ $headerTitle }}</h2>
                </div>

                <div class="data-row">
                    <span class="data-label">Kode Appointment</span>
                    <span class="data-value"><span class="receipt-badge">{{ $apt->ap_no }}</span></span>
                </div>
                <div class="data-row">
                    <span class="data-label">NIK</span>
                    <span class="data-value">{{ $nap->nap_ssn }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nama Pasien</span>
                    <span class="data-value text-uppercase">{{ $nap->nap_name }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Tanggal Pendaftaran</span>
                    <span class="data-value text-uppercase">
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $sd->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}
                    </span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nama Klinik</span>
                    <span class="data-value text-uppercase">{{ $sc->sc_clinic_name }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nama Dokter</span>
                    <span class="data-value text-uppercase">{{ $sc->sc_doctor_name }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Sesi</span>
                    <span class="data-value">SESI {{ $scd->scd_session }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">No Antrian</span>
                    <span class="data-value"><span class="receipt-badge">{{ $apt->ap_queue }}</span></span>
                </div>
                @if ($isAsuransi)
                    <div class="data-row">
                        <span class="data-label">Nama Asuransi</span>
                        <span
                            class="data-value text-uppercase">{{ !empty($nap->nap_business_partner_name) ? $nap->nap_business_partner_name : 'ASURANSI / KONTRAKTOR' }}</span>
                    </div>
                @endif
                <div class="data-row">
                    <span class="data-label">Waktu Daftar Ulang</span>
                    <span
                        class="data-value">{{ \Carbon\Carbon::createFromFormat('H:i:s', $apt->ap_registration_time)->format('H:i') }}
                        WIB</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Status Appointment</span>
                    <span class="data-value">Aktif</span>
                </div>

                {{-- Notes --}}
                @if ($isRehabMedik || $isSundayClinic)
                    <div class="receipt-notes">
                        <p>* Untuk Konfirmasi Kepastian Jadwal Praktek Dokter / Terdapat Pertanyaan Dapat Menghubungi
                            Customer Service di Nomor WhatsApp <b>0812 1111 8009</b></p>
                    </div>
                @else
                    <div class="receipt-notes">
                        <p>* Untuk Konfirmasi Kepastian Jadwal Praktek Dokter / Terdapat Pertanyaan Dapat Menghubungi
                            Customer Service di Nomor WhatsApp <b>0812 1111 8009</b></p>
                    </div>
                @endif

                {{-- Tombol Batal --}}
                <form wire:submit.prevent="deletePatient('{{ $apt->ap_no }}', '{{ $deleteType }}')"
                    style="margin-top: 15px;">
                    <button type="submit" class="w-100 btn btn-danger text-uppercase" wire:loading.attr="disabled">
                        <i class="ri-close-circle-line me-1"></i> Batal Nomor Antrian
                    </button>
                </form>

            </div>
        @endforeach
    @else
        <div class="receipt-card" style="margin-top: 1rem;">
            <div class="receipt-notes">
                <p>Data Antrian Tidak Ditemukan.</p>
            </div>
        </div>
    @endif

    {{-- Tombol Kembali --}}
    <div class="receipt-card" style="margin-top: 1rem; padding: 1rem 1.5rem;">
        <a href="{{ route('home') }}" class="w-100 btn btn-secondary text-uppercase"
            style="background: linear-gradient(135deg, #6c757d, #495057); color: white; border: none;">
            <i class="ri-arrow-left-line me-1"></i> Kembali Ke Halaman Utama
        </a>
    </div>
</div>
