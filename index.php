<?php
session_start();
require_once 'csrf_protection.php';

// If coordinator already logged in, send straight to coordinator dashboard
if (!empty($_SESSION['coordinator_logged_in']) && $_SESSION['coordinator_logged_in'] === true) {
    header('Location: coodinator/coordinator.php');
    exit;
}

// If admin already logged in, send to admin dashboard
if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/admin.php');
    exit;
}
?>
<script
  src="https://www.tuqlas.com/chatbot.js"
  data-key="tq_live_eced92aa93cf8ce4a7d961cf4c7bcc16768c5d47"
  data-api="https://www.tuqlas.com"
  defer
></script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>OJT Monitoring System | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body {
            background-image: url('assets/img/users/wallpaper_19.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 1rem;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(7, 30, 65, 0.45);
            z-index: -1;
        }

        .container { max-width: 100%; padding: 0; }

        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.14);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.29);
            backdrop-filter: blur(14px);
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
        }

        /* Desktop role boxes */
        .role-box {
            border-radius: 18px;
            padding: 38px 24px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            text-align: center;
            backdrop-filter: blur(4px);
            position: relative;
            overflow: hidden;
            border-width: 2px;
            border-style: solid;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .role-box::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0.85;
            pointer-events: none;
        }

        .role-box > * { position: relative; z-index: 1; }

        .role-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            width: 100px;
            height: 100px;
            line-height: 100px;
            border-radius: 50%;
            border-width: 2px;
            border-style: solid;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .role-box h4 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .role-box p {
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .role-box.coordinator {
            background: linear-gradient(145deg, rgba(255,255,255,0.92), rgba(13, 110, 253, 0.12));
            border-color: #0d6efd;
        }
        .role-box.coordinator .role-icon {
            color: #0d6efd;
            border-color: #0d6efd;
            background: rgba(13, 110, 253, 0.15);
        }
        .role-box.coordinator:hover {
            background: rgba(13, 110, 253, 0.15);
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.25);
        }
        .role-box.coordinator:hover .role-icon {
            background: #0d6efd;
            color: #fff;
        }

        .role-box.student {
            background: linear-gradient(145deg, rgba(255,255,255,0.92), rgba(23, 162, 184, 0.12));
            border-color: #17a2b8;
        }
        .role-box.student .role-icon {
            color: #17a2b8;
            border-color: #17a2b8;
            background: rgba(23, 162, 184, 0.15);
        }
        .role-box.student:hover {
            background: rgba(23, 162, 184, 0.15);
            box-shadow: 0 10px 20px rgba(23, 162, 184, 0.25);
        }
        .role-box.student:hover .role-icon {
            background: #17a2b8;
            color: #fff;
        }

        .hidden-form { display: none; }

        .form-control {
            border: 1px solid rgba(100, 116, 139, 0.35);
            border-radius: 10px;
            background: rgba(255,255,255,0.95);
            box-shadow: inset 0 1px 3px rgba(15, 23, 42, 0.05);
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.2s ease;
            padding: 12px 14px;
            font-size: 1rem;
            width: 100%;
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(13, 110, 253, 0.8);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12);
            transform: translateY(-1px);
        }

        .form-label { font-weight: 600; color: #0f172a; margin-bottom: 0.35rem; }

        .btn-primary {
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 8px 18px rgba(13, 110, 253, 0.24);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(13, 110, 253, 0.31);
        }

        .header-section {
            padding: 20px 18px;
            margin-bottom: 25px;
            border-radius: 14px;
            background: linear-gradient(145deg, rgba(1, 30, 85, 0.9), rgba(13, 110, 253, 0.8));
            color: #fff;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.15), 0 6px 15px rgba(8,33,88,0.25);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .header-section h2 {
            margin-bottom: 0.2rem;
            color: #f8fafc;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .header-section p { margin-bottom: 0; color: rgba(236,240,255,0.9); }

        .fade-in { animation: fadeInContent 0.35s ease-in-out forwards; }
        .role-box.rotate-up { transform: translateY(-5px) scale(1.01); }

        .form-panel {
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 0.35s ease, transform 0.35s ease;
        }
        .form-panel.visible { opacity: 1; transform: translateY(0); }

        @keyframes fadeInContent {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .blend-card {
            background: linear-gradient(135deg, rgba(255,255,255,.72), rgba(232,242,255,.52));
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 14px 30px rgba(18,37,82,0.18);
        }

        /* Password Toggle Button */
        .password-toggle-wrapper {
            position: relative;
        }
        .password-toggle-wrapper .form-control {
            padding-right: 48px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #6c757d;
            padding: 8px 4px;
            cursor: pointer;
            font-size: 1.1rem;
            z-index: 5;
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            touch-action: manipulation;
        }
        .toggle-password:hover {
            color: #0d6efd;
        }
        .toggle-password:focus {
            outline: none;
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
        input[type="password"]::-webkit-credentials-auto-fill-button,
        input[type="password"]::-webkit-textfield-decoration-container {
            display: none !important;
        }
        input[type="password"]::-moz-reveal {
            display: none;
        }

        /* LOGIN ACTIONS */
        .login-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.5rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .forgot-password-link {
            flex: 1;
        }
        
        .forgot-password-link a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(13, 110, 253, 0.08);
            border: 2px solid rgba(13, 110, 253, 0.15);
            transition: all 0.25s ease;
        }
        
        .forgot-password-link a:hover {
            background: rgba(13, 110, 253, 0.15);
            border-color: #0d6efd;
            color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
            text-decoration: none;
        }
        
        .forgot-password-link a:active {
            transform: scale(0.96);
        }
        
        .forgot-password-link a i {
            font-size: 0.9rem;
            color: #0d6efd;
        }
        
        .login-actions .btn-primary {
            flex: 0 0 auto;
            padding: 10px 30px;
            min-width: 120px;
        }

        /* CREATE ACCOUNT BUTTON - White */
        .btn-create-account {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 10px 16px;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            background: #ffffff;
            color: #1e293b;
            min-height: 44px;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        
        .btn-create-account:hover {
            background-color: #f0f4ff;
            border-color: rgba(13, 110, 253, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-create-account:active {
            transform: scale(0.97);
        }
        
        .btn-create-account i {
            font-size: 0.9rem;
            margin-right: 6px;
        }

        .login-divider {
            display: flex;
            align-items: center;
            margin: 1.2rem 0 1rem;
        }
        .login-divider::before,
        .login-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .login-divider span {
            padding: 0 1rem;
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-visible {
            border: 1px solid rgba(0,0,0,0.15);
            background-color: #ffffff;
            color: #1e293b;
            font-weight: 500;
            transition: background 0.15s;
        }
        .btn-visible:hover {
            background-color: #f0f4ff;
            border-color: rgba(13, 110, 253, 0.3);
        }

        /* Forgot Password Modal */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .modal-header {
            background: linear-gradient(145deg, rgba(1, 30, 85, 0.95), rgba(13, 110, 253, 0.85));
            color: #fff;
            border-bottom: none;
            padding: 1.5rem 1.5rem 1rem 1.5rem;
        }
        
        .modal-header .modal-title {
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-header .modal-title i {
            font-size: 1.4rem;
        }
        
        .modal-header .btn-close {
            color: rgba(255,255,255,0.9);
            filter: brightness(0) invert(1);
            opacity: 0.8;
            transition: opacity 0.2s ease;
            padding: 0.5rem;
            margin: -0.5rem -0.5rem -0.5rem auto;
        }
        
        .modal-header .btn-close:hover {
            opacity: 1;
        }
        
        .modal-body {
            padding: 1.75rem 1.5rem 1.5rem 1.5rem;
            background: #f8fafc;
        }
        
        .modal-body .forgot-password-icon {
            text-align: center;
            margin-bottom: 1.25rem;
        }
        
        .modal-body .forgot-password-icon i {
            font-size: 3.5rem;
            color: #0d6efd;
            background: rgba(13, 110, 253, 0.1);
            padding: 1.25rem;
            border-radius: 50%;
            display: inline-block;
        }
        
        .modal-body .forgot-password-description {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-body .forgot-password-description p {
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 0;
        }
        
        .modal-body .form-group {
            margin-bottom: 1.25rem;
        }
        
        .modal-body .form-group label {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
        }
        
        .modal-body .form-group .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 2px solid #e2e8f0;
            background: #fff;
            transition: all 0.2s ease;
        }
        
        .modal-body .form-group .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            transform: none;
        }
        
        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1rem 1.5rem 1.5rem 1.5rem;
            background: #f8fafc;
            border-radius: 0 0 16px 16px;
            gap: 0.75rem;
        }
        
        .modal-footer .btn {
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            border-radius: 10px;
            min-height: 48px;
            font-size: 0.95rem;
        }
        
        .modal-footer .btn-primary {
            background: linear-gradient(145deg, #0d6efd, #0b5ed7);
            border: none;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            flex: 1;
        }
        
        .modal-footer .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
        }
        
        .modal-footer .btn-primary i {
            margin-right: 8px;
        }
        
        .modal-footer .btn-outline-secondary {
            border: 2px solid #e2e8f0;
            color: #475569;
            background: #fff;
        }
        
        .modal-footer .btn-outline-secondary:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        /* HIDE CREATE ACCOUNT BUTTON INSIDE REGISTRATION FORM */
        #student-register-form .btn-create-account {
            display: none !important;
        }

        /* Alert styling improvements */
        .alert {
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 16px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .alert-danger {
            background-color: #fef2f2;
            border-left: 4px solid #dc3545;
            color: #991b1b;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #166534;
        }
        
        .alert-warning {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        
        .alert-info {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }

        /* Tablets (≤ 991.98px) */
        @media (max-width: 991.98px) {
            .login-card { max-width: 500px; }
            .role-box { padding: 30px 18px; }
            .role-icon { font-size: 3.2rem; width: 85px; height: 85px; line-height: 85px; }
            .role-box h4 { font-size: 1.2rem; }
            .role-box p { font-size: 0.85rem; }
        }

        /* ============================================================ */
        /* MOBILE (≤ 575.98px) */
        /* ============================================================ */
        @media (max-width: 575.98px) {
            body {
                padding: 0.25rem;
                background-attachment: scroll;
            }

            .login-card {
                max-width: 100%;
                padding: 0.75rem !important;
                border-radius: 10px;
                margin: 0;
                background: rgba(255, 255, 255, 0.35);
                backdrop-filter: blur(6px);
            }

            .header-section {
                padding: 0.6rem 0.8rem;
                margin-bottom: 0.8rem;
                border-radius: 8px;
            }
            .header-section h2 { font-size: 1.2rem; margin-bottom: 0.2rem; }
            .header-section p { font-size: 0.75rem; }

            .row.g-4 {
                gap: 0.75rem !important;
                margin: 0 !important;
            }
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
                padding: 0;
            }
            .role-box {
                padding: 1.2rem 1rem !important;
                border-radius: 14px;
                min-height: 110px;
                border-width: 2px;
                flex-direction: column;
                text-align: center;
                justify-content: center;
            }
            .role-icon {
                font-size: 2.4rem;
                width: 64px;
                height: 64px;
                line-height: 64px;
                margin-bottom: 0.5rem;
            }
            .role-box h4 {
                font-size: 1.1rem;
                margin-bottom: 0.2rem;
            }
            .role-box p {
                font-size: 0.8rem;
                margin-bottom: 0;
            }
            .role-box .role-text {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
            }

            .toggle-password {
                min-width: 48px;
                min-height: 48px;
                right: 8px;
                font-size: 1.2rem;
            }
            .password-toggle-wrapper .form-control {
                padding-right: 52px;
            }

            .form-label { font-size: 0.8rem; margin-bottom: 0.15rem; }
            .form-control, .form-select {
                padding: 0.4rem 0.5rem;
                font-size: 0.95rem !important;
                border-radius: 6px;
                height: 44px;
                min-height: 44px;
            }
            .mb-3 { margin-bottom: 0.6rem !important; }
            .mb-4 { margin-bottom: 0.7rem !important; }
            .mb-2 { margin-bottom: 0.4rem !important; }

            .btn {
                padding: 0.4rem 0.8rem !important;
                font-size: 0.85rem !important;
                border-radius: 6px;
                min-height: 44px;
            }
            .btn-primary { box-shadow: 0 4px 10px rgba(13,110,253,0.2); }

            h3 { font-size: 1.1rem; margin-bottom: 0.4rem; }
            h5 { font-size: 0.95rem; margin-bottom: 0.4rem; }
            .fa-3x { font-size: 1.8rem !important; }
            .alert { 
                padding: 0.5rem; 
                font-size: 0.75rem; 
                margin-bottom: 0.6rem; 
                border-radius: 6px; 
            }
            .text-muted { font-size: 0.75rem; }
            #student-register-form { 
                margin-top: 0.6rem; 
                padding-top: 0.6rem;
            }
            #student-register-form .btn-create-account {
                display: none !important;
            }
            small { font-size: 0.65rem; }

            .container { padding: 0 !important; }
            .login-card * { max-width: 100%; }
            .form-control, .form-select { max-width: 100%; }

            .btn-visible {
                font-size: 0.85rem !important;
                padding: 0.4rem 0.9rem !important;
            }

            /* LOGIN ACTIONS */
            .login-actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 0.75rem;
            }
            
            .forgot-password-link {
                flex: 1;
            }
            
            .forgot-password-link a {
                font-size: 0.85rem;
                padding: 6px 12px;
                background: rgba(13, 110, 253, 0.08);
                border: 2px solid rgba(13, 110, 253, 0.15);
                border-radius: 8px;
                color: #0d6efd;
                font-weight: 600;
                white-space: nowrap;
            }
            
            .forgot-password-link a:hover {
                background: rgba(13, 110, 253, 0.15);
                border-color: #0d6efd;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
            }
            
            .login-actions .btn-primary {
                flex: 0 0 auto;
                padding: 10px 30px !important;
                font-size: 0.85rem !important;
                min-height: 44px;
                min-width: 120px;
                border-radius: 8px;
            }

            /* DIVIDER */
            .login-divider {
                margin: 1.2rem 0 1rem;
            }
            
            .login-divider span {
                font-size: 0.8rem;
                padding: 0 1rem;
            }

            /* CREATE ACCOUNT - White */
            .btn-create-account {
                padding: 10px 16px;
                font-size: 0.85rem;
                border: 1px solid rgba(0, 0, 0, 0.15);
                border-radius: 8px;
                background: #ffffff;
                color: #1e293b;
                min-height: 44px;
                font-weight: 500;
            }
            
            .btn-create-account:hover {
                background-color: #f0f4ff;
                border-color: rgba(13, 110, 253, 0.3);
                transform: translateY(-2px);
            }
            
            #showRegisterBtn {
                display: flex !important;
            }
            
            /* Back to selection */
            .mt-3.text-center .btn-visible {
                font-size: 0.85rem !important;
                padding: 0.4rem 0.9rem !important;
                min-height: 36px;
                border-radius: 6px;
            }

            /* REGISTRATION FORM */
            #student-register-form h5 {
                font-size: 1.1rem;
                font-weight: 700;
                margin-bottom: 1rem;
                text-align: center;
            }
            
            #student-register-form .form-label {
                font-size: 0.85rem;
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 0.2rem;
            }
            
            #student-register-form .form-control {
                font-size: 0.95rem !important;
                padding: 0.5rem 0.7rem;
                border-radius: 8px;
                border: 1.5px solid rgba(100, 116, 139, 0.25);
                height: 44px;
                min-height: 44px;
            }
            
            #student-register-form .form-control::placeholder {
                color: #94a3b8;
                font-size: 0.85rem;
            }
            
            #student-register-form small {
                font-size: 0.65rem;
                color: #6c757d;
            }
            
            #student-register-form .btn-success {
                padding: 10px;
                font-weight: 600;
                border-radius: 8px;
                min-height: 44px;
            }
            
            #student-register-form .text-center a {
                color: #0d6efd;
                text-decoration: none;
                font-weight: 600;
                font-size: 0.9rem;
            }
            
            #student-register-form .text-center a:hover {
                text-decoration: underline;
            }

            /* MOBILE FORGOT PASSWORD MODAL */
            .modal-dialog {
                margin: 0.5rem;
                max-width: 95%;
            }
            
            .modal-content {
                border-radius: 14px;
                overflow: hidden;
            }
            
            .modal-header {
                padding: 1.25rem 1rem 0.75rem 1rem;
                flex-wrap: wrap;
            }
            
            .modal-header .modal-title {
                font-size: 1.1rem;
                font-weight: 700;
            }
            
            .modal-header .modal-title i {
                font-size: 1.2rem;
            }
            
            .modal-header .btn-close {
                padding: 0.35rem;
                margin: -0.35rem -0.35rem -0.35rem auto;
                font-size: 0.75rem;
            }
            
            .modal-body {
                padding: 1.25rem 1rem 1rem 1rem;
                background: #f8fafc;
            }
            
            .modal-body .forgot-password-icon {
                margin-bottom: 1rem;
            }
            
            .modal-body .forgot-password-icon i {
                font-size: 2.8rem;
                padding: 1rem;
                background: rgba(13, 110, 253, 0.08);
            }
            
            .modal-body .forgot-password-description {
                margin-bottom: 1.25rem;
            }
            
            .modal-body .forgot-password-description p {
                font-size: 0.85rem;
                line-height: 1.5;
                color: #475569;
            }
            
            .modal-body .form-group {
                margin-bottom: 1rem;
            }
            
            .modal-body .form-group label {
                font-size: 0.85rem;
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 0.35rem;
            }
            
            .modal-body .form-group .form-control {
                padding: 0.65rem 0.9rem;
                font-size: 0.95rem;
                border-radius: 8px;
                border: 2px solid #e2e8f0;
                height: 48px;
                min-height: 48px;
                background: #ffffff;
                padding-left: 1rem;
            }
            
            .modal-body .form-group .form-control:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
            }
            
            .modal-footer {
                padding: 0.75rem 1rem 1.25rem 1rem;
                flex-direction: column-reverse;
                gap: 0.6rem;
                background: #f8fafc;
                border-top: 1px solid #e2e8f0;
            }
            
            .modal-footer .btn {
                width: 100%;
                padding: 0.65rem 1rem !important;
                font-size: 0.9rem !important;
                min-height: 48px;
                border-radius: 8px;
                font-weight: 600;
            }
            
            .modal-footer .btn-primary {
                order: 1;
                background: linear-gradient(145deg, #0d6efd, #0b5ed7);
                box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
            }
            
            .modal-footer .btn-primary:active {
                transform: scale(0.97);
            }
            
            .modal-footer .btn-outline-secondary {
                order: 2;
                border: 2px solid #e2e8f0;
                background: #ffffff;
                color: #475569;
            }
            
            .modal-footer .btn-outline-secondary:active {
                background: #f1f5f9;
            }
            
            .modal-footer .btn-primary::after {
                content: '';
                display: block;
                border-top: 1px solid #e2e8f0;
                margin: 0 -1rem 0.6rem -1rem;
                order: -1;
            }
        }

        /* Extra small (≤ 375px) */
        @media (max-width: 375px) {
            .login-card { padding: 0.5rem !important; }
            .role-icon { font-size: 2rem; width: 54px; height: 54px; line-height: 54px; }
            .role-box h4 { font-size: 0.95rem; }
            .role-box p { font-size: 0.7rem; }
            .header-section h2 { font-size: 1rem; }
            .btn { font-size: 0.8rem !important; }
            
            .forgot-password-link a {
                font-size: 0.75rem !important;
                padding: 4px 8px !important;
            }
            
            .login-actions .btn-primary {
                font-size: 0.8rem !important;
                padding: 8px 20px !important;
                min-height: 40px;
                min-width: 100px;
            }
            
            .btn-create-account {
                font-size: 0.8rem !important;
                padding: 8px 12px !important;
                min-height: 40px;
            }
            
            #student-register-form h5 {
                font-size: 1rem;
            }
            
            #student-register-form .form-control {
                font-size: 0.9rem !important;
                height: 40px;
                min-height: 40px;
            }
            
            .modal-header .modal-title {
                font-size: 1rem;
            }
            
            .modal-body .forgot-password-icon i {
                font-size: 2.2rem;
                padding: 0.8rem;
            }
            
            .modal-body .forgot-password-description p {
                font-size: 0.8rem;
            }
            
            .modal-body .form-group .form-control {
                font-size: 0.9rem;
                height: 44px;
                min-height: 44px;
                padding: 0.5rem 0.8rem;
            }
            
            .modal-footer .btn {
                font-size: 0.85rem !important;
                min-height: 44px;
                padding: 0.5rem 0.8rem !important;
            }
        }

        /* Landscape */
        @media (max-height: 500px) and (orientation: landscape) {
            .login-card { max-height: 92vh; overflow-y: auto; }
            .header-section { padding: 0.4rem 0.6rem; margin-bottom: 0.4rem; }
            .role-box { padding: 0.6rem 0.4rem !important; min-height: 70px; }
            .role-icon { width: 40px; height: 40px; line-height: 40px; font-size: 1.6rem; }
            .role-box h4 { font-size: 0.85rem; }
            .role-box p { font-size: 0.65rem; }
            
            .modal-dialog {
                max-height: 90vh;
                margin: 0.25rem;
            }
            
            .modal-body {
                max-height: 50vh;
                overflow-y: auto;
                padding: 1rem;
            }
            
            .modal-header {
                padding: 0.75rem 1rem;
            }
            
            .modal-footer {
                padding: 0.5rem 1rem;
                flex-direction: row;
            }
            
            .modal-footer .btn {
                min-height: 36px;
                padding: 0.3rem 0.8rem !important;
                font-size: 0.8rem !important;
            }
        }

        @media print {
            body::before { display: none; }
            .login-card { box-shadow: none; border: 1px solid #ddd; }
        }
        
        /* Registration form validation styles */
        .is-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
        
        .was-validated .form-control:invalid ~ .invalid-feedback,
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-card blend-card p-4 p-md-5">
        
        <div id="selection-step">
            <div class="header-section text-center">
                <h2 class="fw-bold">OJT Monitoring System</h2>
                <p class="text-muted">Select your portal to continue</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="role-box coordinator" data-role="coordinator">
                        <i class="fas fa-chalkboard-teacher role-icon" aria-hidden="true"></i>
                        <div class="role-text">
                            <h4>Coordinator</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="role-box student" data-role="student">
                        <i class="fas fa-user-graduate role-icon" aria-hidden="true"></i>
                        <div class="role-text">
                            <h4>Student</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COORDINATOR LOGIN FORM -->
        <div id="coordinator-form" class="hidden-form">
            <div class="text-center mb-4 mt-3">
                <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                <h3 class="fw-bold">Coordinator Login</h3>
            </div>
            <?php if (isset($_GET['coordinator_login_failed']) && $_GET['coordinator_login_failed'] == 1): ?>
                <div class="alert alert-danger">Invalid login credentials. Please check code and password.</div>
            <?php endif; ?>
            <form action="auth_coordinator.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <div class="mb-3">
                    <label class="form-label">Coordinator Access Code</label>
                    <input type="text" class="form-control" name="access_code" required placeholder="Enter access code e.g. COORD-XXXX-XXXX" autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="password-toggle-wrapper">
                        <input type="password" class="form-control password-toggle" name="password" required placeholder="••••••••">
                        <button type="button" class="toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Access Dashboard</button>
            </form>
            <div class="mt-3 text-center">
                <a href="#" onclick="goBack()" class="btn btn-visible btn-sm shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to selection
                </a>
            </div>
        </div>

        <!-- STUDENT LOGIN FORM -->
        <div id="student-form" class="hidden-form">
            <!-- STUDENT LOGIN HEADER -->
            <div id="studentLoginHeader" class="text-center mb-4 mt-3">
                <i class="fas fa-user-graduate fa-3x text-info mb-3"></i>
                <h3 class="fw-bold">Student Login</h3>
            </div>
            
            <!-- Alert Messages -->
            <?php if (isset($_GET['student_reg_success']) && $_GET['student_reg_success'] == 1): ?>
                <div class="alert alert-info">
                    <i class="fas fa-hourglass-half me-2"></i>
                    <strong>Registration Pending!</strong> Your account has been created and is awaiting approval from your coordinator.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['student_reg_failed']) && $_GET['student_reg_failed'] == 1): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Registration Failed!</strong> Please complete all fields and ensure passwords match with at least 8 characters.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['student_reg_failed']) && $_GET['student_reg_failed'] == 2): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Student ID Already Exists!</strong> Please use a different student number.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['student_reg_failed']) && $_GET['student_reg_failed'] == 3): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Invalid Format!</strong> Student ID must be formatted as TC-YY-A-00000 (year, section A-Z, 5 digits).
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['student_reg_failed']) && $_GET['student_reg_failed'] == 4): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Email Already Registered!</strong> Please use another email or login.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['student_login_failed']) && $_GET['student_login_failed'] == 1): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Invalid Credentials!</strong> Please check your student number or password.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['student_login_failed']) && $_GET['student_login_failed'] == 5): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-hourglass-half me-2"></i>
                    <strong>Account Pending Approval</strong>
                    <p class="mb-0">Your account is awaiting approval from your coordinator. Please check back later.</p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['student_login_failed']) && $_GET['student_login_failed'] == 6): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-ban me-2"></i>
                    <strong>Registration Rejected</strong>
                    <p class="mb-0">Your registration has been rejected. Please contact your department coordinator.</p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['student_login_failed']) && $_GET['student_login_failed'] == 7): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-envelope me-2"></i>
                    <strong>Email Not Verified!</strong>
                    <p class="mb-1">Please check your email for the verification link.</p>
                    <a href="/ojt1/students/resend_verification.php?student_id=<?php echo htmlspecialchars($_GET['student_id'] ?? ''); ?>" class="btn btn-sm btn-outline-primary mt-1">
                        <i class="fas fa-redo me-1"></i> Resend verification email
                    </a>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['forgot_password_success']) && $_GET['forgot_password_success'] == 1): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Password Reset Email Sent!</strong>
                    <p class="mb-0">Please check your email for instructions to reset your password.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['forgot_password_failed']) && $_GET['forgot_password_failed'] == 1): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Student Not Found!</strong>
                    <p class="mb-0">No student account found with that email address.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['student_login_failed']) && $_GET['student_login_failed'] == 8): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Login Failed!</strong>
                    <p class="mb-0">Invalid CSRF token. Please try again.</p>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form id="studentLoginForm" action="auth_student.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                
                <div class="mb-3">
                    <label class="form-label">Student Number</label>
                    <input type="text" class="form-control" name="student_id" required placeholder="e.g. TC-YY-S-00000">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="password-toggle-wrapper">
                        <input type="password" class="form-control password-toggle" name="password" required placeholder="••••••••">
                        <button type="button" class="toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="login-actions">
                    <div class="forgot-password-link">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                            <i class="fas fa-key"></i> Forgot Password?
                        </a>
                    </div>
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </div>
            </form>

            <!-- DIVIDER -->
            <div id="loginDivider" class="login-divider">
                <span>or</span>
            </div>

            <!-- Create Account Button -->
            <div class="text-center" id="createAccountContainer">
                <button id="showRegisterBtn" class="btn-create-account w-100">
                    <i class="fas fa-user-plus me-2"></i> Create Account
                </button>
            </div>

            <!-- Student Registration Form -->
            <div id="student-register-form" class="d-none mt-3">
                <!-- STUDENT REGISTRATION HEADER -->
                <h5 class="text-center fw-bold mb-3">Student Registration</h5>
                
                <!-- Registration Alert Messages -->
                <div id="registrationAlert" class="alert d-none" role="alert"></div>
                
                <form id="studentRegistrationForm" action="students/register.php" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    <div class="mb-2">
                        <label class="form-label">Student Number (TC format)</label>
                        <input type="text" name="student_id" class="form-control" required placeholder="TC-23-A-00001" pattern="TC-[0-9]{2}-[A-Z]-[0-9]{5}">
                        <div class="invalid-feedback">Please enter a valid Student ID (format: TC-YY-A-00000).</div>
                        <small class="text-muted">Format: TC-YY-S-00000 (Section A-Z, 5 digits)</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Your full name">
                        <div class="invalid-feedback">Please enter your full name.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="you@example.com">
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Department / Course</label>
                        <select name="department" class="form-control" required>
                            <option value="" selected disabled hidden>Select Department</option>
                            <optgroup label="Education">
                                <option value="Bachelor of Elementary Education (BEEd)">Bachelor of Elementary Education (BEEd)</option>
                                <option value="Bachelor of Physical Education (BPEd)">Bachelor of Physical Education (BPEd)</option>
                                <option value="Bachelor of Secondary Education (BSEd) - Major in English">BSEd - Major in English</option>
                                <option value="Bachelor of Secondary Education (BSEd) - Major in Filipino">BSEd - Major in Filipino</option>
                                <option value="Bachelor of Secondary Education (BSEd) - Major in Mathematics">BSEd - Major in Mathematics</option>
                                <option value="Bachelor of Secondary Education (BSEd) - Major in Social Studies">BSEd - Major in Social Studies</option>
                            </optgroup>
                            <optgroup label="College of Arts">
                                <option value="Bachelor of Arts in English Language Studies (BAELS)">BA in English Language Studies (BAELS)</option>
                            </optgroup>
                            <optgroup label="College of Agriculture & Forestry">
                                <option value="Bachelor of Science in Agriculture (BSA) - Major in Animal Science">BSA - Animal Science</option>
                                <option value="Bachelor of Science in Agriculture (BSA) - Major in Crop Science">BSA - Crop Science</option>
                                <option value="Bachelor of Science in Agriculture (BSA) - Major in Plant Pathology">BSA - Plant Pathology</option>
                                <option value="Bachelor of Science in Agriculture (BSA) - Major in Soil Science">BSA - Soil Science</option>
                                <option value="Bachelor of Science in Forestry (BSF)">Bachelor of Science in Forestry (BSF)</option>
                            </optgroup>
                            <optgroup label="College of Business & Management">
                                <option value="Bachelor of Science in Agribusiness (BSAB)">Bachelor of Science in Agribusiness (BSAB)</option>
                                <option value="Bachelor of Science in Business Administration (BSBA) - Major in Financial Management">BSBA - Major in Financial Management</option>
                                <option value="Bachelor of Science in Hospitality Management (BSHM)">Bachelor of Science in Hospitality Management (BSHM)</option>
                            </optgroup>
                            <optgroup label="College of Computing Studies">
                                <option value="Bachelor of Science in Computer Science (BSCS)">Bachelor of Science in Computer Science (BSCS)</option>
                                <option value="Bachelor of Science in Information Systems (BSIS)">Bachelor of Science in Information Systems (BSIS)</option>
                            </optgroup>
                            <optgroup label="College of Criminology">
                                <option value="Bachelor of Science in Criminology (BSCrim)">Bachelor of Science in Criminology (BSCrim)</option>
                            </optgroup>
                        </select>
                        <div class="invalid-feedback">Please select your department.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <div class="password-toggle-wrapper">
                            <input type="password" class="form-control password-toggle" name="password" required placeholder="••••••••" minlength="8">
                            <button type="button" class="toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <div class="password-toggle-wrapper">
                            <input type="password" class="form-control password-toggle" name="confirm_password" required placeholder="••••••••" minlength="8">
                            <button type="button" class="toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Register</button>
                </form>
                <div class="text-center mt-2">
                    <a href="#" id="hideRegisterBtn">Back to login</a>
                </div>
            </div>

            <!-- Back to Selection -->
            <div class="mt-3 text-center">
                <a href="#" onclick="goBack()" class="btn-visible btn-sm shadow-sm" style="display: inline-block; padding: 0.4rem 0.9rem; border-radius: 6px; text-decoration: none; border: 1px solid rgba(0,0,0,0.15); background-color: #ffffff; color: #1e293b; font-weight: 500; font-size: 0.85rem;">
                    <i class="fas fa-arrow-left me-1"></i> Back to selection
                </a>
            </div>
        </div>

        <!-- ADMIN LOGIN FORM (hidden, accessible via 'q' key) -->
        <div id="admin-form" class="hidden-form">
            <div class="text-center mb-4 mt-3">
                <i class="fas fa-user-shield fa-3x text-danger mb-3"></i>
                <h3 class="fw-bold">Admin Login</h3>
            </div>
            <?php if (isset($_GET['admin_login_failed']) && $_GET['admin_login_failed'] == 1): ?>
                <div class="alert alert-danger">Invalid admin credentials.</div>
            <?php endif; ?>
            <form action="auth_admin.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <div class="mb-3">
                    <label class="form-label">Admin Username</label>
                    <input type="text" class="form-control" name="username" required placeholder="admin">
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="password-toggle-wrapper">
                        <input type="password" class="form-control password-toggle" name="password" required placeholder="••••••••">
                        <button type="button" class="toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger w-100">Sign In</button>
            </form>
            <div class="mt-3 text-center">
                <a href="#" onclick="goBack()" class="btn btn-visible btn-sm shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to selection
                </a>
            </div>
        </div>

    </div>
