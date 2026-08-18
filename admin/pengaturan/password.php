
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Ganti Password';

$userId = (int) ($_SESSION['user_id'] ?? 0);

$error = '';
$success = '';


// ========================================
// PROSES GANTI PASSWORD
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $passwordLama = $_POST['password_lama'] ?? '';
    $passwordBaru = $_POST['password_baru'] ?? '';
    $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';


    // ========================================
    // VALIDASI
    // ========================================

    if ($passwordLama === '') {

        $error = 'Password lama wajib diisi.';

    } elseif ($passwordBaru === '') {

        $error = 'Password baru wajib diisi.';

    } elseif (strlen($passwordBaru) < 6) {

        $error = 'Password baru minimal 6 karakter.';

    } elseif ($konfirmasiPassword === '') {

        $error = 'Konfirmasi password wajib diisi.';

    } elseif ($passwordBaru !== $konfirmasiPassword) {

        $error = 'Konfirmasi password tidak sama dengan password baru.';

    } else {

        try {

            // ========================================
            // AMBIL PASSWORD LAMA DARI DATABASE
            // ========================================

            $stmt = $pdo->prepare("
                SELECT password
                FROM users
                WHERE id = :id
                LIMIT 1
            ");

            $stmt->execute([
                'id' => $userId
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);


            if (!$user) {

                $error = 'Data akun tidak ditemukan.';

            } else {

                // ========================================
                // CEK PASSWORD LAMA
                // ========================================

                if (!password_verify(
                    $passwordLama,
                    $user['password']
                )) {

                    $error = 'Password lama salah.';

                } else {

                    // ========================================
                    // HASH PASSWORD BARU
                    // ========================================

                    $passwordHash = password_hash(
                        $passwordBaru,
                        PASSWORD_DEFAULT
                    );


                    // ========================================
                    // UPDATE PASSWORD
                    // ========================================

                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET password = :password
                        WHERE id = :id
                    ");

                    $stmt->execute([
                        'password' => $passwordHash,
                        'id' => $userId
                    ]);


                    $success = 'Password berhasil diubah.';

                }

            }

        } catch (PDOException $e) {

            $error = 'Gagal mengubah password.';

        }

    }

}


// ========================================
// HEADER & SIDEBAR
// ========================================

require_once __DIR__ . '/../../includes/header.php';

require_once __DIR__ . '/../../includes/sidebar.php';

?>


<main class="main-content">


    <!-- ========================================
         TOPBAR
    ======================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Ganti Password
                </h1>

                <p>
                    Ubah password akun administrator.
                </p>

            </div>

        </div>


        <div class="topbar-right">

            <!-- NOTIFICATION -->

            <button
                type="button"
                class="notification-btn"
                title="Notifikasi"
            >

                🔔

                <span class="notification-badge">
                    0
                </span>

            </button>


            <!-- USER -->

            <div class="user-info">

                <div class="user-avatar">

                    <?= strtoupper(
                        substr(
                            $_SESSION['nama'] ?? 'A',
                            0,
                            1
                        )
                    ) ?>

                </div>


                <div class="user-details">

                    <div class="user-name">

                        <?= htmlspecialchars(
                            $_SESSION['nama'] ?? 'Admin'
                        ) ?>

                    </div>

                    <div class="user-role">
                        Administrator
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ========================================
         CONTENT
    ======================================== -->

    <div
        style="
            max-width: 700px;
            margin-top: 30px;
        "
    >


        <!-- KEMBALI -->

        <a
            href="index.php"
            style="
                display: inline-block;
                margin-bottom: 20px;
                color: #166534;
                text-decoration: none;
                font-weight: 600;
            "
        >
            ← Kembali ke Pengaturan
        </a>


        <!-- ERROR -->

        <?php if ($error !== ''): ?>

            <div
                style="
                    background: #fee2e2;
                    color: #991b1b;
                    padding: 15px 18px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                "
            >

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <!-- SUCCESS -->

        <?php if ($success !== ''): ?>

            <div
                style="
                    background: #dcfce7;
                    color: #166534;
                    padding: 15px 18px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                "
            >

                <?= htmlspecialchars($success) ?>

            </div>

        <?php endif; ?>


        <!-- FORM -->

        <div
            style="
                background: white;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
            "
        >

            <h2
                style="
                    margin-top: 0;
                    color: #166534;
                    margin-bottom: 8px;
                "
            >
                Keamanan Akun
            </h2>


            <p
                style="
                    color: #6b7280;
                    margin-bottom: 25px;
                "
            >
                Masukkan password lama dan password baru kamu.
            </p>


            <form method="POST">


                <!-- PASSWORD LAMA -->

                <div style="margin-bottom: 20px;">

                    <label
                        for="password_lama"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: bold;
                        "
                    >
                        Password Lama
                    </label>

                    <input
                        type="password"
                        name="password_lama"
                        id="password_lama"
                        placeholder="Masukkan password lama"
                        required
                        style="
                            width: 100%;
                            box-sizing: border-box;
                            padding: 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- PASSWORD BARU -->

                <div style="margin-bottom: 20px;">

                    <label
                        for="password_baru"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: bold;
                        "
                    >
                        Password Baru
                    </label>

                    <input
                        type="password"
                        name="password_baru"
                        id="password_baru"
                        placeholder="Minimal 6 karakter"
                        minlength="6"
                        required
                        style="
                            width: 100%;
                            box-sizing: border-box;
                            padding: 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- KONFIRMASI -->

                <div style="margin-bottom: 25px;">

                    <label
                        for="konfirmasi_password"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: bold;
                        "
                    >
                        Konfirmasi Password Baru
                    </label>

                    <input
                        type="password"
                        name="konfirmasi_password"
                        id="konfirmasi_password"
                        placeholder="Ulangi password baru"
                        minlength="6"
                        required
                        style="
                            width: 100%;
                            box-sizing: border-box;
                            padding: 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- BUTTON -->

                <div
                    style="
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                    "
                >

                    <button
                        type="submit"
                        style="
                            border: none;
                            padding: 12px 20px;
                            border-radius: 8px;
                            background: #16a34a;
                            color: white;
                            font-weight: bold;
                            cursor: pointer;
                        "
                    >
                        🔐 Ganti Password
                    </button>


                    <a
                        href="index.php"
                        style="
                            display: inline-block;
                            padding: 12px 20px;
                            border-radius: 8px;
                            background: #e5e7eb;
                            color: #374151;
                            text-decoration: none;
                            font-weight: bold;
                        "
                    >
                        Batal
                    </a>

                </div>


            </form>


        </div>


    </div>


</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>