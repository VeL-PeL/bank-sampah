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
// AMBIL ID SETORAN
// ======================================================

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: riwayat.php');
    exit;
}


// ======================================================
// VARIABLE
// ======================================================

$setoran = null;
$error = '';


// ======================================================
// AMBIL DATA SETORAN
// ======================================================

try {

    $stmt = $pdo->prepare("
        SELECT
            setoran.id,
            jenis_sampah.nama_sampah,
            setoran.berat,
            setoran.harga_per_kg,
            setoran.total_harga,
            setoran.status,
            setoran.created_at
        FROM setoran

        INNER JOIN jenis_sampah
            ON setoran.jenis_sampah_id = jenis_sampah.id

        WHERE setoran.id = :id
        AND setoran.user_id = :user_id

        LIMIT 1
    ");

    $stmt->execute([
        'id' => $id,
        'user_id' => $userId
    ]);

    $setoran = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$setoran) {
        $error = 'Data setoran tidak ditemukan.';
    }

} catch (PDOException $e) {

    $error = 'Gagal mengambil detail setoran.';

}


// ======================================================
// HEADER & SIDEBAR
// ======================================================

$page_title = 'Detail Setoran';

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
                    Detail Setoran
                </h1>

                <p>
                    Lihat informasi lengkap setoran sampah kamu.
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

    <div class="detail-container">


        <!-- KEMBALI -->

        <a
            href="riwayat.php"
            class="back-link"
        >
            ← Kembali ke Riwayat Setor
        </a>



        <!-- CARD -->

        <div class="detail-card">


            <?php if ($error !== ''): ?>


                <!-- ERROR -->

                <div class="error-state">

                    <div class="error-icon">
                        !
                    </div>

                    <h2>
                        Data Tidak Ditemukan
                    </h2>

                    <p>
                        <?= htmlspecialchars($error) ?>
                    </p>

                    <a
                        href="riwayat.php"
                        class="btn-back"
                    >
                        Kembali ke Riwayat
                    </a>

                </div>


            <?php else: ?>


                <!-- ==================================================
                     CARD HEADER
                ================================================== -->

                <div class="detail-header">

                    <div class="detail-title">

                        <div class="detail-icon">
                            ♻
                        </div>

                        <div>

                            <h2>
                                Detail Setoran
                            </h2>

                            <p>
                                Informasi setoran sampah kamu
                            </p>

                        </div>

                    </div>


                    <!-- STATUS -->

                    <?php

                    $status = strtolower(
                        $setoran['status']
                    );

                    ?>

                    <span
                        class="
                            status
                            status-<?= htmlspecialchars($status) ?>
                        "
                    >

                        <?php if ($status === 'diterima'): ?>

                            ✓

                        <?php elseif ($status === 'ditolak'): ?>

                            ✕

                        <?php else: ?>

                            ⏳

                        <?php endif; ?>


                        <?= ucfirst(
                            htmlspecialchars($status)
                        ) ?>

                    </span>

                </div>



                <!-- ==================================================
                     TOTAL
                ================================================== -->

                <div class="total-card">

                    <div>

                        <span class="total-label">
                            Total Nilai Setoran
                        </span>

                        <div class="total-value">

                            Rp
                            <?= number_format(
                                (float) $setoran['total_harga'],
                                0,
                                ',',
                                '.'
                            ) ?>

                        </div>

                    </div>

                    <div class="total-icon">
                        💰
                    </div>

                </div>



                <!-- ==================================================
                     DETAIL INFORMATION
                ================================================== -->

                <div class="info-section">

                    <h3>
                        Informasi Setoran
                    </h3>


                    <!-- JENIS SAMPAH -->

                    <div class="info-row">

                        <div class="info-label">

                            <span class="info-icon">
                                ♻
                            </span>

                            <span>
                                Jenis Sampah
                            </span>

                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $setoran['nama_sampah']
                            ) ?>

                        </div>

                    </div>



                    <!-- BERAT -->

                    <div class="info-row">

                        <div class="info-label">

                            <span class="info-icon">
                                ⚖
                            </span>

                            <span>
                                Berat
                            </span>

                        </div>

                        <div class="info-value">

                            <?= number_format(
                                (float) $setoran['berat'],
                                2,
                                ',',
                                '.'
                            ) ?>

                            kg

                        </div>

                    </div>



                    <!-- HARGA -->

                    <div class="info-row">

                        <div class="info-label">

                            <span class="info-icon">
                                Rp
                            </span>

                            <span>
                                Harga per Kg
                            </span>

                        </div>

                        <div class="info-value">

                            Rp
                            <?= number_format(
                                (float) $setoran['harga_per_kg'],
                                0,
                                ',',
                                '.'
                            ) ?>

                        </div>

                    </div>



                    <!-- TOTAL -->

                    <div class="info-row">

                        <div class="info-label">

                            <span class="info-icon">
                                💰
                            </span>

                            <span>
                                Total Harga
                            </span>

                        </div>

                        <div class="info-value total-text">

                            Rp
                            <?= number_format(
                                (float) $setoran['total_harga'],
                                0,
                                ',',
                                '.'
                            ) ?>

                        </div>

                    </div>



                    <!-- STATUS -->

                    <div class="info-row">

                        <div class="info-label">

                            <span class="info-icon">
                                ●
                            </span>

                            <span>
                                Status
                            </span>

                        </div>

                        <div class="info-value">

                            <span
                                class="
                                    status
                                    status-<?= htmlspecialchars($status) ?>
                                "
                            >

                                <?php if ($status === 'diterima'): ?>

                                    ✓

                                <?php elseif ($status === 'ditolak'): ?>

                                    ✕

                                <?php else: ?>

                                    ⏳

                                <?php endif; ?>


                                <?= ucfirst(
                                    htmlspecialchars($status)
                                ) ?>

                            </span>

                        </div>

                    </div>



                    <!-- TANGGAL -->

                    <div class="info-row">

                        <div class="info-label">

                            <span class="info-icon">
                                📅
                            </span>

                            <span>
                                Tanggal Setoran
                            </span>

                        </div>

                        <div class="info-value">

                            <?= date(
                                'd-m-Y',
                                strtotime(
                                    $setoran['created_at']
                                )
                            ) ?>

                            <span class="time">

                                <?= date(
                                    'H:i',
                                    strtotime(
                                        $setoran['created_at']
                                    )
                                ) ?>

                                WIB

                            </span>

                        </div>

                    </div>

                </div>



                <!-- ==================================================
                     FOOTER
                ================================================== -->

                <div class="detail-footer">

                    <a
                        href="riwayat.php"
                        class="btn-back"
                    >
                        ← Kembali ke Riwayat
                    </a>

                </div>


            <?php endif; ?>


        </div>


    </div>


