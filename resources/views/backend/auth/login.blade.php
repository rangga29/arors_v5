<!doctype html>
<html lang="en" data-sidenav-size="sm-hover">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <title>ARORS ADMINISTRATOR</title>

    @yield('css')
    @vite(['resources/scss/app.scss', 'resources/scss/icons.scss', 'resources/js/head.js'])
</head>
<body class="authentication-bg position-relative">
    <div class="position-absolute start-0 end-0 start-0 bottom-0 w-100 h-100">
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="100%" height="100%" preserveAspectRatio="none" viewBox="0 0 1920 1028">
            <g mask="url(&quot;#SvgjsMask1166&quot;)" fill="none">
                <use xlink:href="#SvgjsSymbol1173" x="0" y="0"></use>
                <use xlink:href="#SvgjsSymbol1173" x="0" y="720"></use>
                <use xlink:href="#SvgjsSymbol1173" x="720" y="0"></use>
                <use xlink:href="#SvgjsSymbol1173" x="720" y="720"></use>
                <use xlink:href="#SvgjsSymbol1173" x="1440" y="0"></use>
                <use xlink:href="#SvgjsSymbol1173" x="1440" y="720"></use>
            </g>
            <defs>
                <mask id="SvgjsMask1166">
                    <rect width="1920" height="1028" fill="#ffffff"></rect>
                </mask>
                <path d="M-1 0 a1 1 0 1 0 2 0 a1 1 0 1 0 -2 0z" id="SvgjsPath1171"></path>
                <path d="M-3 0 a3 3 0 1 0 6 0 a3 3 0 1 0 -6 0z" id="SvgjsPath1170"></path>
                <path d="M-5 0 a5 5 0 1 0 10 0 a5 5 0 1 0 -10 0z" id="SvgjsPath1169"></path>
                <path d="M2 -2 L-2 2z" id="SvgjsPath1168"></path>
                <path d="M6 -6 L-6 6z" id="SvgjsPath1167"></path>
                <path d="M30 -30 L-30 30z" id="SvgjsPath1172"></path>
            </defs>
            <symbol id="SvgjsSymbol1173">
                <use xlink:href="#SvgjsPath1167" x="30" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="30" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="30" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="30" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="30" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="30" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="30" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="30" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="30" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="30" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="30" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="30" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="90" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="90" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="90" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="90" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="90" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="90" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="90" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="90" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="90" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="90" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="90" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="90" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="150" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="150" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="150" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="150" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="150" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="150" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="150" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="150" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="150" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="150" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="150" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="150" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="210" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="210" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="210" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="210" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="210" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="270" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="270" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="270" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="270" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="270" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="270" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="270" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="270" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="270" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="270" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1172" x="270" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)" stroke-width="3"></use>
                <use xlink:href="#SvgjsPath1171" x="270" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="330" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="330" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="330" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="330" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="330" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="330" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="330" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="330" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="330" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="330" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="330" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="330" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="390" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="390" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="390" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="390" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="390" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="390" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="390" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="390" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="390" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="390" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="390" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="390" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="450" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="450" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="450" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="450" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="450" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="450" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="450" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="450" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="450" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="450" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1172" x="450" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)" stroke-width="3"></use>
                <use xlink:href="#SvgjsPath1168" x="450" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="510" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="510" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1172" x="510" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)" stroke-width="3"></use>
                <use xlink:href="#SvgjsPath1171" x="510" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="510" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="510" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="510" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="510" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="510" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="510" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="570" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="570" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="570" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="570" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="570" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="570" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="570" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="570" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="570" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="570" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="570" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="570" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="630" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="630" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="630" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="630" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="630" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="630" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="630" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="690" y="30" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="90" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1170" x="690" y="150" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="210" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="270" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="330" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="390" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1167" x="690" y="450" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="690" y="510" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1169" x="690" y="570" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1168" x="690" y="630" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
                <use xlink:href="#SvgjsPath1171" x="690" y="690" stroke="rgba(var(--ct-primary-rgb), 0.20)"></use>
            </symbol>
        </svg>
    </div>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">
                        <div class="card-header py-3 text-center bg-secondary bg-opacity-75">
                            <a href="{{ route('any', 'index') }}">
                                <span><img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="logo" height="60"></span>
                            </a>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center w-75 m-auto">
                                <h2 class="text-dark-50 text-center pb-0 fw-bold">ARORS</h2>
                                <h5 class="text-dark-50 text-center pb-0 fw-bold">LOGIN ADMINISTRATOR</h5>
                            </div>
                            <form method="POST" action="{{ route('login') }}">
                                @if (sizeof($errors) > 0)
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li class="text-danger">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                @csrf
                                <div class="mb-3">
                                    <label for="username" class="form-label">USERNAME</label>
                                    <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">PASSWORD</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                                        <div class="input-group-text" data-password="false">
                                            <span class="password-eye"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 mb-0 text-center">
                                    <button class="btn btn-primary fw-bolder" type="submit"> LOGIN </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer footer-alt fw-medium">
            <span class="bg-body">
                <script>
                    document.write(new Date().getFullYear())
                </script> © RUMAH SAKIT CAHYA KAWALUYAN
            </span>
    </footer>

    @vite(['resources/js/app.js'])
    @yield('script')
    @yield('script-bottom')
</body>
</html>
