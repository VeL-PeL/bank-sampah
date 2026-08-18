
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Edit Profil';

$userId = (int) ($_SESSION['user_id'] ?? 0);

$nama = '';
$email = '';

$error = '';
$success = '';


// ========================================
// AMBIL DATA ADMIN
// ========================================

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama,
            email
        FROM users
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $userId
    ]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {

        $error = 'Data admin tidak ditemukan.';

    } else {

        $nama = $admin['nama'];
        $email = $admin['email'];

    }

} catch (PDOException $e) {

    $error = 'Gagal mengambil data profil.';

}


// ========================================
// PROSES UPDATE PROFIL
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');


    // ========================================
    // VALIDASI NAMA
    // ========================================

    if ($nama === '') {

        $error = 'Nama wajib diisi.';

    } elseif (strlen($nama) < 3) {

        $error = 'Nama minimal 3 karakter.';

    }


    // ========================================
    // VALIDASI EMAIL
    // ========================================

    elseif ($email === '') {

        $error = 'Email wajib diisi.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Format email tidak valid.';

    }


    // ========================================
    // UPDATE
    // ========================================

    else {

        try {

            // ----------------------------------------
            // CEK EMAIL MILIK USER LAIN
            // ----------------------------------------

            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE email = :email
                AND id != :id
                LIMIT 1
            ");

            $stmt->execute([
                'email' => $email,
                'id' => $userId
            ]);

            $emailExists = $stmt->fetch(PDO::FETCH_ASSOC);


            if ($emailExists) {

                $error = 'Email tersebut sudah digunakan oleh akun lain.';

            } else {

                // ----------------------------------------
                // UPDATE DATA
                // ----------------------------------------

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET
                        nama = :nama,
                        email = :email
                    WHERE id = :id
                ");

                $stmt->execute([
                    'nama' => $nama,
                    'email' => $email,
                    'id' => $userId
                ]);


                // ----------------------------------------
                // UPDATE SESSION
                // ----------------------------------------

                $_SESSION['nama'] = $nama;


                $success = 'Profil berhasil diperbarui.';

            }

        } catch (PDOException $e) {

            $error = 'Gagal memperbarui profil.';

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
                    Edit Profil
                </h1>

                <p>
                    Ubah informasi akun administrator.
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
                Informasi Profil
            </h2>


            <p
                style="
                    color: #6b7280;
                    margin-bottom: 25px;
                "
            >
                Perubahan akan diterapkan pada akun administrator yang sedang login.
            </p>


            <form method="POST">


                <!-- NAMA -->

                <div style="margin-bottom: 20px;">

                    <label
                        for="nama"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: bold;
                        "
                    >
                        Nama
                    </label>

                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="<?= htmlspecialchars($nama) ?>"
                        placeholder="Masukkan nama"
                        required
                        minlength="3"
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


                <!-- EMAIL -->

                <div style="margin-bottom: 25px;">

                    <label
                        for="email"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: bold;
                        "
                    >
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="contoh@email.com"
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
                        💾 Simpan Perubahan
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