</main>



<style>

/* ======================================================
   DETAIL CONTAINER
====================================================== */

.detail-container {

    width: 100%;

    max-width: 1000px;

    margin-top: 25px;

}


/* ======================================================
   BACK LINK
====================================================== */

.back-link {

    display: inline-flex;

    align-items: center;

    margin-bottom: 18px;

    color: #166534;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

}


.back-link:hover {

    color: #14532d;

}



/* ======================================================
   CARD
====================================================== */

.detail-card {

    background: #ffffff;

    border-radius: 16px;

    padding: 30px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, .06);

}



/* ======================================================
   HEADER
====================================================== */

.detail-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding-bottom: 24px;

    border-bottom: 1px solid #e5e7eb;

}


.detail-title {

    display: flex;

    align-items: center;

    gap: 14px;

}


.detail-icon {

    width: 50px;

    height: 50px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #dcfce7;

    color: #166534;

    font-size: 25px;

}


.detail-title h2 {

    margin: 0 0 5px;

    color: #166534;

    font-size: 22px;

}


.detail-title p {

    margin: 0;

    color: #6b7280;

    font-size: 14px;

}



/* ======================================================
   STATUS
====================================================== */

.status {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 5px;

    padding: 8px 13px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;

}


.status-menunggu {

    background: #fef3c7;

    color: #92400e;

}


