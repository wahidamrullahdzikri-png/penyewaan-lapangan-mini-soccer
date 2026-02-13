<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();

// Clear incomplete session if exists
if (isset($_SESSION['user_id']) && empty($_SESSION['role'])) {
    session_destroy();
}

require_once 'lib/functions.php';
require_once 'lib/auth.php';

// If already logged in as admin, redirect to dashboard
if (isLoggedIn() && getUserRole() === 'admin') {
    redirect('admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid request. CSRF token mismatch.');
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi.";
    } else {
        $role = login($username, $password);
        if ($role) {
            redirectBasedOnRole($role);
        } else {
            $error = "Username atau Password salah.";
        }
    }
}

$csrfToken = generateCSRFToken();
$page_title = "Login Admin";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0a2e1a 0%, #1a4d2e 50%, #0f3d23 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Animated Grass Pattern Background */
        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                repeating-linear-gradient(
                    90deg,
                    rgba(34, 197, 94, 0.03) 0px,
                    rgba(34, 197, 94, 0.03) 2px,
                    transparent 2px,
                    transparent 50px
                ),
                repeating-linear-gradient(
                    0deg,
                    rgba(34, 197, 94, 0.05) 0px,
                    rgba(34, 197, 94, 0.05) 2px,
                    transparent 2px,
                    transparent 50px
                );
            animation: moveField 20s linear infinite;
            z-index: 0;
        }

        @keyframes moveField {
            0% { background-position: 0 0; }
            100% { background-position: 50px 50px; }
        }

        /* Login Container - UKURAN DIPERKECIL */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px; /* DIKECILKAN dari 480px */
            padding: 0 20px;
        }

        /* Login Card */
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px; /* DIKECILKAN dari 24px */
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.4),
                0 0 80px rgba(34, 197, 94, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            overflow: hidden;
            position: relative;
            animation: cardEntrance 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 3px solid rgba(34, 197, 94, 0.3);
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Card Header - UKURAN DIPERKECIL */
        .login-header {
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 50%, #10b981 100%);
            color: white;
            padding: 2rem 1.75rem; /* DIKECILKAN dari 3rem 2rem */
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
        }

        /* Animated Soccer Field Lines in Header */
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255,255,255,0.1) 2px, transparent 2px),
                linear-gradient(90deg, rgba(255,255,255,0.1) 2px, transparent 2px);
            background-size: 40px 40px;
            animation: slideField 15s linear infinite;
            opacity: 0.5;
        }

        @keyframes slideField {
            0% { background-position: 0 0; }
            100% { background-position: 40px 40px; }
        }

        .login-header::after {
            content: '⚽';
            position: absolute;
            font-size: 15rem; /* DIKECILKAN dari 20rem */
            top: -6rem; /* DISESUAIKAN */
            right: -6rem; /* DISESUAIKAN */
            opacity: 0.1;
            animation: rotateBall 20s linear infinite;
            filter: drop-shadow(0 0 40px rgba(255, 255, 255, 0.3));
        }

        @keyframes rotateBall {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-header-icon {
            width: 70px; /* DIKECILKAN dari 100px */
            height: 70px; /* DIKECILKAN dari 100px */
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem; /* DIKECILKAN dari 1.25rem */
            box-shadow: 
                0 15px 35px rgba(0, 0, 0, 0.2),
                0 0 0 6px rgba(255, 255, 255, 0.2),
                inset 0 2px 10px rgba(255, 255, 255, 0.8);
            animation: iconPulse 2.5s ease-in-out infinite;
            border: 3px solid rgba(255, 255, 255, 0.5); /* DIKECILKAN dari 4px */
            position: relative;
            z-index: 2;
        }

        @keyframes iconPulse {
            0%, 100% { 
                transform: scale(1); 
                box-shadow: 
                    0 15px 35px rgba(0, 0, 0, 0.2),
                    0 0 0 6px rgba(255, 255, 255, 0.2),
                    inset 0 2px 10px rgba(255, 255, 255, 0.8);
            }
            50% { 
                transform: scale(1.1); 
                box-shadow: 
                    0 20px 45px rgba(0, 0, 0, 0.3),
                    0 0 0 10px rgba(255, 255, 255, 0.3),
                    inset 0 2px 10px rgba(255, 255, 255, 0.8);
            }
        }

        .login-header-icon i {
            font-size: 2.2rem; /* DIKECILKAN dari 3.2rem */
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-header h3 {
            margin: 0;
            font-size: 1.6rem; /* DIKECILKAN dari 2rem */
            font-weight: 800;
            text-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 2;
            letter-spacing: -0.5px;
        }

        .login-header p {
            margin: 0.5rem 0 0 0; /* DIKECILKAN dari 0.75rem */
            opacity: 0.95;
            font-size: 0.9rem; /* DIKECILKAN dari 1rem */
            font-weight: 500;
            position: relative;
            z-index: 2;
        }

        /* Card Body - UKURAN DIPERKECIL */
        .login-body {
            padding: 2rem 2rem; /* DIKECILKAN dari 3rem 2.5rem */
            position: relative;
            background: linear-gradient(to bottom, #ffffff 0%, #fafafa 100%);
        }

        /* Form Elements */
        .form-label {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.6rem; /* DIKECILKAN dari 0.75rem */
            font-size: 0.9rem; /* DIKECILKAN dari 0.95rem */
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            font-size: 1rem;
        }

        .form-control {
            border: 2.5px solid #e2e8f0;
            border-radius: 12px; /* DIKECILKAN dari 14px */
            padding: 0.85rem 1.1rem; /* DIKECILKAN dari 1rem 1.25rem */
            font-size: 0.95rem; /* DIKECILKAN dari 1rem */
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: #ffffff;
            font-weight: 500;
        }

        .form-control:hover {
            border-color: #cbd5e1;
            background-color: #f8fafc;
        }

        .form-control:focus {
            border-color: #22c55e;
            box-shadow: 
                0 0 0 4px rgba(34, 197, 94, 0.1),
                0 4px 12px rgba(34, 197, 94, 0.15);
            outline: none;
            background-color: white;
            transform: translateY(-1px);
        }

        .form-control::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        /* Button */
        .btn-primary {
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 50%, #10b981 100%);
            border: none;
            color: white;
            padding: 0.85rem; /* DIKECILKAN dari 1rem */
            font-size: 1rem; /* DIKECILKAN dari 1.1rem */
            font-weight: 800;
            border-radius: 12px; /* DIKECILKAN dari 14px */
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 8px 24px rgba(34, 197, 94, 0.35),
                0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-primary:hover::before {
            width: 500px;
            height: 500px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 50%, #22c55e 100%);
            transform: translateY(-3px);
            box-shadow: 
                0 15px 40px rgba(34, 197, 94, 0.45),
                0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-primary:active {
            transform: translateY(-1px);
            box-shadow: 
                0 8px 20px rgba(34, 197, 94, 0.35),
                0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Alert */
        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border: none;
            border-left: 4px solid #dc2626;
            border-radius: 12px; /* DIKECILKAN dari 14px */
            padding: 1rem 1.25rem; /* DIKECILKAN dari 1.25rem 1.5rem */
            margin-bottom: 1.5rem; /* DIKECILKAN dari 1.75rem */
            animation: shake 0.5s;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .alert-danger i {
            font-size: 1.1rem;
            margin-right: 0.5rem;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-12px); }
            75% { transform: translateX(12px); }
        }

        /* Back Link */
        .back-link {
            text-align: center;
            margin-top: 1.5rem; /* DIKECILKAN dari 2rem */
        }

        .back-link a {
            color: #16a34a;
            text-decoration: none;
            font-size: 0.9rem; /* DIKECILKAN dari 1rem */
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            padding: 0.65rem 1.25rem; /* DIKECILKAN dari 0.75rem 1.5rem */
            border-radius: 10px; /* DIKECILKAN dari 12px */
            background: rgba(34, 197, 94, 0.05);
        }

        .back-link a:hover {
            background: rgba(34, 197, 94, 0.15);
            gap: 0.8rem;
            transform: translateX(-3px);
        }

        .back-link a i {
            font-size: 1.1rem;
        }

        /* Security Badge */
        .security-badge {
            text-align: center;
            margin-top: 1.5rem; /* DIKECILKAN dari 2.5rem */
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.85rem; /* DIKECILKAN dari 0.95rem */
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .security-badge i {
            color: #22c55e;
            font-size: 1.2rem;
            vertical-align: middle;
            filter: drop-shadow(0 0 8px rgba(34, 197, 94, 0.6));
        }

        /* Margin Bottom untuk Form Groups */
        .mb-4 {
            margin-bottom: 1.25rem !important; /* DIKECILKAN dari 1.5rem */
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                max-width: 360px; /* DIKECILKAN untuk mobile */
            }

            .login-header {
                padding: 1.75rem 1.5rem;
            }

            .login-body {
                padding: 1.75rem 1.5rem;
            }

            .login-header h3 {
                font-size: 1.4rem;
            }

            .login-header-icon {
                width: 60px;
                height: 60px;
            }

            .login-header-icon i {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Login Card -->
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="login-header-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h3>Login Admin</h3>
                <p>Mini Soccer Rental Dashboard</p>
            </div>

            <!-- Body -->
            <div class="login-body">
                <?php if ($error) : ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Error!</strong> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
                    
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-person-fill text-success"></i>
                            <span>Username</span>
                        </label>
                        <input type="text" 
                               name="username" 
                               class="form-control" 
                               placeholder="Masukkan username Anda"
                               required
                               autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-lock-fill text-success"></i>
                            <span>Password</span>
                        </label>
                        <input type="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Masukkan password Anda"
                               required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk ke Dashboard
                    </button>
                </form>

                <div class="back-link">
                    <a href="index.php">
                        <i class="bi bi-arrow-left-circle-fill"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Security Badge -->
        <div class="security-badge">
            <i class="bi bi-shield-check-fill"></i>
            Halaman ini dilindungi dan aman
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>