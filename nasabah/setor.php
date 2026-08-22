<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('nasabah');


// ======================================================
// CEK LOGIN
// ======================================================

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];


// ======================================================
// CSRF
// ======================================================

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];


// ======================================================
// VARIABLE
// ======================================================

$error = '';
$success = '';
$jenisSampah = [];


// ======================================================
// AMBIL JENIS SAMPAH
// ======================================================

try {

    $stmt = $pdo->query("
        SELECT
            id,
            nama_sampah,
            harga_per_kg
        FROM jenis_sampah
        WHERE status = 'aktif'
        ORDER BY nama_sampah ASC
    ");

    $jenisSampah = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = 'Gagal mengambil data jenis sampah.';
}


// ======================================================
// PROSES FORM
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {

        $error = 'Permintaan tidak valid. Silakan coba lagi.';

    } else {

        $jenisSampahId = (int) ($_POST['jenis_sampah_id'] ?? 0);
        $berat = (float) ($_POST['berat'] ?? 0);


        // ==============================================
        // VALIDASI
        // ==============================================

        if ($jenisSampahId <= 0) {

            $error = 'Silakan pilih jenis sampah.';

        } elseif ($berat <= 0) {

            $error = 'Berat sampah harus lebih dari 0 kg.';

        } else {

            try {

                // ==========================================
                // AMBIL DATA SAMPAH
                // ==========================================

                $stmt = $pdo->prepare("
                    SELECT
                        id,
                        nama_sampah,
                        harga_per_kg
                    FROM jenis_sampah
                    WHERE id = :id
                    AND status = 'aktif'
                    LIMIT 1
                ");

                $stmt->execute([
                    'id' => $jenisSampahId
                ]);

                $sampah = $stmt->fetch(PDO::FETCH_ASSOC);


                if (!$sampah) {

                    $error = 'Jenis sampah tidak ditemukan.';

                } else {

                    // ======================================
                    // HITUNG TOTAL
                    // ======================================

                    $hargaPerKg = (float) $sampah['harga_per_kg'];

                    $totalHarga = $berat * $hargaPerKg;


                    // ======================================
                    // SIMPAN SETORAN
                    // ======================================

                    $stmt = $pdo->prepare("
                        INSERT INTO setoran (
                            user_id,
                            jenis_sampah_id,
                            berat,
                            harga_per_kg,
                            total_harga,
                            status
                        )
                        VALUES (
                            :user_id,
                            :jenis_sampah_id,
                            :berat,
                            :harga_per_kg,
                            :total_harga,
                            'menunggu'
                        )
                    ");

                    $stmt->execute([
                        'user_id' => $userId,
                        'jenis_sampah_id' => $jenisSampahId,
                        'berat' => $berat,
                        'harga_per_kg' => $hargaPerKg,
                        'total_harga' => $totalHarga
                    ]);


                    $success = 'Pengajuan setoran berhasil dikirim.';
                }

            } catch (PDOException $e) {

                $error = 'Gagal menyimpan pengajuan setoran.';

            }
        }
    }
}


// ======================================================
// HEADER & SIDEBAR
// ======================================================

$page_title = 'Setor Sampah';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

?>


<main class="main-content">


    <!-- ==================================================
         TOPBAR
    ================================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Setor Sampah
                </h1>

                <p>
                    Ajukan setoran sampah kamu dengan mudah.
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
                            $_SESSION['nama'] ?? 'N',
                            0,
                            1
                        )
                    ) ?>

                </div>


                <div class="user-details">

                    <div class="user-name">

                        <?= htmlspecialchars(
                            $_SESSION['nama'] ?? 'Nasabah'
                        ) ?>

                    </div>

                    <div class="user-role">
                        Nasabah
                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- ==================================================
         CONTENT
    ================================================== -->

    <div class="setor-container">


        <!-- CARD -->

        <div class="setor-card">


            <!-- HEADER CARD -->

            <div class="setor-header">

                <div class="setor-title">

                    <div class="setor-icon">
                        ♻
                    </div>

                    <div>

                        <h2>
                            Ajukan Setoran
                        </h2>

                        <p>
                            Isi data sampah yang ingin kamu setorkan.
                        </p>

                    </div>

                </div>

            </div>



            <!-- ALERT ERROR -->

            <?php if ($error !== ''): ?>

                <div class="alert alert-error">

                    <span class="alert-icon">
                        !
                    </span>

                    <span>
                        <?= htmlspecialchars($error) ?>
                    </span>

                </div>

            <?php endif; ?>



            <!-- ALERT SUCCESS -->

            <?php if ($success !== ''): ?>

                <div class="alert alert-success">

                    <span class="alert-icon">
                        ✓
                    </span>

                    <span>
                        <?= htmlspecialchars($success) ?>
                    </span>

                </div>

            <?php endif; ?>



            <!-- JIKA BELUM ADA JENIS SAMPAH -->

            <?php if (empty($jenisSampah)): ?>

                <div class="empty-state">

                    <div class="empty-icon">
                        ♻
                    </div>

                    <h3>
                        Belum Ada Jenis Sampah
                    </h3>

                    <p>
                        Belum tersedia jenis sampah yang aktif.
                        Silakan hubungi admin.
                    </p>

                </div>

            <?php else: ?>


                <!-- ==================================================
                     FORM
                ================================================== -->

                <form
                    method="POST"
                    action=""
                    class="setor-form"
                >


                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken) ?>"
                    >



                    <!-- JENIS SAMPAH -->

                    <div class="form-group">

                        <label for="jenis_sampah">

                            Jenis Sampah

                            <span class="required">
                                *
                            </span>

                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                ♻
                            </span>

                            <select
                                name="jenis_sampah_id"
                                id="jenis_sampah"
                                required
                            >

                                <option value="">
                                    -- Pilih Jenis Sampah --
                                </option>


                                <?php foreach ($jenisSampah as $sampah): ?>

                                    <option
                                        value="<?= (int) $sampah['id'] ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $sampah['nama_sampah']
                                        ) ?>

                                        -
                                        Rp
                                        <?= number_format(
                                            (float) $sampah['harga_per_kg'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                        /kg

                                    </option>

                                <?php endforeach; ?>


                            </select>

                        </div>

                        <small>
                            Pilih jenis sampah yang ingin kamu setorkan.
                        </small>

                    </div>



                    <!-- BERAT -->

                    <div class="form-group">

                        <label for="berat">

                            Berat Sampah

                            <span class="required">
                                *
                            </span>

                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                ⚖
                            </span>

                            <input
                                type="number"
                                name="berat"
                                id="berat"
                                min="0.01"
                                step="0.01"
                                placeholder="Contoh: 2.5"
                                required
                            >

                            <span class="input-suffix">
                                kg
                            </span>

                        </div>

                        <small>
                            Masukkan berat sampah dalam kilogram.
                        </small>

                    </div>



                    <!-- INFO -->

                    <div class="info-box">

                        <div class="info-box-icon">
                            💡
                        </div>

                        <div>

                            <strong>
                                Informasi
                            </strong>

                            <p>
                                Harga setoran akan dihitung berdasarkan
                                jenis sampah dan berat yang kamu masukkan.
                            </p>

                        </div>

                    </div>



                    <!-- BUTTON -->

                    <button
                        type="submit"
                        class="btn-submit"
                    >

                        <span>
                            ✓
                        </span>

                        Ajukan Setoran

                    </button>


                </form>


            <?php endif; ?>


        </div>


    </div>


</main>



<style>

/* ======================================================
   CONTAINER
====================================================== */

.setor-container {

    width: 100%;

    max-width: 900px;

    margin-top: 25px;

}



/* ======================================================
   CARD
====================================================== */

.setor-card {

    background: #ffffff;

    border-radius: 16px;

    padding: 30px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, .06);

}



/* ======================================================
   HEADER
====================================================== */

.setor-header {

    padding-bottom: 24px;

    border-bottom: 1px solid #e5e7eb;

}


.setor-title {

    display: flex;

    align-items: center;

    gap: 14px;

}


.setor-icon {

    width: 52px;

    height: 52px;

    border-radius: 13px;

    background: #dcfce7;

    color: #166534;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 26px;

}


.setor-title h2 {

    margin: 0 0 5px;

    color: #166534;

    font-size: 22px;

}


.setor-title p {

    margin: 0;

    color: #6b7280;

    font-size: 14px;

}



/* ======================================================
   ALERT
====================================================== */

.alert {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 22px;

    padding: 14px 16px;

    border-radius: 10px;

    font-size: 14px;

}


.alert-icon {

    width: 25px;

    height: 25px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    font-weight: 800;

}


.alert-error {

    background: #fee2e2;

    color: #b91c1c;

}


.alert-error .alert-icon {

    background: #fecaca;

}


.alert-success {

    background: #dcfce7;

    color: #166534;

}


.alert-success .alert-icon {

    background: #bbf7d0;

}



/* ======================================================
   FORM
====================================================== */

.setor-form {

    margin-top: 25px;

}


.form-group {

    margin-bottom: 25px;

}


.form-group label {

    display: block;

    margin-bottom: 9px;

    color: #374151;

    font-size: 14px;

    font-weight: 700;

}


.required {

    color: #dc2626;

}


.input-wrapper {

    position: relative;

    display: flex;

    align-items: center;

}


.input-icon {

    position: absolute;

    left: 14px;

    z-index: 2;

    color: #166534;

    font-size: 17px;

}


select,
input[type="number"] {

    width: 100%;

    height: 50px;

    padding: 0 15px 0 45px;

    border: 1px solid #d1d5db;

    border-radius: 10px;

    background: #ffffff;

    color: #1f2937;

    font-family: inherit;

    font-size: 14px;

    transition: .2s;

}


select {

    cursor: pointer;

}


select:focus,
input[type="number"]:focus {

    outline: none;

    border-color: #16a34a;

    box-shadow:
        0 0 0 3px rgba(22, 163, 74, .12);

}


.input-suffix {

    position: absolute;

    right: 15px;

    color: #6b7280;

    font-size: 13px;

    font-weight: 600;

    pointer-events: none;

}


input[type="number"] {

    padding-right: 45px;

}


.form-group small {

    display: block;

    margin-top: 7px;

    color: #9ca3af;

    font-size: 12px;

}



/* ======================================================
   INFO BOX
====================================================== */

.info-box {

    display: flex;

    gap: 12px;

    padding: 15px;

    margin: 5px 0 25px;

    background: #f0fdf4;

    border: 1px solid #dcfce7;

    border-radius: 10px;

}


.info-box-icon {

    width: 32px;

    height: 32px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 8px;

    background: #dcfce7;

}


.info-box strong {

    display: block;

    margin-bottom: 3px;

    color: #166534;

    font-size: 13px;

}


.info-box p {

    margin: 0;

    color: #6b7280;

    font-size: 12px;

    line-height: 1.5;

}



/* ======================================================
   BUTTON
====================================================== */

.btn-submit {

    width: 100%;

    height: 50px;

    border: none;

    border-radius: 10px;

    background: #16a34a;

    color: #ffffff;

    font-family: inherit;

    font-size: 15px;

    font-weight: 700;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    transition: .2s;

}


.btn-submit:hover {

    background: #15803d;

    transform: translateY(-1px);

}


.btn-submit:active {

    transform: translateY(0);

}



/* ======================================================
   EMPTY STATE
====================================================== */

.empty-state {

    margin-top: 25px;

    padding: 45px 20px;

    text-align: center;

    background: #f9fafb;

    border-radius: 12px;

}


.empty-icon {

    width: 60px;

    height: 60px;

    margin: 0 auto 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #dcfce7;

    color: #166534;

    font-size: 28px;

}


.empty-state h3 {

    margin: 0 0 8px;

    color: #374151;

}


.empty-state p {

    margin: 0;

    color: #6b7280;

    font-size: 14px;

}



/* ======================================================
   RESPONSIVE
====================================================== */

@media (max-width: 768px) {

    .setor-card {

        padding: 22px;

    }


    .setor-title h2 {

        font-size: 20px;

    }

}


@media (max-width: 480px) {

    .setor-card {

        padding: 18px;

    }


    .setor-title {

        align-items: flex-start;

    }


    .setor-icon {

        width: 45px;

        height: 45px;

        font-size: 22px;

    }


    .setor-title h2 {

        font-size: 18px;

    }


    .setor-title p {

        font-size: 12px;

    }

}

</style>



<?php

require_once __DIR__ . '/../includes/footer.php';

?>