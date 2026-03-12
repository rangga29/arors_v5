<div>
    {{-- HEADER UTAMA: Logo + Judul (hanya 1x) --}}
    <div class="receipt-card">
        <div class="receipt-header">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="RS Cahya Kawaluyan">
            </a>
            <h2>HASIL CEK ANTRIAN PASIEN</h2>
            <span class="receipt-subtitle">BUKTI PENDAFTARAN</span>
        </div>
    </div>

    @if (!empty($appointmentList))
        @foreach ($appointmentList as $index => $appointment)
            @php
                $customerType = $appointment['CustomerType'] ?? 'Pribadi';
                $serviceUnitName = strtoupper($appointment['ServiceUnitName'] ?? '');
                $departmentID = $appointment['DepartmentID'] ?? 'OUTPATIENT';
                $paramedicCode = $appointment['ParamedicCode'] ?? '';
                $startDate = $appointment['StartDate'] ?? '';

                // Detect type
                $isFisio = $departmentID === 'DIAGNOSTIC' || str_contains($serviceUnitName, 'FISIOTERAPI');
                $isRehabMedik = !$isFisio && str_contains($serviceUnitName, 'REHAB');
                $isSundayClinic = false;
                if ($startDate) {
                    try {
                        $isSundayClinic = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->isSunday();
                    } catch (\Exception $e) {
                    }
                }

                // Detect payment type
                $isAsuransi = $customerType !== 'Pribadi' && strtoupper($customerType) !== 'BPJS';
                $isBpjs = strtoupper($customerType) === 'BPJS';

                // For fisio: ParamedicCode FIS002 = BPJS
                if ($isFisio && $paramedicCode === 'FIS002') {
                    $isBpjs = true;
                    $isAsuransi = false;
                }

                // Determine header title (Pasien Baru style for NIK users)
                if ($isFisio) {
                    if ($isBpjs) {
                        $headerTitle = 'REGISTRASI PASIEN BARU FISIOTERAPI BPJS';
                    } elseif ($isAsuransi) {
                        $headerTitle = 'REGISTRASI PASIEN BARU FISIOTERAPI ASURANSI / KONTRAKTOR';
                    } else {
                        $headerTitle = 'REGISTRASI PASIEN BARU FISIOTERAPI UMUM';
                    }
                } elseif ($isRehabMedik) {
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

                $session = $appointment['_session'] ?? null;
                $registrationTime = $appointment['_registration_time'] ?? '-';
            @endphp

            <div class="receipt-card" style="margin-top: 1rem;">
                <div class="receipt-header" style="padding: 0.5rem 0 0.75rem;">
                    <h2 style="font-size: 1rem; margin: 0;">{{ $headerTitle }}</h2>
                </div>

                <div class="data-row">
                    <span class="data-label">Kode Appointment</span>
                    <span class="data-value"><span
                            class="receipt-badge">{{ $appointment['AppointmentNo'] }}</span></span>
                </div>
                <div class="data-row">
                    <span class="data-label">NIK</span>
                    <span class="data-value">{{ $appointment['SSN'] ?? '-' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nama Pasien</span>
                    <span class="data-value text-uppercase">{{ $appointment['PatientName'] }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Tanggal Pendaftaran</span>
                    <span class="data-value text-uppercase">
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $appointment['StartDate'])->isoFormat('dddd, DD MMMM YYYY') }}
                    </span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nama Klinik</span>
                    <span class="data-value text-uppercase">{{ $appointment['ServiceUnitName'] }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nama Dokter</span>
                    <span class="data-value text-uppercase">{{ $appointment['ParamedicName'] }}</span>
                </div>
                @if ($session)
                    <div class="data-row">
                        <span class="data-label">Sesi</span>
                        <span class="data-value">SESI {{ $session }}</span>
                    </div>
                @endif
                <div class="data-row">
                    <span class="data-label">No Antrian</span>
                    <span class="data-value"><span class="receipt-badge">{{ $appointment['QueueNo'] }}</span></span>
                </div>
                @if ($isAsuransi)
                    <div class="data-row">
                        <span class="data-label">Nama Asuransi</span>
                        <span
                            class="data-value text-uppercase">{{ !empty($appointment['BusinessPartnerName']) ? $appointment['BusinessPartnerName'] : 'ASURANSI / KONTRAKTOR' }}</span>
                    </div>
                @endif
                <div class="data-row">
                    <span class="data-label">Waktu Daftar Ulang</span>
                    <span class="data-value">{{ $registrationTime }} WIB</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Status Appointment</span>
                    <span class="data-value">
                        @if (($appointment['AppointmentStatus'] ?? '') === 'Started')
                            Aktif
                        @elseif (($appointment['AppointmentStatus'] ?? '') === 'Deleted')
                            Tidak Aktif
                        @else
                            {{ $appointment['AppointmentStatus'] ?? '-' }}
                        @endif
                    </span>
                </div>

                {{-- Notes sesuai jenis --}}
                @if ($isFisio)
                    <div class="receipt-notes">
                        <p>* Mohon datang tepat waktu sesuai waktu pendaftaran ulang</p>
                        <p>* Nomor antrian ini adalah nomor antrian tindakan di fisioterapi</p>
                        <p>* Apabila pasien datang tidak sesuai jam daftar ulang, maka syarat dan ketentuan berlaku</p>
                        <p>* Pasien fisioterapi wajib membawa surat pengantar dari dokter rehabilitasi medik</p>
                    </div>
                @elseif ($isRehabMedik || $isSundayClinic)
                    <div class="receipt-notes">
                        <p>* Untuk Konfirmasi Kepastian Jadwal Praktek Dokter / Terdapat Pertanyaan Dapat Menghubungi
                            Customer Service di Nomor WhatsApp <b>0812 1111 8009</b></p>
                    </div>
                @else
                    <div class="receipt-notes">
                        <p>* Nomor Antrian Ini <strong>TIDAK DAPAT</strong> Digunakan Untuk Mendaftar Sebagai Pasien
                            BPJS <strong>KECUALI</strong> Pasien Klinik Rehabilitasi Medik</p>
                        @if (!$isAsuransi)
                            <p>* Silahkan Melakukan Registrasi Ulang Secara Mandiri Pada Anjungan Pendaftaran Mandiri
                                (APM)
                                di depan pintu Klinik Kamilus</p>
                        @endif
                        <p>* Untuk Konfirmasi Kepastian Jadwal Praktek Dokter / Terdapat Pertanyaan Dapat Menghubungi
                            Customer Service di Nomor WhatsApp <b>0812 1111 8009</b></p>
                    </div>
                @endif
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
        <a href="{{ route('home') }}" class="w-100 btn btn-danger text-uppercase">
            <i class="ri-arrow-left-line me-1"></i> Kembali Ke Halaman Utama
        </a>
    </div>
</div>
