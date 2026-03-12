<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Maintenance — RS Cahya Kawaluyan</title>
    <link rel="shortcut icon" href="{{ asset('images/rsck_trans.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Figtree', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #e2e8f0;
            overflow: hidden;
            position: relative;
        }

        /* Animated background particles */
        .bg-particles {
            position: fixed;
            inset: 0;
            overflow: hidden;
            z-index: 0;
        }

        .bg-particles .particle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
            animation: float-particle linear infinite;
        }

        .bg-particles .particle:nth-child(1) {
            width: 300px;
            height: 300px;
            background: #3b82f6;
            top: -150px;
            left: 10%;
            animation-duration: 20s;
        }

        .bg-particles .particle:nth-child(2) {
            width: 200px;
            height: 200px;
            background: #8b5cf6;
            bottom: -100px;
            right: 15%;
            animation-duration: 25s;
            animation-delay: -5s;
        }

        .bg-particles .particle:nth-child(3) {
            width: 250px;
            height: 250px;
            background: #06b6d4;
            top: 50%;
            left: -125px;
            animation-duration: 30s;
            animation-delay: -10s;
        }

        @keyframes float-particle {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            25% {
                transform: translate(50px, -80px) scale(1.1);
            }

            50% {
                transform: translate(-30px, 60px) scale(0.9);
            }

            75% {
                transform: translate(70px, 30px) scale(1.05);
            }
        }

        .maintenance-container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem 1.5rem;
            max-width: 520px;
            width: 100%;
        }

        /* Logo */
        .logo {
            margin-bottom: 2rem;
        }

        .logo img {
            height: 60px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 12px;
        }

        /* Animated gear icon */
        .icon-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 120px;
            height: 120px;
            margin-bottom: 2rem;
        }

        .icon-glow {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
            animation: pulse-glow 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.3);
                opacity: 1;
            }
        }

        .gear-icon {
            width: 64px;
            height: 64px;
            animation: spin-gear 8s linear infinite;
        }

        .gear-icon svg {
            width: 100%;
            height: 100%;
            fill: none;
            stroke: #60a5fa;
            stroke-width: 1.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        @keyframes spin-gear {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Text */
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
        }

        .message {
            font-size: 1rem;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        /* Status bar */
        .status-bar {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 100px;
            padding: 0.5rem 1.25rem;
            font-size: 0.85rem;
            color: #60a5fa;
            margin-bottom: 2.5rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #3b82f6;
            animation: blink 1.5s ease-in-out infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        /* Divider */
        .divider {
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #334155, transparent);
            margin: 0 auto 1.5rem;
        }

        /* Footer */
        .footer-text {
            font-size: 0.8rem;
            color: #475569;
        }

        .footer-text a {
            color: #64748b;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .maintenance-container {
                padding: 1.5rem 1rem;
            }

            h1 {
                font-size: 1.4rem;
            }

            .message {
                font-size: 0.9rem;
            }

            .icon-wrapper {
                width: 100px;
                height: 100px;
            }

            .gear-icon {
                width: 48px;
                height: 48px;
            }
        }
    </style>
</head>

<body>
    <!-- Background particles -->
    <div class="bg-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="maintenance-container">
        <!-- Logo -->
        <div class="logo">
            <img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="RS Cahya Kawaluyan">
        </div>

        <!-- Animated Icon -->
        <div class="icon-wrapper">
            <div class="icon-glow"></div>
            <div class="gear-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                    <path
                        d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-1.415 3.417 2 2 0 0 1-1.415-.587l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" />
                </svg>
            </div>
        </div>

        <!-- Title -->
        <h1>Sedang Dalam Pemeliharaan</h1>

        <!-- Message -->
        <p class="message">
            {{ $message ?? 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.' }}
        </p>

        <!-- Status indicator -->
        <div class="status-bar">
            <span class="status-dot"></span>
            Proses pemeliharaan sedang berlangsung
        </div>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Footer -->
        <p class="footer-text">
            &copy; {{ date('Y') }} RS Cahya Kawaluyan
        </p>
    </div>
</body>

</html>
