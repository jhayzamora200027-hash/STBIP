<!DOCTYPE html>
<html lang="en">
<head>
        <style>
            .stb-navbar-title {
                transition: all 0.2s;
            }
            .stb-nav-avatar {
                width: 30px;
                height: 30px;
                border-radius: 10px;
                object-fit: cover;
                border: 1px solid rgba(255,255,255,0.28);
                box-shadow: 0 6px 12px rgba(0,0,0,0.14);
            }
            .stb-nav-avatar-fallback {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 30px;
                height: 30px;
                border-radius: 10px;
                background: rgba(255,255,255,0.14);
                border: 1px solid rgba(255,255,255,0.22);
                font-size: 0.78rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                color: #fff;
                box-shadow: 0 6px 12px rgba(0,0,0,0.12);
            }
            @media (max-width: 600px) {
                .stb-navbar-title {
                    font-size: 1.05rem !important;
                    padding-right: 0.2rem !important;
                    max-width: 55%;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                .navbar .dropdown-toggle .fw-semibold,
                .navbar .dropdown-toggle .bi-person-circle {
                    font-size: 0.95rem !important;
                }
                .navbar-toggler {
                    transform: scale(0.8);
                    margin-left: 0.2rem;
                }
                .navbar .container-fluid.d-flex {
                    flex-wrap: nowrap !important;
                }
            }
            @media (min-width: 901px) {
                .stb-navbar-title {
                    margin-left: 300px !important;
                }
            }
            @media (max-width: 900px) {
                .stb-navbar-title {
                    margin-left: 0 !important;
                }
            }
        </style>
    <meta charset="UTF-8">
    @php
        $favFile = public_path('images/dattachments/social technology bureau innovating solution logo.png');
        $favData = null;
        if (file_exists($favFile) && is_readable($favFile)) {
            $favData = 'data:image/png;base64,' . base64_encode(file_get_contents($favFile));
        }
    @endphp
    @php
        $favAsset = request()->isSecure() ? secure_asset('images/dattachments/social technology bureau innovating solution logo.png') : asset('images/dattachments/social technology bureau innovating solution logo.png');
    @endphp
    <link rel="icon" href="{{ $favData ?? $favAsset }}">
    <link rel="shortcut icon" href="{{ $favData ?? $favAsset }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>STB Inventory Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link href="/css/select2.min.css" rel="stylesheet" />
    <style>
    .select2-container--default .select2-selection--multiple {
        background: #f8fafc;
        border: 2px solid #06306e;
        border-radius: 10px;
        min-height: 44px;
        padding: 6px 8px;
        box-shadow: 0 2px 8px rgba(16, 174, 181, 0.08);
        font-size: 1.08rem;
        transition: border 0.2s;
    }
    .select2-container--default .select2-selection--multiple:focus {
        border: 2px solid #06306e;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: linear-gradient(90deg, #06306e 60%, #06306e 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        margin: 2px 6px 2px 0;
        padding: 4px 10px;
        font-weight: 500;
        box-shadow: 0 1px 4px #000000;
    }
    .select2-container--default .select2-selection--multiple .select2-search__field {
        background: transparent;
        color: #222;
        font-size: 1.05rem;
    }
    .select2-dropdown {
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(16, 174, 181, 0.13);
        border: 2px solid #06306e;
        padding: 4px 0;
    }
    .select2-results__option {
        padding: 8px 14px;
        font-size: 1.07rem;
        border-radius: 6px;
    }
    .select2-results__option input[type=checkbox] {
        margin-right: 6px;
    }
    .select2-results__option--highlighted {
        background: #e0f2f1;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
        color: #8492a6;
    }
    .st-dashboard-card {
        background: #f8fafc;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(16, 174, 181, 0.07);
        margin-bottom: 24px;
        border: 2.5px solid rgba(16,174,181,0.65);
        transition: transform 0.15s, box-shadow 0.15s;
        min-width: 320px;
        max-width: 340px;
        min-height: 180px;
        display:flex;
        flex-direction:column;
        justify-content:center;
        align-items:center;
    }
    .st-dashboard-card:hover {
        transform: translateY(-4px) scale(1.03);
        box-shadow: 0 8px 24px rgba(16, 174, 181,0.18);
    }
    .st-dashboard-card .card-header {
        background: linear-gradient(90deg, #06306e 60%, #06306e 100%);
        color: #fff;
        font-weight:600;
        font-size:1.15rem;
        border-radius:14px 14px 0 0;
        border:none;
        padding:14px 0;
        letter-spacing:0.5px;
        width:100%;
        margin:0;
        text-align:center;
        box-sizing:border-box;
        display:block;
    }
    .st-dashboard-card .card-body {
        padding: 28px 0 16px 0;
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        html, body {
            height: 100%;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .stb-main-content {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
        }

        /* Mobile-only: remove container side padding and center dashboard wrapper for all users */
        @media (max-width: 767px) {
            .container.stb-main-content, .stb-main-content {
                padding-left: 0 !important;
                padding-right: 0 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            .st-center-outer { justify-content: center !important; padding-left: 0 !important; padding-right: 0 !important; }
            html, body { overflow-x: hidden !important; }
        }

        @if(request()->query('embed') || (isset($embed) && $embed))
        nav.navbar,
        .stb-sidebar,
        footer {
            display: none !important;
        }
        body, html {
            margin: 0 !important;
            overflow: hidden !important;
            width: 100% !important;
            height: 100% !important;
        }
        .stb-main-content {
            padding: 0 !important;
            margin: 0 !important;
        }
        @endif
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.hidden {
            display: none;
        }

        .loader10{width:100px;height:100px;margin:50px auto;overflow:hidden;position:relative}
        .loader10 span{display:inline-block;position:absolute;animation:loading-10 9s cubic-bezier(.45,.05,.55,.95) infinite}
        .loader10 span:nth-child(1){background:#ff4b7d;animation-name:loading-10}
        .loader10 span:nth-child(2){background:#3485ef;animation-name:loading-102}
        .loader10 span:nth-child(3){background:#5fad56;animation-name:loading-103}
        .loader10 span:nth-child(4){background:#e9573d;animation-name:loading-104}

        @keyframes loading-10{
            0%,5%{width:25%;height:25%;border-radius:100% 0 0;background:#ff4b7d;bottom:50%;left:25%}
            10%{width:25%;height:25%;border-radius:100% 100% 0 0;background:#ff4b7d;bottom:50%;left:25%}
            13%,18%{width:25%;height:25%;border-radius:100% 100% 0 0;background:#5fad56;bottom:50%;left:12.5%}
            20%{width:32.5%;height:32.5%;border-radius:50%;background:#5fad56;bottom:50%;left:6.25%}
            25%,30%{width:25%;height:25%;border-radius:50%;background:#3485ef;bottom:62.5%;left:12.5%}
            35%{width:14%;height:10%;border-radius:999px;background:#ff4b7d;left:0;bottom:0}
            40%,60%{height:100%}
            55%{height:10%}
            70%{width:14%;height:25%;border-radius:999px;background:#ff4b7d;bottom:0;left:0}
            75%,97%{width:25%;height:25%;border-radius:100%;bottom:57.5%;left:17.5%}
            100%{width:50%;height:50%;border-radius:100%;bottom:25%;left:25%}
        }

        @keyframes loading-102{
            0%,5%{width:25%;height:25%;background:#ff4b7d;border-radius:0 0 0 100%;bottom:25%;left:25%}
            10%{width:25%;height:25%;background:#ff4b7d;border-radius:0 0 100% 100%;bottom:25%;left:25%}
            13%,18%{width:25%;height:25%;background:#5fad56;border-radius:0 0 100% 100%;bottom:25%;left:12.5%}
            20%{width:32.5%;height:32.5%;background:#5fad56;border-radius:50%;bottom:25%;left:6.25%}
            25%{width:25%;height:25%;background:#3485ef;border-radius:50%;bottom:12.5%;left:12.5%}
            30%{left:12.5%;bottom:12.5%;border-radius:50%;height:25%;width:25%;background:#3485EF}
            35%{left:28%;bottom:0;border-radius:999px;height:10%;width:14%;background:#3485EF}
            40%,60%{height:10%}
            45%,65%{height:100%}
            75%{left:28%;bottom:0;border-radius:999px;height:25%;width:14%;background:#3485EF}
            80%{left:17.5%;bottom:17.5%;border-radius:100%;height:25%;width:25%}
            97%{left:17.5%;bottom:17.5%;border-radius:100%;height:25%;width:25%;box-shadow:none}
            100%{box-shadow:-3px -3px 5px -5px #3485EF;border-radius:100%;left:25%;bottom:-50%;height:50%;width:50%}
        }

        @keyframes loading-103{
            0%,5%{left:50%;bottom:50%;border-radius:0 100% 0 0;height:25%;width:25%;background:#FF4B7D}
            10%{left:50%;bottom:50%;border-radius:100% 100% 0 0;height:25%;width:25%;background:#FF4B7D}
            13%,18%{left:62.5%;bottom:50%;border-radius:100% 100% 0 0;height:25%;width:25%;background:#5FAD56}
            20%{left:66.25%;bottom:50%;border-radius:50%;height:32.5%;width:32.5%;background:#5FAD56}
            25%,30%{left:62.5%;bottom:62.5%;border-radius:50%;height:25%;width:25%;background:#3485EF}
            35%{left:56%;bottom:0;border-radius:999px;height:10%;width:14%;background:#5FAD56}
            45%,65%{height:10%}
            50%,70%{height:100%}
            80%{left:56%;bottom:0;border-radius:999px;height:25%;width:14%;background:#5FAD56}
            85%{left:57.5%;bottom:57.5%;border-radius:100%;height:25%;width:25%}
            97%{left:57.5%;bottom:57.5%;border-radius:100%;height:25%;width:25%;box-shadow:none}
            100%{box-shadow:-3px -3px 5px -5px #5FAD56;border-radius:100%;left:100%;bottom:25%;height:50%;width:50%}
        }

        @keyframes loading-104{
            0%,5%{left:50%;bottom:25%;border-radius:0 0 100%;height:25%;width:25%;background:#FF4B7D}
            10%{left:50%;bottom:25%;border-radius:0 0 100% 100%;height:25%;width:25%;background:#FF4B7D}
            13%,18%{left:62.5%;bottom:25%;border-radius:0 0 100% 100%;height:25%;width:25%;background:#5FAD56}
            20%{left:66.25%;bottom:25%;border-radius:50%;height:32.5%;width:32.5%;background:#5FAD56}
            25%,30%{left:62.5%;bottom:12.5%;border-radius:50%;height:25%;width:25%;background:#3485EF}
            35%{left:84%;bottom:0;border-radius:999px;height:10%;width:14%;background:#e9573d}
            50%,70%{height:10%}
            55%,75%{height:100%}
            85%{left:84%;bottom:0;border-radius:999px;height:25%;width:14%;background:#E9573D}
            90%{left:57.5%;bottom:17.5%;border-radius:100%;height:25%;width:25%}
            97%{left:57.5%;bottom:17.5%;border-radius:100%;height:25%;width:25%;box-shadow:none}
            100%{box-shadow:-3px -3px 5px -5px #e9573d;border-radius:100%;left:100%;bottom:-50%;height:50%;width:50%}
        }

        body {
            background: url('{{ request()->isSecure() ? secure_asset('images/dattachments/STBIP cover white.png') : asset('images/dattachments/STBIP cover white.png') }}') no-repeat center center fixed;
            background-size: cover;
            background-attachment: fixed;
        }

            .modal-login-bg {
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(2px);
            }
            .modal-login-content {
                background: rgba(255,255,255,0.15);
                box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border-radius: 20px;
                border: 1px solid rgba(255,255,255,0.18);
                padding: 40px 30px 30px 30px;
                color: #fff;
                text-align: center;
            }
            .modal-login-content h2 {
                margin-bottom: 30px;
                font-size: 2rem;
                font-weight: bold;
            }
            .modal-login-content input[type="email"],
            .modal-login-content input[type="password"] {
                width: 100%;
                padding: 12px 10px;
                margin: 10px 0 20px 0;
                border: none;
                border-radius: 8px;
                background: rgba(255,255,255,0.2);
                color: #fff;
                font-size: 1rem;
            }
            .modal-login-content input[type="checkbox"] {
                margin-right: 8px;
            }
            .modal-login-content label {
                font-size: 0.95rem;
            }
            .modal-login-content .actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 25px;
            }
            .modal-login-content .forgot {
                color: #fff;
                text-decoration: underline;
                cursor: pointer;
                font-size: 0.95rem;
            }
            .modal-login-content button[type="submit"] {
                width: 100%;
                padding: 12px 0;
                border: none;
                border-radius: 25px;
                background: #fff;
                color: #7b2ff2;
                font-size: 1.1rem;
                font-weight: bold;
                cursor: pointer;
                margin-bottom: 15px;
                transition: background 0.2s;
            }
            .modal-login-content button[type="submit"]:hover {
                background: #e0e0e0;
            }
            .modal-login-content .register {
                color: #fff;
                font-size: 0.95rem;
            }
            .modal-login-content .register a {
                color: #fff;
                font-weight: bold;
                text-decoration: underline;
            }

            .portal-login-modal .modal-dialog {
                max-width: 1040px;
                margin: 1.75rem auto;
            }
            .portal-auth-modal .modal-dialog {
                transform: translateY(26px) scale(0.965);
                opacity: 0;
                transition: transform 0.38s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3s ease;
            }
            .portal-auth-modal.show .modal-dialog {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            .portal-auth-pane {
                opacity: 0;
                transform: translateY(24px);
            }
            .portal-auth-modal.show .portal-auth-pane {
                animation: portalAuthPaneIn 0.52s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            }
            .portal-auth-modal.show .portal-auth-pane:nth-child(2) {
                animation-delay: 0.08s;
            }
            @keyframes portalAuthPaneIn {
                from {
                    opacity: 0;
                    transform: translateY(24px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .portal-login-frame {
                background: transparent;
                border-radius: 28px;
                overflow: hidden;
                box-shadow: 0 30px 80px rgba(15, 23, 42, 0.28);
            }
            .portal-login-shell {
                display: grid;
                grid-template-columns: minmax(320px, 1.05fr) minmax(360px, 0.95fr);
                min-height: 620px;
                background: linear-gradient(135deg, #f6f8fb 0%, #eef3f8 100%);
            }
            .portal-login-brand-panel {
                position: relative;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                gap: 1.5rem;
                padding: 2.5rem;
                background: linear-gradient(160deg, #0f4c81 0%, #1b6ca8 52%, #d7ecfb 100%);
                color: #fff;
                overflow: hidden;
            }
            .portal-login-brand-panel::before,
            .portal-login-brand-panel::after {
                content: "";
                position: absolute;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.14);
            }
            .portal-login-brand-panel::before {
                width: 260px;
                height: 260px;
                top: -90px;
                right: -100px;
            }
            .portal-login-brand-panel::after {
                width: 220px;
                height: 220px;
                bottom: -110px;
                left: -90px;
            }
            .portal-login-brand-copy,
            .portal-login-brand-footer,
            .portal-login-illustration {
                position: relative;
                z-index: 1;
            }
            .portal-login-kicker {
                display: inline-flex;
                align-items: center;
                width: fit-content;
                padding: 0.38rem 0.8rem;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.16);
                border: 1px solid rgba(255, 255, 255, 0.24);
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }
            .portal-login-brand-copy h2 {
                margin: 1rem 0 0.9rem;
                font-size: clamp(2rem, 3vw, 3rem);
                font-weight: 800;
                line-height: 1.02;
            }
            .portal-login-brand-copy p {
                margin: 0;
                max-width: 28rem;
                color: rgba(255, 255, 255, 0.9);
                font-size: 1rem;
                line-height: 1.7;
            }
            .portal-login-illustration {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 250px;
                position: relative;
            }
            .portal-login-illustration-ring {
                position: absolute;
                width: 280px;
                height: 280px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(255,255,255,0.32) 0%, rgba(255,255,255,0.08) 55%, rgba(255,255,255,0) 70%);
                filter: blur(1px);
            }
            .portal-login-illustration img {
                position: relative;
                z-index: 1;
                width: min(330px, 86%);
                max-height: 260px;
                object-fit: contain;
                filter: drop-shadow(0 20px 35px rgba(8, 47, 73, 0.22));
            }
            .portal-login-brand-footer {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1rem 1.1rem;
                border-radius: 20px;
                background: rgba(7, 34, 56, 0.2);
                border: 1px solid rgba(255, 255, 255, 0.18);
                backdrop-filter: blur(10px);
            }
            .portal-login-brand-footer img {
                width: 54px;
                height: 54px;
                object-fit: contain;
                border-radius: 16px;
                background: rgba(255, 255, 255, 0.88);
                padding: 0.35rem;
            }
            .portal-login-brand-footer strong,
            .portal-login-brand-footer span {
                display: block;
            }
            .portal-login-brand-footer strong {
                font-size: 0.96rem;
                font-weight: 700;
            }
            .portal-login-brand-footer span {
                color: rgba(255, 255, 255, 0.8);
                font-size: 0.86rem;
                line-height: 1.55;
            }
            .portal-login-form-panel {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2.5rem;
                background: rgba(255, 255, 255, 0.94);
            }
            .portal-login-form-wrap {
                width: min(100%, 370px);
            }
            .portal-login-eyebrow {
                margin: 0 0 0.35rem;
                color: #1b6ca8;
                font-size: 0.82rem;
                font-weight: 700;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }
            .portal-login-form-wrap h1 {
                margin: 0;
                color: #203040;
                font-size: clamp(1.9rem, 3vw, 2.35rem);
                font-weight: 800;
                line-height: 1.1;
            }
            .portal-login-subtitle {
                margin: 0.75rem 0 1.8rem;
                color: #5c6c7c;
                font-size: 0.98rem;
                line-height: 1.65;
            }
            .portal-login-alert {
                margin-bottom: 1rem;
                border: 1px solid rgba(191, 40, 44, 0.18);
                border-radius: 16px;
                background: #fff3f3;
                color: #7f1d1d;
                box-shadow: none;
            }
            .portal-login-alert .btn-close {
                margin-top: 0.1rem;
            }
            .portal-login-alert-body {
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
            }
            .portal-login-alert-body i {
                font-size: 1rem;
                line-height: 1.4;
            }
            .portal-login-alert-body ul,
            .portal-login-alert-inline ul {
                margin: 0;
                padding-left: 1rem;
            }
            .portal-login-alert-inline {
                padding: 0.85rem 1rem;
                font-size: 0.95rem;
            }
            .portal-login-field {
                margin-bottom: 1rem;
            }
            .portal-login-field label {
                display: block;
                margin-bottom: 0.5rem;
                color: #314255;
                font-size: 0.9rem;
                font-weight: 700;
            }
            .portal-login-input-wrap {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.1rem 0.95rem;
                border: 1px solid #d5dde6;
                border-radius: 14px;
                background: #fff;
                transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            }
            .portal-login-input-wrap:focus-within {
                border-color: #1b6ca8;
                box-shadow: 0 0 0 4px rgba(27, 108, 168, 0.12);
                transform: translateY(-1px);
            }
            .portal-login-input-wrap i {
                color: #7b8a99;
                font-size: 1rem;
            }
            .portal-login-input-wrap input {
                width: 100%;
                border: 0;
                background: transparent;
                color: #203040;
                padding: 0.95rem 0;
                font-size: 0.98rem;
                outline: none;
                box-shadow: none;
            }
            .portal-password-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.5rem;
                height: 2.5rem;
                border: 0;
                padding: 0;
                border-radius: 999px;
                background: transparent;
                color: #7b8a99;
                transition: background 0.18s ease, color 0.18s ease;
            }
            .portal-password-toggle:hover {
                background: rgba(27, 108, 168, 0.08);
                color: #1b6ca8;
            }
            .portal-password-toggle:focus-visible {
                outline: 2px solid rgba(27, 108, 168, 0.28);
                outline-offset: 2px;
                color: #1b6ca8;
            }
            .portal-login-input-wrap input::placeholder {
                color: #9aa7b5;
            }
            .portal-login-utility-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin: 0.35rem 0 1.6rem;
            }
            .portal-login-check {
                display: inline-flex;
                align-items: center;
                gap: 0.55rem;
                color: #4c5d6e;
                font-size: 0.92rem;
                font-weight: 600;
                cursor: pointer;
            }
            .portal-login-check input {
                width: 1rem;
                height: 1rem;
                accent-color: #1b6ca8;
            }
            .portal-login-help {
                color: #1b6ca8;
                font-size: 0.88rem;
                font-weight: 600;
                text-decoration: none;
                text-align: right;
            }
            .portal-login-help:hover {
                color: #0f4c81;
                text-decoration: underline;
            }
            .portal-login-help-muted {
                color: #6b7b8b;
                cursor: default;
            }
            .portal-login-help-muted:hover {
                color: #6b7b8b;
                text-decoration: none;
            }
            .portal-login-action-row {
                display: flex;
                align-items: center;
                gap: 0.85rem;
            }
            .portal-login-secondary-btn,
            .portal-login-primary-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 52px;
                border-radius: 14px;
                font-size: 0.98rem;
                font-weight: 700;
                transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
            }
            .portal-login-secondary-btn {
                flex: 1;
                border: 1px solid #b9d5e9;
                background: #fff;
                color: #1b6ca8;
            }
            .portal-login-primary-btn {
                flex: 1.2;
                border: 0;
                background: linear-gradient(135deg, #1b6ca8 0%, #0f4c81 100%);
                color: #fff;
                box-shadow: 0 16px 28px rgba(27, 108, 168, 0.24);
            }
            .portal-login-secondary-btn:hover,
            .portal-login-primary-btn:hover {
                transform: translateY(-1px);
            }
            .portal-login-secondary-btn:hover {
                border-color: #1b6ca8;
                background: #eef7fd;
            }
            .portal-login-primary-btn:hover {
                background: linear-gradient(135deg, #155b8d 0%, #0b3e69 100%);
                box-shadow: 0 18px 32px rgba(15, 76, 129, 0.28);
            }
            .portal-login-primary-btn:disabled {
                opacity: 0.8;
                cursor: wait;
                transform: none;
            }
            .portal-register-modal .modal-dialog {
                max-width: 1120px;
                margin: 1.5rem auto;
            }
            .portal-register-frame {
                background: transparent;
            }
            .portal-register-shell {
                display: grid;
                grid-template-columns: minmax(320px, 0.9fr) minmax(460px, 1.1fr);
                 min-height: 0;
                background: linear-gradient(135deg, #f7f9fc 0%, #eef4fa 100%);
            }
            .portal-register-brand-panel {
                position: relative;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                gap: 1.5rem;
                padding: 2.35rem;
                color: #fff;
                background: linear-gradient(160deg, #0f4c81 0%, #1f7a68 48%, #9ed9cf 100%);
                overflow: hidden;
            }
            .portal-register-brand-panel::before,
            .portal-register-brand-panel::after {
                content: "";
                position: absolute;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.14);
            }
            .portal-register-brand-panel::before {
                width: 260px;
                height: 260px;
                top: -90px;
                left: -80px;
            }
            .portal-register-brand-panel::after {
                width: 220px;
                height: 220px;
                right: -90px;
                bottom: -110px;
            }
            .portal-register-brand-copy,
            .portal-register-highlights,
            .portal-register-illustration-wrap {
                position: relative;
                z-index: 1;
            }
            .portal-register-kicker {
                display: inline-flex;
                align-items: center;
                width: fit-content;
                padding: 0.38rem 0.8rem;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.16);
                border: 1px solid rgba(255, 255, 255, 0.24);
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }
            .portal-register-brand-copy h2 {
                margin: 1rem 0 0.85rem;
                font-size: clamp(2rem, 3vw, 2.7rem);
                font-weight: 800;
                line-height: 1.06;
            }
            .portal-register-brand-copy p {
                margin: 0;
                max-width: 30rem;
                color: rgba(255, 255, 255, 0.9);
                font-size: 1rem;
                line-height: 1.72;
            }
            .portal-register-highlights {
                display: grid;
                gap: 0.9rem;
            }
            .portal-register-highlight-card {
                display: grid;
                grid-template-columns: 44px 1fr;
                gap: 0.9rem;
                align-items: start;
                padding: 1rem 1.05rem;
                border-radius: 18px;
                background: rgba(7, 34, 56, 0.18);
                border: 1px solid rgba(255, 255, 255, 0.18);
                backdrop-filter: blur(12px);
            }
            .portal-register-highlight-card i {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                border-radius: 14px;
                background: rgba(255, 255, 255, 0.14);
                font-size: 1.15rem;
            }
            .portal-register-highlight-card strong,
            .portal-register-highlight-card span {
                display: block;
            }
            .portal-register-highlight-card strong {
                font-size: 0.95rem;
                font-weight: 700;
            }
            .portal-register-highlight-card span {
                color: rgba(255, 255, 255, 0.82);
                font-size: 0.88rem;
                line-height: 1.55;
            }
            .portal-register-highlight-card code {
                color: #fff;
                background: rgba(255, 255, 255, 0.12);
                padding: 0.08rem 0.3rem;
                border-radius: 0.35rem;
            }
            .portal-register-illustration-wrap {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 200px;
                padding-bottom: 5rem;
            }
            .portal-register-illustration-glow {
                position: absolute;
                width: 260px;
                height: 260px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.08) 58%, rgba(255,255,255,0) 72%);
            }
            .portal-register-stat-card {
                position: relative;
                z-index: 1;
                border-radius: 22px;
                border: 1px solid rgba(255,255,255,0.28);
                backdrop-filter: blur(14px);
                box-shadow: 0 18px 34px rgba(6, 51, 53, 0.22);
            }
            .portal-register-stat-card-main {
                display: grid;
                gap: 0.65rem;
                width: min(300px, 80%);
                padding: 1.45rem 1.3rem;
                background: linear-gradient(180deg, rgba(255,255,255,0.34) 0%, rgba(255,255,255,0.2) 100%);
            }
            .portal-register-stat-card-main strong {
                font-size: 1.14rem;
                font-weight: 800;
                color: #ffffff;
                text-shadow: 0 1px 10px rgba(9, 54, 57, 0.18);
            }
            .portal-register-stat-card-main span {
                display: flex;
                align-items: center;
                gap: 0.55rem;
                font-size: 0.96rem;
                color: rgba(255,255,255,0.96);
                text-shadow: 0 1px 8px rgba(9, 54, 57, 0.15);
            }
            .portal-register-stat-card-main span::before {
                content: "";
                width: 9px;
                height: 9px;
                border-radius: 50%;
                background: #d8fff2;
                box-shadow: 0 0 0 5px rgba(216,255,242,0.14);
            }
            .portal-register-stat-card-alt {
                position: absolute;
                right: 0.5rem;
                bottom: 0;
                display: grid;
                gap: 0.4rem;
                max-width: 220px;
                padding: 0.9rem 1rem;
                background: rgba(8, 40, 53, 0.62);
            }
            .portal-register-stat-badge {
                display: inline-flex;
                width: fit-content;
                padding: 0.18rem 0.55rem;
                border-radius: 999px;
                background: rgba(255,255,255,0.16);
                font-size: 0.74rem;
                font-weight: 700;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            }
            .portal-register-stat-card-alt small {
                color: rgba(255,255,255,0.86);
                font-size: 0.82rem;
                line-height: 1.5;
            }
            .portal-register-form-panel {
                display: flex;
                align-items: flex-start;
                justify-content: center;
                padding: 2rem 2.25rem;
                background: rgba(255, 255, 255, 0.96);
                max-height: calc(100vh - 3rem);
                overflow: auto;
            }
            .portal-register-form-wrap {
                width: min(100%, 610px);
            }
            .portal-register-form-wrap h1 {
                margin: 0;
                color: #203040;
                font-size: clamp(1.85rem, 2.9vw, 2.3rem);
                font-weight: 800;
                line-height: 1.08;
            }
            .portal-register-subtitle {
                margin: 0.75rem 0 1.45rem;
                color: #5c6c7c;
                font-size: 0.97rem;
                line-height: 1.68;
            }
            .portal-register-field {
                display: flex;
                flex-direction: column;
                gap: 0.45rem;
            }
            .portal-register-password-field {
                position: relative;
            }
            .portal-register-input-wrap {
                position: relative;
            }
            .portal-register-label {
                color: #314255;
                font-size: 0.9rem;
                font-weight: 700;
            }
            .portal-register-input {
                width: 100%;
                min-height: 50px;
                padding: 0.82rem 0.95rem;
                border: 1px solid #d5dde6;
                border-radius: 14px;
                background: #fff;
                color: #203040;
                font-size: 0.95rem;
                transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            }
            .portal-register-input-wrap .portal-register-input {
                padding-right: 3.4rem;
            }
            .portal-register-input:focus {
                outline: none;
                border-color: #1f7a68;
                box-shadow: 0 0 0 4px rgba(31, 122, 104, 0.12);
                transform: translateY(-1px);
            }
            .portal-password-toggle-register {
                position: absolute;
                top: 50%;
                right: 0.7rem;
                transform: translateY(-50%);
                color: #6b7b8b;
            }
            .portal-password-toggle-register:hover,
            .portal-register-input-wrap:focus-within .portal-password-toggle-register {
                color: #1f7a68;
            }
            .portal-register-input::placeholder {
                color: #9aa7b5;
            }
            .portal-register-select {
                appearance: auto;
            }
            .portal-register-textarea {
                min-height: 92px;
                resize: vertical;
            }
            .portal-register-help {
                color: #6b7b8b;
                font-size: 0.8rem;
            }
            .portal-register-actions {
                display: flex;
                align-items: center;
                gap: 0.9rem;
                margin-top: 1.5rem;
            }
            .portal-register-modal .pw-requirements {
                margin-top: 0;
                display: none;
                position: absolute;
                top: calc(100% + 8px);
                left: 0;
                z-index: 30;
                width: 340px;
                max-width: min(90vw, 340px);
                padding: 1rem 1rem 0.95rem;
                border-radius: 18px;
                border: 1px solid rgba(255, 255, 255, 0.16);
                background: rgba(14, 29, 43, 0.96);
                box-shadow: 0 20px 40px rgba(2, 12, 20, 0.28);
                backdrop-filter: blur(16px);
                opacity: 0;
                transform: translateY(8px) scale(0.98);
                transform-origin: top left;
                transition: opacity 0.26s ease, transform 0.26s ease;
            }
            .portal-register-modal .pw-requirements.show {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            .portal-register-modal .pw-popover-arrow {
                position: absolute;
                width: 12px;
                height: 12px;
                left: 18px;
                top: -6px;
                background: rgba(14, 29, 43, 0.96);
                border-left: 1px solid rgba(255, 255, 255, 0.14);
                border-top: 1px solid rgba(255, 255, 255, 0.14);
                transform: rotate(45deg);
            }
            .portal-register-modal .pw-req-header {
                margin-bottom: 0.55rem;
                color: #d9e4ef;
                font-size: 0.86rem;
                font-weight: 700;
            }
            .portal-register-modal .pw-req-list {
                display: flex;
                flex-direction: column;
                gap: 0.28rem;
            }
            .portal-register-modal .pw-req-item {
                display: flex;
                align-items: center;
                color: #aebdcb;
                font-size: 0.84rem;
            }
            .portal-register-modal .pw-req-icon {
                width: 20px;
                margin-right: 0.45rem;
                text-align: center;
                font-weight: 700;
                color: #aebdcb;
            }
            .portal-register-modal .pw-req-item.met,
            .portal-register-modal .pw-req-item.met .pw-req-icon {
                color: #aff4cb;
            }
            .portal-register-modal .pw-strength .progress {
                height: 8px;
                border-radius: 999px;
                overflow: hidden;
                background: rgba(255,255,255,0.12);
            }
            .portal-register-modal .pw-strength .progress-bar {
                transition: width 0.22s ease, background 0.22s ease;
            }
            .portal-register-modal .pw-strength-text {
                color: #d5dde6;
            }
            .portal-register-modal .pw-match-msg {
                color: #ffb3b3;
            }
            @media (max-width: 991px) {
                .portal-login-shell {
                    grid-template-columns: 1fr;
                }
                .portal-login-brand-panel {
                    padding: 2rem;
                }
                .portal-login-form-panel {
                    padding: 2rem;
                }
                .portal-register-shell {
                    grid-template-columns: 1fr;
                }
                .portal-register-brand-panel,
                .portal-register-form-panel {
                    padding: 1.75rem;
                }
                .portal-register-illustration-wrap {
                    padding-bottom: 4.25rem;
                }
                .portal-register-form-panel {
                    max-height: none;
                }
            }
            @media (max-width: 575px) {
                .portal-login-modal .modal-dialog {
                    margin: 0.85rem;
                }
                .portal-register-modal .modal-dialog {
                    margin: 0.85rem;
                }
                .portal-login-brand-panel,
                .portal-login-form-panel {
                    padding: 1.4rem;
                }
                .portal-register-brand-panel,
                .portal-register-form-panel {
                    padding: 1.25rem;
                }
                .portal-register-illustration-wrap {
                    padding-bottom: 1rem;
                }
                .portal-login-utility-row,
                .portal-login-action-row,
                .portal-login-brand-footer {
                    flex-direction: column;
                    align-items: stretch;
                }
                .portal-register-actions {
                    flex-direction: column;
                    align-items: stretch;
                }
                .portal-register-modal .pw-requirements {
                    width: min(100%, 320px);
                    max-width: calc(100vw - 2rem);
                }
                .portal-register-stat-card-alt {
                    position: static;
                    margin-top: 0.85rem;
                    max-width: none;
                }
                .portal-login-help {
                    text-align: left;
                }
            }

        .dropdown-menu .dropend .dropdown-menu {
            position: absolute;
            top: 0;
            right: 100%;
            left: auto;
            margin-top: 0;
            margin-right: 0.125rem;
        }

        .dropdown-menu .dropend:hover > .dropdown-menu {
            display: block;
        }

        .dropdown-menu .dropdown-item.dropdown-toggle::after {
            margin-left: .255em;
            border: none;
            content: "\25BC";
            font-size: .7em;
            font-weight: normal;
            vertical-align: middle;
        }
        .dropdown-menu {
            border: none;
            border-radius: 14px;
            padding: 0.5rem;
            min-width: 240px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
            animation: fadeSlide 0.18s ease;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.15s ease;
        }

        .dropdown-item:hover {
            background: #eef2ff;
            color: #2563eb;
            transform: translateX(3px);
        }

        .dropdown-item.active {
            background: #e0e7ff;
            color: #2563eb;
        }

        .dropdown-item.dropdown-toggle {
            position: relative;
        }

        .dropdown-item.dropdown-toggle::after {
            float: right;
            margin-top: 6px;
            font-size: 0.65rem;
            opacity: 0.6;
            transition: transform 0.2s ease;
        }

        .dropdown-item.dropdown-toggle.show::after {
            transform: rotate(180deg);
        }

        .dropdown-menu .dropdown-menu {
            border-radius: 12px;
            margin-left: 0.3rem;
            margin-right: 0.3rem;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.10);
        }

        .dropdown-divider {
            margin: 0.6rem 0;
            opacity: 0.1;
        }

        .dropdown-item.text-danger:hover {
            background: #fee2e2;
            color: #dc2626;
        }
        @media print {
            body {
                background: #ffffff !important;
            }
            nav.navbar,
            .stb-sidebar,
            footer,
            #bg-preloader,
            .modal-login-bg,
            .modal-login-content {
                display: none !important;
            }
            .stb-main-content {
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .stb-main-content.container {
                max-width: 100% !important;
                width: 100% !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }
        @media (max-width: 991px) {

                .dropdown-menu .dropend .dropdown-menu {
                    position: relative !important;
                    top: 0 !important;
                    left: 0 !important;
                    right: 0 !important;
                    transform: none !important;
                    margin-left: 1rem;
                    width: auto;
                }

                .dropdown-menu {
                    width: 100%;
                }

                .dropdown-item {
                    padding: 0.75rem 1rem;
                }

                .dropdown-item.dropdown-toggle::after {
                    float: right;
                    transform: rotate(0deg);
                    transition: transform 0.2s ease;
                }

                .dropdown-item.dropdown-toggle.show::after {
                    transform: rotate(180deg);
                }
            }
    </style>
</head>

<body>
    <script>
    if (window.console) {
        ['log','warn','error','debug','info','trace'].forEach(m=>{console[m]=function(){};});
    }
    document.addEventListener('DOMContentLoaded', function () {

    const isMobile = window.innerWidth <= 991;

    document.querySelectorAll(
        '#userManagementDropdown, #userUtilitiesDropdown'
    ).forEach(function (toggle) {

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            let submenu = this.nextElementSibling;

            if (submenu && submenu.classList.contains('dropdown-menu')) {

                let parentMenu = this.closest('.dropdown-menu');
                parentMenu.querySelectorAll(':scope > .dropend > .dropdown-menu')
                    .forEach(function (menu) {
                        if (menu !== submenu) {
                            menu.classList.remove('show');
                        }
                    });

                submenu.classList.toggle('show');
                this.classList.toggle('show');
            }
        });

    });

    const mainDropdown = document.getElementById('navbarDropdown');

    if (mainDropdown) {
        mainDropdown.addEventListener('hide.bs.dropdown', function () {
            document.querySelectorAll('.dropdown-menu .dropdown-menu')
                .forEach(function (submenu) {
                    submenu.classList.remove('show');
                });

            document.querySelectorAll('.dropdown-item.dropdown-toggle')
                .forEach(function (toggle) {
                    toggle.classList.remove('show');
                });
        });
    }

        });
        </script>
    @include('components.loader')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                if (window.sessionStorage && sessionStorage.getItem('bgPreloaded') === '1') {
                    if (typeof hideLoader === 'function') hideLoader();
                    return;
                }
            } catch (e) {
            }

            var bgUrl = '{{ request()->isSecure() ? secure_asset('images/dattachments/STBIP cover white.png') : asset('images/dattachments/STBIP cover white.png') }}';
            var img = new window.Image();
            img.src = bgUrl;

            if (typeof showLoader === 'function') showLoader();

            var bgReady = false;
            var cssReady = false;
            var maxTotalWaitMs = 8000;

            function markPreloaded() {
                try {
                    if (window.sessionStorage) {
                        sessionStorage.setItem('bgPreloaded', '1');
                    }
                } catch (e) {
                }
            }

            function maybeHideLoader() {
                if (bgReady && cssReady) {
                    markPreloaded();
                    if (typeof hideLoader === 'function') hideLoader();
                }
            }

            img.onload = function() {
                bgReady = true;
                maybeHideLoader();
            };

            function checkCssLoaded() {
                try {
                    var sheets = Array.from(document.styleSheets || []);
                    var appSheets = sheets.filter(function(s) {
                        return s.href && /app-.*\.css/.test(s.href);
                    });
                    if (appSheets.length === 0) {
                        cssReady = true;
                        maybeHideLoader();
                        return;
                    }
                    var allHaveRules = appSheets.every(function(s) {
                        try {
                            return s.cssRules && s.cssRules.length > 0;
                        } catch (e) {
                            return true;
                        }
                    });
                    if (allHaveRules) {
                        cssReady = true;
                        maybeHideLoader();
                        return;
                    }
                } catch (e) {
                    cssReady = true;
                    maybeHideLoader();
                    return;
                }
                setTimeout(checkCssLoaded, 150);
            }

            checkCssLoaded();

            setTimeout(function() {
                if (!(bgReady && cssReady) && typeof hideLoader === 'function') {
                    markPreloaded();
                    hideLoader();
                }
            }, maxTotalWaitMs);
        });
    </script>
    
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #06306e; position: fixed; top: 0; left: 0; width: 100%; z-index: 1030;">
        <div class="container-fluid px-3 d-flex align-items-center justify-content-between flex-nowrap">
            <a class="navbar-brand fw-bold me-3 flex-shrink-0 stb-navbar-title" href="{{ route('main') }}" style="@guest margin-left:0 !important; @endguest; white-space:nowrap;">STB Inventory Portal</a>
            <div class="d-flex align-items-center ms-auto" style="gap: 0.5rem; position: relative; z-index: 1;">
                @auth
                    <button class="navbar-toggler d-block d-lg-none order-2" type="button" aria-label="Toggle sidebar" style="z-index:1060;" onclick="document.body.classList.toggle('sidebar-open')">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                @endauth
                @auth
                    <div class="order-1">
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center"
                               href="#"
                               id="navbarDropdown"
                               role="button"
                               data-bs-toggle="dropdown"
                               data-bs-auto-close="outside"
                               aria-expanded="false">
                                <span class="fw-semibold d-flex align-items-center" style="gap:8px; color:#fff;">
                                    @if(Auth::user()->profile_picture_url)
                                        <img src="{{ Auth::user()->profile_picture_url }}" alt="{{ Auth::user()->display_name }}" class="stb-nav-avatar">
                                    @else
                                        <span class="stb-nav-avatar-fallback">{{ Auth::user()->initials }}</span>
                                    @endif
                                    {{ Auth::user()->name }}
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#profileModal">
                                        <i class="bi bi-person me-2"></i>Profile
                                    </button>
                                </li>
                                @if(Auth::user()->usergroup === 'sysadmin' || Auth::user()->usergroup === 'admin')
                                    <li class="dropend">
                                        <a class="dropdown-item dropdown-toggle admin-parent-toggle"
                                           href="#"
                                           id="adminUtilitiesDropdown"
                                           role="button">
                                            <i class="bi bi-people me-2"></i>Admin Utilities
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li class="dropend">
                                                <a class="dropdown-item dropdown-toggle nested-toggle"
                                                   href="#"
                                                   id="userUtilitiesDropdown"
                                                   role="button">
                                                    <i class="bi bi-people-fill me-2"></i>User Utilities
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('users.index') }}">
                                                            <i class="bi bi-people-fill me-2"></i>
                                                            Users Management
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('approvals.index') }}">
                                                            <i class="bi bi-person-check-fill me-2"></i>
                                                            User Approvals
                                                        </a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.stsreportsectors') }}">
                                                    <i class="bi bi-diagram-3 me-2"></i>
                                                    Sector Utilities
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('STDashboard') }}">
                                                    <i class="bi bi-upload me-2"></i>
                                                    Social Technologies Titles
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                @endif
                                {{-- Admin Dashboard removed per request --}}
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endauth
                @guest
                    <div class="order-1 d-flex align-items-center guest-auth-btns" style="gap: 1.2rem;">
                        <a href="#" class="guest-auth-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                        <a href="#" class="guest-auth-link" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="bi bi-person-plus"></i> Register
                        </a>
                    </div>
                    <style>
                        .guest-auth-btns .guest-auth-link {
                            color: #fff !important;
                            background: none !important;
                            border: none !important;
                            box-shadow: none !important;
                            font-weight: 600;
                            font-size: 1.08rem;
                            padding: 0;
                            border-radius: 0;
                            display: flex;
                            align-items: center;
                            gap: 0.4em;
                            transition: color 0.18s, text-decoration 0.18s;
                            text-decoration: none;
                        }
                        .guest-auth-btns .guest-auth-link:hover, .guest-auth-btns .guest-auth-link:focus {
                            color: #e0e7ff !important;
                            text-decoration: underline;
                        }
                        .guest-auth-btns .guest-auth-link i {
                            font-size: 1.15em;
                        }
                        </style>
                @endguest
            </div>
            <div class="collapse navbar-collapse justify-content-end d-lg-none" id="navbarNav" style="display: none !important;">
            </div>
        </div>
    </nav>
@include('Login.accounts.register')
@include('Login.login')
@auth
@include('Login.accounts.profile')
@endauth
    <style>
        #bg-preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #fff;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.4s;
        }
        #bg-preloader .spinner-border {
            width: 3.5rem;
            height: 3.5rem;
            color: #2563eb;
        }
        .stb-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 300px;
            background: linear-gradient(135deg, #f8fafc 60%, #e3eafc 100%);
            box-shadow: 2px 0 12px rgba(44,62,80,0.07);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2.2rem 1rem 1rem 1rem;
            transition: left 0.3s;
            overflow-y: auto;
        }
        @media (max-width: 900px) {
            .stb-sidebar {
                left: -320px;
                display: block;
            }
            body.sidebar-open .stb-sidebar {
                left: 0;
                box-shadow: 2px 0 12px rgba(44,62,80,0.18);
            }
            .stb-main-content {
                margin-left: 0 !important;
            }
            .navbar {
                left: 0 !important;
                width: 100% !important;
            }
            body.sidebar-open::before {
                content: '';
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.25);
                z-index: 1039;
            }
        }
        .stb-main-content {
            margin-left: 320px;
            transition: margin 0.2s;
        }
        @media (max-width: 900px) {
            .stb-main-content {
                margin-left: 0 !important;
            }
        }
    </style>
    <script>
        document.addEventListener('click', function(e) {
            if (document.body.classList.contains('sidebar-open')) {
                if (e.target === document.body) {
                    document.body.classList.remove('sidebar-open');
                }
            }
        });
        document.addEventListener('DOMContentLoaded', function(){
            try {
                var label = document.getElementById('sidebarRedirectLabel');
                var btn = document.getElementById('sidebarRedirectSetter');
                var current = (window.localStorage && localStorage.getItem('stbGlobalRedirect')) || '/';
                if (label) label.textContent = current || '/';
                if (btn) {
                    btn.addEventListener('click', function(ev){
                        ev.preventDefault();
                        var existing = (window.localStorage && localStorage.getItem('stbGlobalRedirect')) || '/';
                        var raw = prompt('Set global redirect URL (leave empty to disable).', existing || '/');
                        if (raw === null) return; // cancelled
                        var value = (raw || '').trim();
                        if (!value) {
                            try { localStorage.removeItem('stbGlobalRedirect'); } catch(e) {}
                            if (label) label.textContent = '/';
                            alert('Global redirect disabled.');
                            return;
                        }
                        var normalized = value;
                        try {
                            if (!/^\/|^https?:\/\/|^\/\//i.test(value)) {
                                normalized = 'https://' + value;
                            }
                        } catch(e) { normalized = value; }
                        try { localStorage.setItem('stbGlobalRedirect', normalized); } catch(e) {}
                        if (label) label.textContent = normalized;
                        alert('Global redirect set to: ' + normalized);
                    });
                }
            } catch(e) {}
        });
    </script>
@auth
    <div class="stb-sidebar" id="stbSidebar" style="z-index: 1040;">
        <img src="{{ asset('images/dattachments/social technology bureau innovating solution logo.png') }}" alt="STB Innovating Solution Logo" class="sidebar-logo">
        <nav class="nav flex-column w-100">
            <a class="nav-link {{ request()->routeIs('main') ? 'active' : '' }}" href="{{ route('main') }}"><i class="bi bi-house-door me-2"></i>Dashboard</a>
            @if(Auth::check() && in_array(Auth::user()->usergroup, ['user', 'admin', 'sysadmin']))
            <a class="nav-link {{ request()->routeIs('masterdata.*') ? 'active' : '' }}" href="{{ route('masterdata.index') }}"><i class="bi bi-database-gear me-2"></i>Master Data</a>
            <a class="nav-link {{ request()->routeIs('sttitles.all') ? 'active' : '' }}" href="{{ route('sttitles.all') }}"><i class="bi bi-journal-text me-2"></i>Inventory for Social Technologies</a>
            @endif
            @if(Auth::check() && Auth::user()->usergroup === 'sysadmin')
            <a id="sidebarRedirectSetter" class="nav-link" href="#" title="Set global page redirect"><i class="bi bi-link-45deg me-2"></i>Page Redirect <span id="sidebarRedirectLabel" style="font-weight:600; float:right; color:#2563eb; font-size:0.86rem;">/</span></a>
            @endif
        </nav>
        <style>
            #sidebarReportsCollapse .nav-link {
                background: none;
                color: #2c3e50;
                font-size: 1.01rem;
                border-radius: 5px;
                margin-bottom: 0.5rem;
                padding-left: 2.2rem;
                transition: background 0.15s, color 0.15s;
            }
            #sidebarReportsCollapse .nav-link:hover, #sidebarReportsCollapse .nav-link.active {
                background: #e0e7ff;
                color: #2563eb;
            }
            #sidebarUploadCollapse .nav-link {
                background: none;
                color: #2c3e50;
                font-size: 1.01rem;
                border-radius: 5px;
                margin-bottom: 0.5rem;
                padding-left: 2.2rem;
                transition: background 0.15s, color 0.15s;
            }
            #sidebarUploadCollapse .nav-link:hover, #sidebarUploadCollapse .nav-link.active {
                background: #e0e7ff;
                color: #2563eb;
            }
            .nav-link[aria-expanded="true"] #sidebarReportsChevron,
            .nav-link[aria-expanded="true"] #sidebarUploadChevron {
                transform: rotate(180deg);
                transition: transform 0.2s;
            }
            .nav-link[aria-expanded="false"] #sidebarReportsChevron,
            .nav-link[aria-expanded="false"] #sidebarUploadChevron {
                transform: rotate(0deg);
                transition: transform 0.2s;
            }
            #sidebarReportsToggle.active, #sidebarReportsToggle[aria-expanded="true"],
            #sidebarUploadToggle.active, #sidebarUploadToggle[aria-expanded="true"] {
                background: #e0e7ff;
                color: #2563eb;
            }
        </style>
    </div>
@endauth
    <div class="container py-5 stb-main-content" style="margin-top: 70px;">
        @yield('content')
    </div>
    <footer style="width:100%; background:linear-gradient(90deg,#e0e7ff 60%,#f8fafc 100%); color:#2c3e50; text-align:center;  font-size:0.92rem; margin-top:1.2rem; margin-bottom: -10.2rem; margin-left: -40px; box-shadow:0 -2px 12px rgba(44,62,80,0.05);">
        <div style="max-width:700px; margin:0 auto; display:flex; flex-direction:column; align-items:center; gap:0.15rem; line-height:1.3;">
            <div>Department of Social Welfare and Development, Social Technology Bureau</div>
            <div>3RD Floor, Matapat Building Department Of Social Welfare And Development - Central Office IBP Road, Constitution Hills, Batasan Complex, Quezon City</div>
            <div>Tel: 02-8951-7124 | 02-8951-2802 | 02-8931-8144</div>
            <div>© {{ date('Y') }} DSWD STB Inventory Portal. All rights reserved.</div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    @yield('scripts')
</body>


</body>
</html>