<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="@yield('meta-description', 'ورود به پنل ادمین')" />
    <title>@yield('title', 'ورود') | {{ config('app.name', 'AliShaghaghi') }}</title>
    <link rel="icon" href="{{ admin_logo_url() }}" type="image/png" />

    @include('admin::partials.styles')

    @livewireStyles

    <style>
        /* Auth background mesh */
        .bg-mesh {
            background:
                radial-gradient(ellipse 90% 70% at 15% 20%, rgba(99, 102, 241, 0.18), transparent 55%),
                radial-gradient(ellipse 70% 55% at 85% 75%, rgba(139, 92, 246, 0.14), transparent 50%),
                radial-gradient(ellipse 50% 40% at 50% 100%, rgba(129, 140, 248, 0.1), transparent 45%),
                linear-gradient(180deg, #020617 0%, #0f172a 45%, #020617 100%);
            animation: mesh-shift 14s ease-in-out infinite;
        }

        @keyframes mesh-shift {
            0%, 100% { opacity: 0.55; }
            50% { opacity: 0.85; }
        }

        /* Floating orbs */
        .orb {
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;
            filter: blur(70px);
        }

        .orb-1 {
            width: 300px;
            height: 300px;
            top: 5%;
            right: 0%;
            background: rgba(99, 102, 241, 0.4);
            animation: orb-float-1 18s ease-in-out infinite;
        }

        .orb-2 {
            width: 240px;
            height: 240px;
            bottom: 10%;
            left: 5%;
            background: rgba(139, 92, 246, 0.32);
            animation: orb-float-2 22s ease-in-out infinite;
        }

        .orb-3 {
            width: 200px;
            height: 200px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(129, 140, 248, 0.22);
            animation: orb-float-3 16s ease-in-out infinite;
        }

        @keyframes orb-float-1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -40px) scale(1.08); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }

        @keyframes orb-float-2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-35px, 35px) scale(1.12); }
        }

        @keyframes orb-float-3 {
            0%, 100% { transform: translate(-50%, -50%) scale(1.05); }
            40% { transform: translate(-50%, -50%) translate(25px, 30px) scale(0.9); }
            80% { transform: translate(-50%, -50%) translate(-30px, -25px) scale(1.1); }
        }

        /* Grid lines */
        .bg-grid-lines {
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.04) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Card animation */
        .animate-card-enter {
            animation: card-enter 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        @keyframes card-enter {
            from {
                opacity: 0;
                transform: translateY(28px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* OTP input styles */
        .otp-input {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            outline: none;
            transition: all 0.2s;
            background-color: rgba(255, 255, 255, 0.04);
            caret-color: #6366f1;
        }

        .otp-input::placeholder {
            color: transparent;
        }

        .otp-input:focus {
            border-color: #6366f1;
            transform: scale(1.03);
            background-color: rgba(99, 102, 241, 0.08);
            box-shadow:
                0 0 0 3px rgba(99, 102, 241, 0.3),
                0 0 24px rgba(99, 102, 241, 0.25);
        }

        .otp-input.filled {
            border-color: rgba(99, 102, 241, 0.7);
            background-color: rgba(99, 102, 241, 0.1);
        }

        .otp-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.3);
        }

        .otp-input.is-valid {
            border-color: #22c55e;
            color: #4ade80;
            background-color: rgba(34, 197, 94, 0.12);
            box-shadow: 0 0 16px rgba(34, 197, 94, 0.25);
        }

        /* Button styles */
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #4338ca 100%);
            background-size: 200% 200%;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        }

        .btn-primary:hover:not(:disabled) {
            background-position: 100% 0;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.45);
            transform: translateY(-1px);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 50%, #15803d 100%);
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3);
        }

        .btn-success:hover {
            box-shadow: 0 10px 40px rgba(34, 197, 94, 0.45);
            transform: translateY(-1px);
        }

        .btn-back {
            color: #cbd5e1;
            border: 1px solid #475569;
            background-color: rgba(15, 23, 42, 0.6);
            transition: all 0.2s;
        }

        .btn-back:hover {
            color: white;
            border-color: rgba(99, 102, 241, 0.4);
            background-color: rgba(30, 41, 59, 0.8);
        }

        /* Card glow */
        .auth-card {
            border-color: #1e293b;
            box-shadow:
                0 0 32px rgba(99, 102, 241, 0.22),
                0 0 64px rgba(99, 102, 241, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        /* Pulse glow - shadow effect */
        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 15px 2px rgba(99, 102, 241, 0.2); }
            50% { box-shadow: 0 0 25px 5px rgba(99, 102, 241, 0.4); }
        }

        /* Global form field border-radius */
        .dark input:not([type=checkbox]):not([type=radio]),
        .dark select,
        .dark textarea {
            border-radius: 10px;
        }

        /* Animated gradient border wrapper */
        .gradient-border-wrap {
            position: relative;
            border-radius: 10px;
            padding: 2px;
            background: #334155;
            transition: all 0.3s ease;
        }

        .gradient-border-wrap::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 10px;
            padding: 2px;
            background: linear-gradient(
                var(--gradient-angle, 0deg),
                #6366f1,
                #8b5cf6,
                #a855f7,
                #c084fc,
                #818cf8,
                #6366f1
            );
            -webkit-mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .gradient-border-wrap:focus-within {
            background: transparent;
        }

        .gradient-border-wrap:focus-within::before {
            opacity: 1;
            animation: rotate-gradient 2s linear infinite;
        }

        @keyframes rotate-gradient {
            0% { --gradient-angle: 0deg; }
            100% { --gradient-angle: 360deg; }
        }

        /* Fallback animation for browsers without @property */
        @supports not (background: paint(something)) {
            .gradient-border-wrap::before {
                background: linear-gradient(
                    0deg,
                    #6366f1, #8b5cf6, #a855f7, #c084fc, #818cf8, #6366f1
                );
                background-size: 300% 300%;
                animation: gradient-move 2s linear infinite;
            }
        }

        @keyframes gradient-move {
            0% { background-position: 0% 0%; }
            25% { background-position: 100% 0%; }
            50% { background-position: 100% 100%; }
            75% { background-position: 0% 100%; }
            100% { background-position: 0% 0%; }
        }

        /* Inner input wrapper */
        .gradient-border-wrap > .input-inner {
            position: relative;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            z-index: 1;
        }

        /* Success animation */
        .success-check-wrap {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            width: 6rem;
            height: 6rem;
        }

        .success-check-ring {
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            border: 2px solid rgba(34, 197, 94, 0.4);
            animation: success-ring 0.7s ease-out forwards;
        }

        .success-check-circle {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 5rem;
            height: 5rem;
            border-radius: 9999px;
            background-color: rgba(34, 197, 94, 0.15);
            border: 2px solid #22c55e;
            animation: success-pop 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            box-shadow: 0 0 40px rgba(34, 197, 94, 0.35);
        }

        @keyframes success-pop {
            0% { transform: scale(0.5); opacity: 0; }
            70% { transform: scale(1.08); }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes success-ring {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1.15); opacity: 0; }
        }

        .success-check-path {
            stroke: #4ade80;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: success-draw 0.45s 0.35s ease forwards;
        }

        @keyframes success-draw {
            to { stroke-dashoffset: 0; }
        }

        /* Shake animation */
        .animate-shake {
            animation: shake 0.55s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            15% { transform: translateX(-10px); }
            30% { transform: translateX(10px); }
            45% { transform: translateX(-8px); }
            60% { transform: translateX(8px); }
            75% { transform: translateX(-4px); }
            90% { transform: translateX(4px); }
        }
    </style>
</head>
<body class="min-h-screen antialiased">

    <!-- Background -->
    <div id="bg-layer" class="fixed inset-0 z-0 overflow-hidden bg-mesh transition-all duration-700" aria-hidden="true">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="absolute inset-0 opacity-50 pointer-events-none bg-grid-lines"></div>
    </div>

    <!-- Main Content -->
    <main class="relative z-10 min-h-screen flex items-center justify-center p-4">
        @yield('content')
    </main>

    @include('admin::partials.scripts')
    @livewireScripts
</body>
</html>