</div>

<!-- FORGOT PASSWORD MODAL -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">
                    <i class="fas fa-key"></i> Forgot Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="forgot-password-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="forgot-password-description">
                    <p>Enter your registered email address and we'll send you a link to reset your password.</p>
                </div>
                <form id="forgotPasswordForm" action="students/forgot_password.php" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    <div class="form-group">
                        <label for="forgotPasswordEmail">Email Address</label>
                        <input type="email" class="form-control" id="forgotPasswordEmail" name="email" required placeholder="Enter your registered email address">
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Reset Link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- BOOTSTRAP JAVASCRIPT BUNDLE -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ----- Role selection -----
    function showForm(role) {
        var selectionStep = document.getElementById('selection-step');
        var coordinatorForm = document.getElementById('coordinator-form');
        var studentForm = document.getElementById('student-form');
        var adminForm = document.getElementById('admin-form');

        [coordinatorForm, studentForm, adminForm].forEach(function(form){
            form.classList.remove('visible');
            form.style.display = 'none';
        });

        selectionStep.style.display = 'none';

        var target = role === 'coordinator' ? coordinatorForm : role === 'student' ? studentForm : adminForm;
        if (target) {
            target.style.display = 'block';
            setTimeout(function() {
                target.classList.add('form-panel', 'visible');
            }, 20);
        }

        document.querySelectorAll('.role-box').forEach(function(box){
            box.classList.remove('rotate-up');
        });
    }

    function goBack() {
        var selectionStep = document.getElementById('selection-step');
        var coordinatorForm = document.getElementById('coordinator-form');
        var studentForm = document.getElementById('student-form');
        var adminForm = document.getElementById('admin-form');

        [coordinatorForm, studentForm, adminForm].forEach(function(form){
            form.classList.remove('visible');
            form.style.display = 'none';
        });

        selectionStep.style.display = 'block';
    }

    document.addEventListener('keydown', function (event) {
        if (event.key.toLowerCase() === 'q') {
            showForm('admin');
        }
    });

    document.querySelectorAll('.role-box').forEach(function(box){
        box.addEventListener('click', function(){
            box.classList.add('rotate-up');
            setTimeout(function(){
                var target = box.dataset.role || 'student';
                showForm(target);
                document.querySelectorAll('.role-box').forEach(function(item){
                    item.classList.remove('rotate-up');
                });
            }, 180);
        });
    });

    // ================================================================
    // FIX: STAY ON STUDENT LOGIN FORM WHEN REFRESHING
    // ================================================================
    function initDefaultFormFromQuery() {
        var params = new URLSearchParams(window.location.search);
        
        if (params.get('student_login_failed') || params.get('student_reg_failed') || 
            params.get('student_reg_success') || params.get('forgot_password_success') || 
            params.get('forgot_password_failed')) {
            showForm('student');
            return;
        }
        
        if (params.get('coordinator_login_failed') === '1') {
            showForm('coordinator');
            return;
        }
        
        if (params.get('admin_login_failed') === '1') {
            showForm('admin');
            return;
        }
        
        var lastForm = sessionStorage.getItem('lastActiveForm');
        
        if (lastForm === 'student') {
            showForm('student');
            return;
        }
        
        if (lastForm === 'coordinator') {
            showForm('coordinator');
            return;
        }
        
        if (lastForm === 'admin') {
            showForm('admin');
            return;
        }
        
        var selectionStep = document.getElementById('selection-step');
        selectionStep.style.display = 'block';
    }

    function saveFormState(role) {
        sessionStorage.setItem('lastActiveForm', role);
    }

    var originalShowForm = showForm;
    showForm = function(role) {
        originalShowForm(role);
        saveFormState(role);
    };

    var originalGoBack = goBack;
    goBack = function() {
        originalGoBack();
        sessionStorage.removeItem('lastActiveForm');
    };

    function sanitizeTextValue(value) {
        return value.trim().replace(/[<>"'`]/g, '');
    }

    function sanitizeTextInputs(form) {
        var textInputs = form.querySelectorAll('input[type="text"], input[type="email"]');
        textInputs.forEach(function(input) {
            input.value = sanitizeTextValue(input.value);
        });
    }

    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            sanitizeTextInputs(form);
        });
    });

    document.querySelectorAll('input[type="text"], input[type="email"]').forEach(function(input) {
        input.addEventListener('blur', function() {
            this.value = sanitizeTextValue(this.value);
        });
    });

    initDefaultFormFromQuery();

    // ----- Toggle password visibility -----
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var wrapper = this.closest('.password-toggle-wrapper');
            if (!wrapper) return;
            var input = wrapper.querySelector('.password-toggle');
            if (!input) return;
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('aria-label', 'Hide password');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('aria-label', 'Show password');
            }
        });
        btn.addEventListener('touchstart', function(e) {
            e.preventDefault();
            this.click();
        });
    });

    // ----- Registration Form Validation -----
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // ----- Student Registration Validation -----
    var registrationForm = document.getElementById('studentRegistrationForm');
    var registrationAlert = document.getElementById('registrationAlert');
    
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            // Reset any previous alerts
            if (registrationAlert) {
                registrationAlert.classList.add('d-none');
                registrationAlert.innerHTML = '';
            }
            
            // Validate student ID format
            var studentId = this.querySelector('input[name="student_id"]');
            if (studentId) {
                var pattern = /^TC-[0-9]{2}-[A-Z]-[0-9]{5}$/;
                if (!pattern.test(studentId.value.trim())) {
                    e.preventDefault();
                    showRegistrationAlert('danger', 'Invalid Student ID format. Please use the format: TC-YY-A-00000');
                    studentId.classList.add('is-invalid');
                    return;
                } else {
                    studentId.classList.remove('is-invalid');
                }
            }
            
            // Validate passwords match
            var password = this.querySelector('input[name="password"]');
            var confirmPassword = this.querySelector('input[name="confirm_password"]');
            if (password && confirmPassword) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    showRegistrationAlert('danger', 'Passwords do not match. Please try again.');
                    confirmPassword.classList.add('is-invalid');
                    return;
                } else {
                    confirmPassword.classList.remove('is-invalid');
                }
            }
            
            // Validate password length
            if (password && password.value.length < 8) {
                e.preventDefault();
                showRegistrationAlert('danger', 'Password must be at least 8 characters long.');
                password.classList.add('is-invalid');
                return;
            }
            
            // Validate email
            var email = this.querySelector('input[name="email"]');
            if (email) {
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email.value.trim())) {
                    e.preventDefault();
                    showRegistrationAlert('danger', 'Please enter a valid email address.');
                    email.classList.add('is-invalid');
                    return;
                } else {
                    email.classList.remove('is-invalid');
                }
            }
            
            // Validate name
            var name = this.querySelector('input[name="name"]');
            if (name && name.value.trim().length < 2) {
                e.preventDefault();
                showRegistrationAlert('danger', 'Please enter your full name.');
                name.classList.add('is-invalid');
                return;
            }
            
            // Validate department
            var department = this.querySelector('select[name="department"]');
            if (department && !department.value) {
                e.preventDefault();
                showRegistrationAlert('danger', 'Please select your department.');
                department.classList.add('is-invalid');
                return;
            }
        });
    }
    
    function showRegistrationAlert(type, message) {
        if (registrationAlert) {
            registrationAlert.className = 'alert alert-' + type;
            registrationAlert.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i> ' + message;
            registrationAlert.classList.remove('d-none');
        }
    }

    // ----- Show/Hide Registration Form -----
    var showRegister = document.getElementById('showRegisterBtn');
    var hideRegister = document.getElementById('hideRegisterBtn');
    var studentRegister = document.getElementById('student-register-form');
    var studentLogin = document.getElementById('studentLoginForm');
    var studentLoginHeader = document.getElementById('studentLoginHeader');
    var loginDivider = document.getElementById('loginDivider');
    var createAccountContainer = document.getElementById('createAccountContainer');

    if (showRegister) {
        showRegister.addEventListener('click', function() {
            studentRegister.classList.remove('d-none');
            studentRegister.classList.add('fade-in');
            studentLogin.style.display = 'none';
            // Hide the "Student Login" header
            if (studentLoginHeader) {
                studentLoginHeader.style.display = 'none';
            }
            // Hide the divider
            if (loginDivider) {
                loginDivider.style.display = 'none';
            }
            // Hide the create account button container
            if (createAccountContainer) {
                createAccountContainer.style.display = 'none';
            }
            // Hide the "Back to selection" button
            document.querySelectorAll('.btn-visible').forEach(function(btn) {
                btn.style.display = 'none';
            });
            // Hide any existing alerts
            if (registrationAlert) {
                registrationAlert.classList.add('d-none');
            }
        });
    }
    
    if (hideRegister) {
        hideRegister.addEventListener('click', function(e) {
            e.preventDefault();
            studentRegister.classList.add('d-none');
            studentRegister.classList.remove('fade-in');
            studentLogin.style.display = 'block';
            // Show the "Student Login" header again
            if (studentLoginHeader) {
                studentLoginHeader.style.display = 'block';
            }
            // Show the divider again
            if (loginDivider) {
                loginDivider.style.display = 'flex';
            }
            // Show the create account button container again
            if (createAccountContainer) {
                createAccountContainer.style.display = 'block';
            }
            // Show the "Back to selection" buttons again
            document.querySelectorAll('.btn-visible').forEach(function(btn) {
                btn.style.display = 'inline-block';
            });
            // Reset form validation
            if (registrationForm) {
                registrationForm.classList.remove('was-validated');
                var invalidInputs = registrationForm.querySelectorAll('.is-invalid');
                invalidInputs.forEach(function(input) {
                    input.classList.remove('is-invalid');
                });
            }
            // Hide any alerts
            if (registrationAlert) {
                registrationAlert.classList.add('d-none');
            }
        });
    }

    // ----- Forgot Password Modal Validation -----
    var forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            var email = this.querySelector('input[name="email"]');
            if (email) {
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email.value.trim())) {
                    e.preventDefault();
                    email.classList.add('is-invalid');
                    return;
                } else {
                    email.classList.remove('is-invalid');
                }
            }
        });
    }

    var forgotPasswordModal = document.getElementById('forgotPasswordModal');
    if (forgotPasswordModal) {
        var modal = new bootstrap.Modal(forgotPasswordModal);
        var closeButtons = forgotPasswordModal.querySelectorAll('[data-bs-dismiss="modal"]');
        closeButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                modal.hide();
            });
        });
        
        // Reset form when modal is hidden
        forgotPasswordModal.addEventListener('hidden.bs.modal', function() {
            if (forgotPasswordForm) {
                forgotPasswordForm.reset();
                forgotPasswordForm.classList.remove('was-validated');
                var invalidInputs = forgotPasswordForm.querySelectorAll('.is-invalid');
                invalidInputs.forEach(function(input) {
                    input.classList.remove('is-invalid');
                });
            }
        });
    }

    // ----- Clear URL parameters on page load to prevent alert persistence -----
    if (window.history && window.history.replaceState) {
        var url = window.location.href;
        var params = new URLSearchParams(window.location.search);
        var hasParams = false;
        for (var key of params.keys()) {
            if (key !== '') {
                hasParams = true;
                break;
            }
        }
        if (hasParams) {
            var cleanUrl = url.split('?')[0];
            window.history.replaceState({}, document.title, cleanUrl);
        }
    }
</script>

</body>
</html>