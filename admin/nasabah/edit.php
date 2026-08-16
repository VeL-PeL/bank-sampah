```php
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Edit Nasabah';


// ========================================
// AMBIL ID
// ========================================

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {

    header('Location: index.php');
    exit;

}


// ========================================
// AMBIL DATA NASABAH
// ========================================

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama,
            email,
            role,
            status
        FROM users
        WHERE id = ?
          AND role = 'nasabah'
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $nasabah =
        $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die('Gagal mengambil data nasabah.');

}


if (!$nasabah) {

    header('Location: index.php');
    exit;

}


// ========================================
// VARIABEL FORM
// ========================================

$nama = $nasabah['nama'];
$email = $nasabah['email'];
$status = $nasabah['status'];

$error = '';


// ========================================
// PROSES UPDATE
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama =
        trim($_POST['nama'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $status =
        $_POST['status'] ?? 'aktif';

    $password =
        $_POST['password'] ?? '';

    $password_confirm =
        $_POST['password_confirm'] ?? '';


    // ========================================
    // VALIDASI
    // ========================================

    if (
        $nama === '' ||
        $email === ''
    ) {

        $error =
            'Nama dan email wajib diisi.';

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Format email tidak valid.';

    } elseif (
        !in_array(
            $status,
            ['aktif', 'nonaktif'],
            true
        )
    ) {

        $error =
            'Status tidak valid.';

    } elseif (
        $password !== '' &&
        strlen($password) < 6
    ) {

        $error =
            'Password baru minimal 6 karakter.';

    } elseif (
        $password !== '' &&
        $password !== $password_confirm
    ) {

        $error =
            'Konfirmasi password tidak sama.';

    } else {

        try {

            // ========================================
            // CEK EMAIL DUPLIKAT
            // ========================================

            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE email = ?
                  AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $email,
                $id
            ]);

            $email_exists =
                $stmt->fetch(PDO::FETCH_ASSOC);


            if ($email_exists) {

                $error =
                    'Email sudah digunakan oleh akun lain.';

            } else {


                // ========================================
                // UPDATE DENGAN PASSWORD
                // ========================================

                if ($password !== '') {

                    $password_hash =
                        password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET
                            nama = ?,
                            email = ?,
                            password = ?,
                            status = ?
                        WHERE id = ?
                          AND role = 'nasabah'
                    ");

                    $stmt->execute([
                        $nama,
                        $email,
                        $password_hash,
                        $status,
                        $id
                    ]);

                } else {


                    // ========================================
                    // UPDATE TANPA PASSWORD
                    // ========================================

                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET
                            nama = ?,
                            email = ?,
                            status = ?
                        WHERE id = ?
                          AND role = 'nasabah'
                    ");

                    $stmt->execute([
                        $nama,
                        $email,
                        $status,
                        $id
                    ]);

                }


                // ========================================
                // REDIRECT
                // ========================================

                header(
                    'Location: index.php?success=edit'
                );

                exit;

            }

        } catch (PDOException $e) {

            $error =
                'Gagal memperbarui data nasabah.';

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
                    Edit Nasabah
                </h1>

                <p>
                    Perbarui data nasabah.
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
         FORM EDIT
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
                    ✏️ Edit Data Nasabah
                </h2>

                <p
                    style="
                        margin: 0;
                        color: #6b7280;
                    "
                >
                    Ubah informasi akun nasabah.
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


                <!-- STATUS -->

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
                        Status
                    </label>

                    <select
                        name="status"
                        required
                        style="
                            width: 100%;
                            padding: 12px 14px;
                            border: 1px solid #d1d5db;
                            border-radius: 10px;
                            box-sizing: border-box;
                            font-size: 15px;
                            background: white;
                        "
                    >

                        <option
                            value="aktif"
                            <?= $status === 'aktif'
                                ? 'selected'
                                : '' ?>
                        >
                            Aktif
                        </option>

                        <option
                            value="nonaktif"
                            <?= $status === 'nonaktif'
                                ? 'selected'
                                : '' ?>
                        >
                            Nonaktif
                        </option>

                    </select>

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
                        Password Baru
                    </label>

                    <input
                        type="password"
                        name="password"
                        minlength="6"
                        placeholder="Kosongkan jika tidak ingin mengubah"
                        style="
                            width: 100%;
                            padding: 12px 14px;
                            border: 1px solid #d1d5db;
                            border-radius: 10px;
                            box-sizing: border-box;
                            font-size: 15px;
                        "
                    >

                    <small
                        style="
                            display: block;
                            margin-top: 6px;
                            color: #6b7280;
                        "
                    >
                        Isi hanya jika ingin mengganti password.
                    </small>

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
                        Konfirmasi Password Baru
                    </label>

                    <input
                        type="password"
                        name="password_confirm"
                        minlength="6"
                        placeholder="Ulangi password baru"
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
                        💾 Simpan Perubahan
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