.status-diterima {

    background: #dcfce7;

    color: #166534;

}


.status-ditolak {

    background: #fee2e2;

    color: #b91c1c;

}



/* ======================================================
   TOTAL CARD
====================================================== */

.total-card {

    margin-top: 25px;

    padding: 24px;

    border-radius: 14px;

    background: linear-gradient(
        135deg,
        #059669,
        #10b981
    );

    color: #ffffff;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

}


.total-label {

    display: block;

    margin-bottom: 7px;

    font-size: 14px;

    opacity: .9;

}


.total-value {

    font-size: 30px;

    font-weight: 800;

}


.total-icon {

    width: 55px;

    height: 55px;

    border-radius: 14px;

    background: rgba(255,255,255,.18);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 26px;

}



/* ======================================================
   INFORMATION
====================================================== */

.info-section {

    margin-top: 28px;

}


.info-section h3 {

    margin: 0 0 15px;

    color: #166534;

    font-size: 18px;

}


.info-row {

    min-height: 62px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    border-bottom: 1px solid #e5e7eb;

}


.info-row:last-child {

    border-bottom: none;

}


.info-label {

    display: flex;

    align-items: center;

    gap: 10px;

    color: #6b7280;

    font-size: 14px;

}


.info-icon {

    width: 34px;

    height: 34px;

    border-radius: 9px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f0fdf4;

    color: #166534;

    font-size: 15px;

    font-weight: 700;

}


.info-value {

    color: #1f2937;

    font-size: 14px;

    font-weight: 600;

    text-align: right;

}


.total-text {

    color: #166534;

    font-size: 16px;

}


.time {

    color: #9ca3af;

    font-size: 12px;

    font-weight: 400;

}



/* ======================================================
   FOOTER
====================================================== */

.detail-footer {

    margin-top: 25px;

    padding-top: 20px;

    border-top: 1px solid #e5e7eb;

}


.btn-back {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 10px 16px;

    background: #166534;

    color: #ffffff;

    text-decoration: none;

    border-radius: 8px;

    font-size: 14px;

    font-weight: 600;

    transition: .2s;

}


.btn-back:hover {

    background: #14532d;

}



/* ======================================================
   ERROR
====================================================== */

.error-state {

    padding: 50px 20px;

    text-align: center;

}


.error-icon {

    width: 60px;

    height: 60px;

    margin: 0 auto 15px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #fee2e2;

    color: #b91c1c;

    font-size: 28px;

    font-weight: 800;

}


.error-state h2 {

    margin: 0 0 8px;

    color: #374151;

}


.error-state p {

    margin: 0 0 20px;

    color: #6b7280;

}



/* ======================================================
   RESPONSIVE
====================================================== */

@media (max-width: 768px) {

    .detail-container {

        margin-top: 20px;

    }


    .detail-card {

        padding: 20px;

    }


    .detail-header {

        align-items: flex-start;

        flex-direction: column;

    }


    .total-card {

        padding: 20px;

    }


    .total-value {

        font-size: 25px;

    }


    .info-row {

        padding: 13px 0;

        align-items: flex-start;

    }


    .info-value {

        text-align: right;

    }

}


@media (max-width: 480px) {

    .detail-title h2 {

        font-size: 19px;

    }


    .detail-title p {

        font-size: 13px;

    }


    .detail-icon {

        width: 44px;

        height: 44px;

        font-size: 21px;

    }


    .total-value {

        font-size: 22px;

    }


    .info-label {

        font-size: 13px;

    }


    .info-value {

        font-size: 13px;

    }

}

</style>



<?php

require_once __DIR__ . '/../includes/footer.php';

?>