<div class="leftside-menu">
    <a href="{{ route('dashboard') }}" class="logo logo-light">
        <span class="logo-lg">
            <img src="{{ asset('images/rsck_arors.png') }}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('images/rsck_logo_back.png') }}" alt="small logo">
        </span>
    </a>
    <a href="{{ route('dashboard') }}" class="logo logo-dark">
        <span class="logo-lg">
            <img src="{{ asset('images/rsck_arors_dark.png') }}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('images/rsck_logo_back.png') }}" alt="small logo">
        </span>
    </a>
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>
    <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div>
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <div class="leftbar-user">
            <a href="{{ route('second', ['pages', 'profile']) }}">
                <img src="{{ asset('images/rsck_logo.png') }}" alt="user-image" height="42" class="rounded-circle shadow-sm">
                <span class="leftbar-user-name mt-2">{{ auth()->user()->name }}</span>
            </a>
        </div>
        <ul class="side-nav">
            <li class="side-nav-title">Menu</li>
            <li class="side-nav-item">
                <a href="{{ route('any', ['dashboard']) }}" class="side-nav-link">
                    <i class="ri-dashboard-fill"></i>
                    <span> Dashboard </span>
                </a>
            </li>
            @can('view appointments')
                <li class="side-nav-item">
                    <a href="{{ route('second', ['appointments',  \Carbon\Carbon::today()->format('Y-m-d')]) }}" class="side-nav-link">
                        <i class="ri-book-2-fill"></i>
                        <span> Data Appointment </span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('third', ['appointments', 'fisioterapi', \Carbon\Carbon::today()->format('Y-m-d')]) }}" class="side-nav-link">
                        <i class="ri-book-3-fill"></i>
                        <span> Data Fisioterapi </span>
                    </a>
                </li>
            @endcan
            @can('view schedule dates')
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarSchedule" aria-expanded="false" aria-controls="sidebarSchedule" class="side-nav-link">
                        <i class="ri-calendar-todo-fill"></i>
                        <span> Data Jadwal Dokter </span>
                    </a>
                    <div class="collapse" id="sidebarSchedule">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ route('second', ['schedules', 'dates']) }}">Data Tanggal</a>
                            </li>
                            <li>
                                <a href="{{ route('second', ['schedules',  \Carbon\Carbon::today()->format('Y-m-d')]) }}">Data Jadwal</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endcan
            @can('view clinics')
                <li class="side-nav-item">
                    <a href="{{ route('any', ['clinics']) }}" class="side-nav-link">
                        <i class="ri-hospital-fill"></i>
                        <span> Data Klinik </span>
                    </a>
                </li>
            @endcan
            @can('view users')
                <li class="side-nav-item">
                    <a href="{{ route('any', ['users']) }}" class="side-nav-link">
                        <i class="ri-user-3-fill"></i>
                        <span> Data User </span>
                    </a>
                </li>
            @endcan
            @can('view logs')
                <li class="side-nav-item">
                    <a href="{{ route('any', ['logs']) }}" class="side-nav-link">
                        <i class="ri-file-list-3-line"></i>
                        <span> Data Logs </span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('any', ['qrcarolus']) }}" class="side-nav-link">
                        <i class="ri-file-list-3-line"></i>
                        <span> Data QR Carolus </span>
                    </a>
                </li>
            @endcan
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
