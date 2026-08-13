<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

redirect_if_logged_in();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrfToken)) {
        $error = 'Permintaan tidak valid. Silakan coba lagi.';
    } else {

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {

            $error = 'Email dan password wajib diisi.';

        } else {

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    nama,
                    email,
                    password,
                    role,
                    status
                FROM users
                WHERE email = :email
                LIMIT 1
            ");

            $stmt->execute([
                'email' => $email
            ]);

            $user = $stmt->fetch();

            if (!$user) {

                $error = 'Email atau password salah.';

            } elseif ($user['status'] !== 'aktif') {

                $error = 'Akun kamu tidak aktif.';

            } elseif (!password_verify($password, $user['password'])) {

                $error = 'Email atau password salah.';

            } else {

                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                /*
                 * Buat CSRF token baru setelah login.
                 */
                $_SESSION['csrf_token'] = bin2hex(
                    random_bytes(32)
                );

                if ($user['role'] === 'admin') {
                    header('Location: ../admin/dashboard.php');
                    exit;
                }

                if ($user['role'] === 'nasabah') {
                    header('Location: ../nasabah/dashboard.php');
                    exit;
                }

                $error = 'Role pengguna tidak dikenali.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Bank Sampah</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background: #f0fdf4;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .login-card h1 {
            margin-top: 0;
            margin-bottom: 8px;
            text-align: center;
            color: #166534;
        }

        .login-card p {
            margin-top: 0;
            margin-bottom: 25px;
            text-align: center;
            color: #666;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #16a34a;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #16a34a;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #15803d;
        }

        .error {
            margin-bottom: 18px;
            padding: 12px;
            border-radius: 8px;
            background: #fee2e2;
            color: #b91c1c;
        }

        .demo {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            background: #f3f4f6;
            font-size: 13px;
        }
    </style>
</head>

<body>

<div class="login-container">

    <div class="login-card">

        <h1>Bank Sampah</h1>

        <p>Silakan login ke akun kamu</p>

        <?php if ($error !== ''): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars(csrf_token()) ?>"
    >

            <div class="form-group">
                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Masukkan email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <button type="submit" class="btn-login">
                Login
            </button>

        </form>

        <div class="demo">
            <strong>Akun Demo</strong><br><br>

            Admin:<br>
            admin@banksampah.test<br>
            admin123

            <br><br>

            Nasabah:<br>
            budi@banksampah.test<br>
            nasabah123
        </div>

    </div>

</div>

</body>
</html>