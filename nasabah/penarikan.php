<?php

require_once __DIR__ . '/../includes/auth.php';

require_role('nasabah');

require_once __DIR__ . '/../config/database.php';

$page_title = 'Penarikan Saldo';

$userId = $_SESSION['user_id'];

$saldo = 0;
$error = '';
$success = '';


// ========================================
// AMBIL SALDO NASABAH
// ========================================

try {

    $stmt = $pdo->prepare("
        SELECT s.saldo
        FROM saldo s
        INNER JOIN nasabah n
            ON n.id = s.nasabah_id
        WHERE n.user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        'user_id' => $userId
    ]);

    $dataSaldo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dataSaldo) {
        $saldo = (float) $dataSaldo['saldo'];
    }

} catch (PDOException $e) {

    $error = 'Gagal mengambil data saldo.';

}


// ========================================
// PROSES PENARIKAN
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jumlah = (float) ($_POST['jumlah'] ?? 0);
    $metode = $_POST['metode'] ?? '';
    $nomorTujuan = trim($_POST['nomor_tujuan'] ?? '');

    if ($jumlah <= 0) {

        $error = 'Jumlah penarikan harus lebih dari Rp 0.';

    } elseif ($jumlah > $saldo) {

        $error = 'Saldo kamu tidak mencukupi.';

    } elseif (!in_array($metode, ['bank', 'e_wallet'], true)) {

        $error = 'Metode penarikan tidak valid.';

    } elseif ($nomorTujuan === '') {

        $error = 'Nomor tujuan harus diisi.';

    } else {

        try {

            $stmt = $pdo->prepare("
                SELECT id
                FROM nasabah
                WHERE user_id = :user_id
                LIMIT 1
            ");

            $stmt->execute([
                'user_id' => $userId
            ]);

            $nasabah = $stmt->fetch(PDO::FETCH_ASSOC);


            if (!$nasabah) {

                $error = 'Data nasabah tidak ditemukan.';

            } else {

                $nasabahId = (int) $nasabah['id'];

                $kodePenarikan =
                    'WD-' .
                    date('YmdHis') .
                    '-' .
                    strtoupper(
                        substr(
                            bin2hex(random_bytes(3)),
                            0,
                            6
                        )
                    );


                $stmt = $pdo->prepare("
                    INSERT INTO penarikan (
                        kode_penarikan,
                        nasabah_id,
                        jumlah,
                        metode,
                        nomor_tujuan,
                        status
                    )
                    VALUES (
                        :kode_penarikan,
                        :nasabah_id,
                        :jumlah,
                        :metode,
                        :nomor_tujuan,
                        'pending'
                    )
                ");

                $stmt->execute([
                    'kode_penarikan' => $kodePenarikan,
                    'nasabah_id' => $nasabahId,
                    'jumlah' => $jumlah,
                    'metode' => $metode,
                    'nomor_tujuan' => $nomorTujuan
                ]);


                $success =
                    'Pengajuan penarikan berhasil dikirim. ' .
                    'Kode: ' .
                    $kodePenarikan;
            }

        } catch (PDOException $e) {

            $error = 'Gagal menyimpan pengajuan penarikan.';

        }

    }

}


// ========================================
// HEADER & SIDEBAR
// ========================================

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
                    Penarikan Saldo
                </h1>

                <p>
                    Ajukan penarikan saldo Bank Sampah kamu.
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
                        Nasabah
                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- ==================================================
         CONTENT
    ================================================== -->

    <div class="penarikan-container">


        <!-- ==================================================
             SALDO CARD
        ================================================== -->

        <div class="saldo-card">

            <div class="saldo-top">

                <div>

                    <span class="saldo-label">
                        SALDO TERSEDIA
                    </span>

                    <p class="saldo-title">
                        Saldo yang dapat ditarik
                    </p>

                </div>


                <div class="saldo-icon">
                    💰
                </div>

            </div>


            <div class="saldo-value">

                Rp <?= number_format(
                    $saldo,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>


            <div class="saldo-footer">

                <span>
                    ♻ Bank Sampah
                </span>

                <span>
                    Saldo aktif
                </span>

            </div>

        </div>



        <!-- ==================================================
             ALERT ERROR
        ================================================== -->

        <?php if ($error !== ''): ?>

            <div class="alert alert-error">

                <div class="alert-icon">
                    !
                </div>

                <div>

                    <strong>
                        Pengajuan gagal
                    </strong>

                    <p>
                        <?= htmlspecialchars($error) ?>
                    </p>

                </div>

            </div>

        <?php endif; ?>



        <!-- ==================================================
             ALERT SUCCESS
        ================================================== -->

        <?php if ($success !== ''): ?>

            <div class="alert alert-success">

                <div class="alert-icon">
                    ✓
                </div>

                <div>

                    <strong>
                        Pengajuan berhasil
                    </strong>

                    <p>
                        <?= htmlspecialchars($success) ?>
                    </p>

                </div>

            </div>

        <?php endif; ?>



        <!-- ==================================================
             FORM CARD
        ================================================== -->

        <div class="form-card">


            <div class="form-header">

                <div class="form-icon">
                    ↓
                </div>

                <div>

                    <h2>
                        Ajukan Penarikan
                    </h2>

                    <p>
                        Isi data berikut untuk mengajukan
                        penarikan saldo.
                    </p>

                </div>

            </div>



            <form
                method="POST"
                class="penarikan-form"
            >


                <!-- JUMLAH -->

                <div class="form-group">

                    <label for="jumlah">

                        Jumlah Penarikan

                        <span>
                            *
                        </span>

                    </label>


                    <div class="input-wrapper">

                        <span class="input-icon">
                            Rp
                        </span>

                        <input
                            type="number"
                            name="jumlah"
                            id="jumlah"
                            min="1"
                            max="<?= htmlspecialchars($saldo) ?>"
                            step="1"
                            placeholder="Contoh: 10000"
                            required
                        >

                    </div>


                    <small>
                        Maksimal penarikan:
                        Rp <?= number_format(
                            $saldo,
                            0,
                            ',',
                            '.'
                        ) ?>
                    </small>

                </div>



                <!-- METODE -->

                <div class="form-group">

                    <label for="metode">

                        Metode Penarikan

                        <span>
                            *
                        </span>

                    </label>


                    <div class="input-wrapper">

                        <span class="input-icon">
                            ▣
                        </span>

                        <select
                            name="metode"
                            id="metode"
                            required
                        >

                            <option value="">
                                -- Pilih Metode --
                            </option>

                            <option value="bank">
                                Bank
                            </option>

                            <option value="e_wallet">
                                E-Wallet
                            </option>

                        </select>

                    </div>

                </div>



                <!-- NOMOR TUJUAN -->

                <div class="form-group">

                    <label for="nomor_tujuan">

                        Nomor Rekening / E-Wallet

                        <span>
                            *
                        </span>

                    </label>


                    <div class="input-wrapper">

                        <span class="input-icon">
                            #
                        </span>

                        <input
                            type="text"
                            name="nomor_tujuan"
                            id="nomor_tujuan"
                            placeholder="Contoh: 081234567890"
                            required
                        >

                    </div>


                    <small>
                        Pastikan nomor tujuan sudah benar.
                    </small>

                </div>



                <!-- INFO -->

                <div class="info-box">

                    <div class="info-icon">
                        💡
                    </div>

                    <div>

                        <strong>
                            Informasi Penarikan
                        </strong>

                        <p>
                            Pengajuan akan diperiksa oleh admin.
                            Saldo akan diproses sesuai status
                            pengajuan penarikan.
                        </p>

                    </div>

                </div>



                <!-- BUTTON -->

                <button
                    type="submit"
                    class="btn-submit"
                >

                    <span>
                        ↓
                    </span>

                    Ajukan Penarikan

                </button>


            </form>

        </div>



        <!-- ==================================================
             BACK BUTTON
        ================================================== -->

        <a
            href="saldo.php"
            class="back-link"
        >

            ← Kembali ke Saldo

        </a>


    </div>

</main>



<style>

/* ======================================================
   CONTAINER
====================================================== */

.penarikan-container {

    max-width: 850px;

    margin-top: 25px;

}



/* ======================================================
   SALDO CARD
====================================================== */

.saldo-card {

    position: relative;

    overflow: hidden;

    padding: 30px;

    border-radius: 18px;

    background:
        linear-gradient(
            135deg,
            #166534,
            #16a34a
        );

    color: white;

    box-shadow:
        0 12px 30px rgba(22,101,52,.18);

}


.saldo-card::after {

    content: "";

    position: absolute;

    width: 180px;

    height: 180px;

    right: -60px;

    top: -70px;

    border-radius: 50%;

    background: rgba(255,255,255,.08);

}


.saldo-top {

    position: relative;

    z-index: 1;

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

}


.saldo-label {

    font-size: 11px;

    font-weight: 800;

    letter-spacing: 1.5px;

    opacity: .8;

}


.saldo-title {

    margin: 6px 0 0;

    font-size: 14px;

    opacity: .9;

}


.saldo-icon {

    position: relative;

    z-index: 2;

    width: 50px;

    height: 50px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    background: rgba(255,255,255,.15);

    font-size: 23px;

}


.saldo-value {

    position: relative;

    z-index: 1;

    margin-top: 28px;

    font-size: 35px;

    font-weight: 800;

}


.saldo-footer {

    position: relative;

    z-index: 1;

    display: flex;

    justify-content: space-between;

    margin-top: 24px;

    padding-top: 14px;

    border-top: 1px solid rgba(255,255,255,.18);

    font-size: 12px;

    opacity: .85;

}



/* ======================================================
   ALERT
====================================================== */

.alert {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-top: 20px;

    padding: 15px;

    border-radius: 11px;

}


.alert-icon {

    width: 34px;

    height: 34px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 9px;

    font-weight: 800;

}


.alert strong {

    display: block;

    margin-bottom: 3px;

    font-size: 13px;

}


.alert p {

    margin: 0;

    font-size: 12px;

    line-height: 1.5;

}


.alert-error {

    background: #fee2e2;

    color: #991b1b;

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
   FORM CARD
====================================================== */

.form-card {

    margin-top: 20px;

    padding: 30px;

    border-radius: 16px;

    background: white;

    box-shadow:
        0 8px 25px rgba(0,0,0,.05);

}


.form-header {

    display: flex;

    align-items: center;

    gap: 13px;

    padding-bottom: 22px;

    border-bottom: 1px solid #f0f0f0;

}


.form-icon {

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: #dcfce7;

    color: #166534;

    font-size: 23px;

    font-weight: 800;

}


.form-header h2 {

    margin: 0 0 5px;

    color: #166534;

    font-size: 20px;

}


.form-header p {

    margin: 0;

    color: #9ca3af;

    font-size: 12px;

}



/* ======================================================
   FORM
====================================================== */

.penarikan-form {

    margin-top: 25px;

}


.form-group {

    margin-bottom: 22px;

}


.form-group label {

    display: block;

    margin-bottom: 8px;

    color: #374151;

    font-size: 13px;

    font-weight: 700;

}


.form-group label span {

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

    font-size: 13px;

    font-weight: 800;

}


.input-wrapper input,
.input-wrapper select {

    width: 100%;

    height: 48px;

    padding: 0 15px 0 45px;

    border: 1px solid #d1d5db;

    border-radius: 9px;

    background: white;

    color: #1f2937;

    font-family: inherit;

    font-size: 14px;

    transition: .2s;

}


.input-wrapper input:focus,
.input-wrapper select:focus {

    outline: none;

    border-color: #16a34a;

    box-shadow:
        0 0 0 3px rgba(22,163,74,.10);

}


.form-group small {

    display: block;

    margin-top: 6px;

    color: #9ca3af;

    font-size: 11px;

}



/* ======================================================
   INFO BOX
====================================================== */

.info-box {

    display: flex;

    gap: 12px;

    margin: 5px 0 24px;

    padding: 14px;

    border-radius: 10px;

    background: #f0fdf4;

    border: 1px solid #dcfce7;

}


.info-icon {

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

    margin-bottom: 4px;

    color: #166534;

    font-size: 12px;

}


.info-box p {

    margin: 0;

    color: #6b7280;

    font-size: 11px;

    line-height: 1.5;

}



/* ======================================================
   BUTTON
====================================================== */

.btn-submit {

    width: 100%;

    height: 50px;

    border: none;

    border-radius: 9px;

    background: #16a34a;

    color: white;

    font-family: inherit;

    font-size: 14px;

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
   BACK
====================================================== */

.back-link {

    display: inline-block;

    margin-top: 18px;

    color: #166534;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

}


.back-link:hover {

    text-decoration: underline;

}



/* ======================================================
   RESPONSIVE
====================================================== */

@media (max-width: 650px) {

    .penarikan-container {

        margin-top: 20px;

    }


    .saldo-card {

        padding: 23px;

    }


    .saldo-value {

        font-size: 29px;

    }


    .form-card {

        padding: 22px;

    }

}


@media (max-width: 400px) {

    .saldo-card {

        padding: 20px;

    }


    .saldo-value {

        font-size: 25px;

    }


    .saldo-footer {

        flex-direction: column;

        gap: 5px;

    }


    .form-card {

        padding: 18px;

    }

}

</style>



<?php

require_once __DIR__ . '/../includes/footer.php';

?>