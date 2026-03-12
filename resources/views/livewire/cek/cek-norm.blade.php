@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css'])
@endsection

<main class="background">
    <div wire:loading wire:target="checkPatient" id="overlay-form" style="display: none;">
        <div class="d-flex justify-content-center spinner-container">
            <div class="spinner-border" role="status"></div>
        </div>
    </div>

    @if (!$isHaveAppointment)
        <div class="reg-card">
            <div class="reg-header">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="RS Cahya Kawaluyan">
                </a>
                <h2>CEK NOMOR ANTRIAN</h2>
                <span class="reg-badge">Cek Dengan NORM</span>
            </div>

            <p class="reg-description">
                Masukkan No Rekam Medis (NORM), Tanggal Lahir, dan Tanggal Berobat untuk melihat data antrian Anda.
            </p>

            @if (session()->has('error'))
                <div class="reg-announcement">
                    <div class="reg-announcement-content">{{ session('error') }}</div>
                </div>
            @endif

            <form wire:submit.prevent="checkPatient">
                <div class="reg-section-title">
                    <i class="ri-search-line"></i> Data Pencarian
                </div>

                <div class="reg-form-group">
                    <label for="norm" class="reg-label">No Rekam Medis (NORM)</label>
                    <input type="text" class="reg-input" name="norm" id="norm" wire:model="norm"
                        placeholder="Masukkan No Rekam Medis" maxlength="8"
                        oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                </div>
                <div class="reg-form-group">
                    <label for="birthday" class="reg-label">Tanggal Lahir</label>
                    <div x-data="{ birthday: '' }">
                        <input type="text" class="reg-input" name="birthday" id="birthday" wire:model="birthday"
                            x-model="birthday" x-mask="99/99/9999" placeholder="dd/mm/yyyy" autocomplete="on" required>
                    </div>
                </div>
                <div class="reg-form-group">
                    <label for="selectedDate" class="reg-label">Tanggal Berobat</label>
                    <select class="reg-select" id="selectedDate" wire:model.live="selectedDate" required>
                        <option value="" selected>Pilih Tanggal Berobat</option>
                        @foreach ($appointmentDates as $appointmentDate)
                            <option value="{{ $appointmentDate->sd_date }}">
                                {{ \Carbon\Carbon::createFromFormat('Y-m-d', $appointmentDate->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="reg-submit-btn" wire:loading.attr="disabled">
                    <i class="ri-search-line"></i> Cek Antrian
                </button>
            </form>

            <a href="{{ route('cek-antrian.nik') }}" class="reg-submit-btn mt-2"
                style="display: block; text-align: center; text-decoration: none; background: linear-gradient(135deg, #6c757d, #495057); margin-top: 8px;">
                <i class="ri-file-list-3-line me-1"></i> Cek Antrian Dengan NIK
            </a>
        </div>
    @else
        @livewire('cek.show-cek-data', ['appointmentList' => $appointmentList])
    @endif
</main>

@section('script')
    @vite(['resources/js/customs/patient-check-form.js'])
@endsection
