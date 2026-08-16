
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Edit Jenis Sampah';


// ========================================
// AMBIL ID
// ========================================

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {

    header('Location: index.php');
    exit;

}


// ========================================
// AMBIL DATA
// ========================================

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama_sampah,
            harga_per_kg,
            deskripsi,
            status
        FROM jenis_sampah
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $sampah = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die('Gagal mengambil data jenis sampah.');

}


if (!$sampah) {

    header('Location: index.php');
    exit;

}


// ========================================
// DATA FORM
// ========================================

$nama_sampah = $sampah['nama_sampah'];
$harga_per_kg = $sampah['harga_per_kg'];
$deskripsi = $sampah['deskripsi'] ?? '';
$status = $sampah['status'];

$errors = [];


// ========================================
// PROSES UPDATE
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_sampah = trim(
        $_POST['nama_sampah'] ?? ''
    );

    $harga_per_kg = trim(
        $_POST['harga_per_kg'] ?? ''
    );

    $deskripsi = trim(
        $_POST['deskripsi'] ?? ''
    );

    $status = $_POST['status'] ?? 'aktif';


    // ====================================
    // VALIDASI NAMA
    // ====================================

    if ($nama_sampah === '') {

        $errors[] =
            'Nama sampah wajib diisi.';

    } elseif (strlen($nama_sampah) < 2) {

        $errors[] =
            'Nama sampah minimal 2 karakter.';

    }


    // ====================================
    // VALIDASI HARGA
    // ====================================

    if ($harga_per_kg === '') {

        $errors[] =
            'Harga per kg wajib diisi.';

    } elseif (!is_numeric($harga_per_kg)) {

        $errors[] =
            'Harga per kg harus berupa angka.';

    } elseif ((float) $harga_per_kg < 0) {

        $errors[] =
            'Harga per kg tidak boleh negatif.';

    }


    // ====================================
    // VALIDASI STATUS
    // ====================================

    if (
        $status !== 'aktif'
        && $status !== 'nonaktif'
    ) {

        $errors[] =
            'Status tidak valid.';

    }


    // ====================================
    // CEK NAMA DUPLIKAT
    // ====================================

    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare("
                SELECT id
                FROM jenis_sampah
                WHERE nama_sampah = ?
                  AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $nama_sampah,
                $id
            ]);

            if ($stmt->fetch()) {

                $errors[] =
                    'Nama sampah sudah digunakan oleh data lain.';

            }

        } catch (PDOException $e) {

            $errors[] =
                'Gagal memeriksa nama sampah.';

        }

    }


    // ====================================
    // UPDATE DATA
    // ====================================

    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare("
                UPDATE jenis_sampah
                SET
                    nama_sampah = ?,
                    harga_per_kg = ?,
                    deskripsi = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $nama_sampah,
                $harga_per_kg,
                $deskripsi !== ''
                    ? $deskripsi
                    : null,
                $status,
                $id
            ]);


            header(
                'Location: index.php?success=2'
            );

            exit;

        } catch (PDOException $e) {

            if (
                $e->getCode() === '23000'
            ) {

                $errors[] =
                    'Nama sampah sudah digunakan.';

            } else {

                $errors[] =
                    'Gagal memperbarui jenis sampah.';

            }

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
                    Edit Jenis Sampah
                </h1>

                <p>
                    Perbarui informasi jenis sampah.
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
            max-width: 800px;
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

            <h2
                style="
                    margin-top: 0;
                    color: #166534;
                "
            >
                ✏️ Edit Data Jenis Sampah
            </h2>

            <p
                style="
                    color: #6b7280;
                    margin-bottom: 25px;
                "
            >
                Ubah informasi jenis sampah sesuai kebutuhan.
            </p>


            <!-- ====================================
                 ERROR
            ==================================== -->

            <?php if (!empty($errors)): ?>

                <div
                    style="
                        padding: 15px;
                        margin-bottom: 20px;
                        border-radius: 10px;
                        background: #fee2e2;
                        color: #991b1b;
                    "
                >

                    <strong>
                        Terjadi kesalahan:
                    </strong>

                    <ul
                        style="
                            margin-bottom: 0;
                        "
                    >

                        <?php foreach (
                            $errors
                            as $error
                        ): ?>

                            <li>
                                <?= htmlspecialchars(
                                    $error
                                ) ?>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>


            <!-- ====================================
                 FORM
            ==================================== -->

            <form
                method="POST"
                action=""
            >


                <!-- NAMA SAMPAH -->

                <div
                    style="
                        margin-bottom: 20px;
                    "
                >

                    <label
                        for="nama_sampah"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                            color: #374151;
                        "
                    >
                        Nama Sampah
                    </label>

                    <input
                        type="text"
                        id="nama_sampah"
                        name="nama_sampah"
                        value="<?= htmlspecialchars(
                            $nama_sampah
                        ) ?>"
                        required
                        style="
                            width: 100%;
                            box-sizing: border-box;
                            padding: 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 10px;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- HARGA -->

                <div
                    style="
                        margin-bottom: 20px;
                    "
                >

                    <label
                        for="harga_per_kg"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                            color: #374151;
                        "
                    >
                        Harga per Kg
                    </label>

                    <input
                        type="number"
                        id="harga_per_kg"
                        name="harga_per_kg"
                        value="<?= htmlspecialchars(
                            $harga_per_kg
                        ) ?>"
                        min="0"
                        step="0.01"
                        required
                        style="
                            width: 100%;
                            box-sizing: border-box;
                            padding: 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 10px;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- DESKRIPSI -->

                <div
                    style="
                        margin-bottom: 20px;
                    "
                >

                    <label
                        for="deskripsi"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                            color: #374151;
                        "
                    >
                        Deskripsi
                    </label>

                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="4"
                        style="
                            width: 100%;
                            box-sizing: border-box;
                            padding: 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 10px;
                            font-size: 15px;
                            resize: vertical;
                            font-family: inherit;
                        "
                    ><?= htmlspecialchars(
                        $deskripsi
                    ) ?></textarea>

                </div>


                <!-- STATUS -->

                <div
                    style="
                        margin-bottom: 25px;
                    "
                >

                    <label
                        for="status"
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
                        id="status"
                        name="status"
                        required
                        style="
                            width: 100%;
                            box-sizing: border-box;
                            padding: 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 10px;
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


                <!-- TOMBOL -->

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
                            padding: 12px 20px;
                            border: none;
                            border-radius: 10px;
                            background: #166534;
                            color: white;
                            font-size: 15px;
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
