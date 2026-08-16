```php
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Tambah Nasabah';


// ========================================
// VARIABEL
// ========================================

$nama = '';
$email = '';

$error = '';
$success = '';


// ========================================
// PROSES TAMBAH NASABAH
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';


    // ========================================
    // VALIDASI
    // ========================================

    if (
        $nama === '' ||
        $email === '' ||
        $password === '' ||
        $password_confirm === ''
    ) {

        $error =
            'Semua field wajib diisi.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error =
            'Format email tidak valid.';

    } elseif (strlen($password) < 6) {

        $error =
            'Password minimal 6 karakter.';

    } elseif ($password !== $password_confirm) {

        $error =
            'Konfirmasi password tidak sama.';

    } else {

        try {

            // ========================================
            // CEK EMAIL
            // ========================================

            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE email = ?
                LIMIT 1
            ");

            $stmt->execute([
                $email
            ]);

            $existing_user =
                $stmt->fetch(PDO::FETCH_ASSOC);


            if ($existing_user) {

                $error =
                    'Email sudah digunakan.';

            } else {

                // ========================================
                // HASH PASSWORD
                // ========================================

                $password_hash =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                // ========================================
                // SIMPAN NASABAH
                // ========================================

                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        nama,
                        email,
                        password,
                        role,
                        status
                    )
                    VALUES (
                        ?,
                        ?,
                        ?,
                        'nasabah',
                        'aktif'
                    )
                ");

                $stmt->execute([
                    $nama,
                    $email,
                    $password_hash
                ]);


                // ========================================
                // REDIRECT
                // ========================================

                header(
                    'Location: index.php?success=tambah'
                );

                exit;
            }

        } catch (PDOException $e) {

            $error =
                'Gagal menyimpan data nasabah.';
        }
    }
}


// ========================================
// HEADER
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
                    Tambah Nasabah
                </h1>

                <p>
                    Tambahkan nasabah baru ke sistem.
                </p>

            </div>

        </div>


        <div class="topbar-right">

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


            <div class="user-info">

                <div class="user-avatar">

                    <?= strtoupper(
                        substr(
                            $_SESSION['nama'],
                            0,
                            1
                        )
                    ) ?>

                </div>

                <div class="user-details">

                    <div class="user-name">

                        <?= htmlspecialchars(
                            $_SESSION['nama']
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
         FORM
    ======================================== -->

    <div
        style="
            max-width: 750px;
            margin: 30px auto;
        "
    >

        <div
            style="
                background: white;
                padding: 30px;
                border-radius: 15px;
                box-shadow:
                    0 10px 30px rgba(0,0,0,.06);
            "
        >

            <div
                style="
                    margin-bottom: 25px;
                "
            >

                <h2
                    style="
                        margin: 0 0 8px 0;
                        color: #166534;
                    "
                >
                    👤 Form Tambah Nasabah
                </h2>

                <p
                    style="
                        margin: 0;
                        color: #6b7280;
                    "
                >
                    Isi data nasabah dengan lengkap.
                </p>

            </div>


            <!-- ERROR -->

            <?php if ($error !== ''): ?>

                <div
                    style="
                        padding: 14px;
                        margin-bottom: 20px;
                        border-radius: 10px;
                        background: #fee2e2;
                        color: #b91c1c;
                    "
                >
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                action=""
            >

                <!-- NAMA -->

                <div
                    style="
                        margin-bottom: 20px;
                    "
                >

                    <label
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                            color: #374151;
                        "
                    >
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        name="nama"
                        value="<?= htmlspecialchars($nama) ?>"
                        placeholder="Masukkan nama lengkap"
                        required
                        style="
                            width: 100%;
                            padding: 12px 14px;
                            border: 1px solid #d1d5db;
                            border-radius: 10px;
                            box-sizing: border-box;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- EMAIL -->

                <div
                    style="
                        margin-bottom: 20px;
                    "
                >

                    <label
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                            color: #374151;
                        "
                    >
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="contoh@email.com"
                        required
                        style="
                            width: 100%;
                            padding: 12px 14px;
                            border: 1px solid #d1d5db;
                            border-radius: 10px;
                            box-sizing: border-box;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- PASSWORD -->

                <div
                    style="
                        margin-bottom: 20px;
                    "
                >

                    <label
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                            color: #374151;
                        "
                    >
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Minimal 6 karakter"
                        minlength="6"
                        required
                        style="
                            width: 100%;
                            padding: 12px 14px;
                            border: 1px solid #d1d5db;
                            border-radius: 10px;
                            box-sizing: border-box;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- KONFIRMASI PASSWORD -->

                <div
                    style="
                        margin-bottom: 25px;
                    "
                >

                    <label
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                            color: #374151;
                        "
                    >
                        Konfirmasi Password
                    </label>

                    <input
                        type="password"
                        name="password_confirm"
                        placeholder="Ulangi password"
                        minlength="6"
                        required
                        style="
                            width: 100%;
                            padding: 12px 14px;
                            border: 1px solid #d1d5db;
                            border-radius: 10px;
                            box-sizing: border-box;
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

                    <a
                        href="index.php"
                        style="
                            display: inline-block;
                            padding: 12px 20px;
                            border-radius: 10px;
                            background: #f3f4f6;
                            color: #374151;
                            text-decoration: none;
                            font-weight: 600;
                        "
                    >
                        ← Kembali
                    </a>


                    <button
                        type="submit"
                        style="
                            padding: 12px 22px;
                            border: none;
                            border-radius: 10px;
                            background: #166534;
                            color: white;
                            font-weight: 600;
                            cursor: pointer;
                        "
                    >
                        + Tambah Nasabah
                    </button>

                </div>

            </form>

        </div>

    </div>

</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>
```
