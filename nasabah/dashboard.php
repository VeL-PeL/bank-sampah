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
// VARIABLE
// ======================================================

$saldo = 0;
$totalSetoran = 0;
$totalBerat = 0;
$setoranTerbaru = [];


// ======================================================
// AMBIL DATA DASHBOARD
// ======================================================

try {

    /*
     * SALDO
     *
     * Saldo dihitung dari setoran yang sudah diterima.
     */

    $stmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(total_harga), 0) AS saldo
        FROM setoran
        WHERE user_id = :user_id
        AND status = 'diterima'
    ");

    $stmt->execute([
        'user_id' => $userId
    ]);

    $saldo = (float) $stmt->fetchColumn();


    /*
     * TOTAL SETORAN
     */

    $stmt = $pdo->prepare("
        SELECT
            COUNT(*)
        FROM setoran
        WHERE user_id = :user_id
    ");

    $stmt->execute([
        'user_id' => $userId
    ]);

    $totalSetoran = (int) $stmt->fetchColumn();


    /*
     * TOTAL BERAT
     */

    $stmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(berat), 0)
        FROM setoran
        WHERE user_id = :user_id
        AND status = 'diterima'
    ");

    $stmt->execute([
        'user_id' => $userId
    ]);

    $totalBerat = (float) $stmt->fetchColumn();


    /*
     * 5 SETORAN TERBARU
     */

    $stmt = $pdo->prepare("
        SELECT
            setoran.id,
            jenis_sampah.nama_sampah,
            setoran.berat,
            setoran.total_harga,
            setoran.status,
            setoran.created_at
        FROM setoran

        INNER JOIN jenis_sampah
            ON setoran.jenis_sampah_id = jenis_sampah.id

        WHERE setoran.user_id = :user_id

        ORDER BY setoran.created_at DESC

        LIMIT 5
    ");

    $stmt->execute([
        'user_id' => $userId
    ]);

    $setoranTerbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    // Jika terjadi error database,
    // dashboard tetap bisa ditampilkan.

}


// ======================================================
// PAGE
// ======================================================

$page_title = 'Dashboard Nasabah';

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
                    Dashboard
                </h1>

                <p>
                    Selamat datang kembali,
                    <?= htmlspecialchars($_SESSION['nama']) ?>!
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
         WELCOME CARD
    ================================================== -->

    <div class="welcome-card">

        <div class="welcome-content">

            <div>

                <span class="welcome-label">
                    BANK SAMPAH
                </span>

                <h2>
                    Selamat datang,
                    <?= htmlspecialchars($_SESSION['nama']) ?>! 👋
                </h2>

                <p>
                    Kelola setoran sampah dan pantau saldo kamu
                    dengan mudah melalui dashboard.
                </p>

            </div>


            <div class="welcome-icon">
                ♻
            </div>

        </div>

    </div>



    <!-- ==================================================
         STAT CARDS
    ================================================== -->

    <div class="stats-grid">


        <!-- SALDO -->

        <div class="stat-card saldo-card">

            <div class="stat-icon">
                💰
            </div>

            <div class="stat-content">

                <span class="stat-label">
                    Saldo Saya
                </span>

                <h3>
                    Rp <?= number_format(
                        $saldo,
                        0,
                        ',',
                        '.'
                    ) ?>
                </h3>

            </div>

        </div>


        <!-- TOTAL SETORAN -->

        <div class="stat-card">

            <div class="stat-icon">
                ♻
            </div>

            <div class="stat-content">

                <span class="stat-label">
                    Total Setoran
                </span>

                <h3>
                    <?= number_format(
                        $totalSetoran,
                        0,
                        ',',
                        '.'
                    ) ?>

                    <small>
                        transaksi
                    </small>
                </h3>

            </div>

        </div>


        <!-- TOTAL BERAT -->

        <div class="stat-card">

            <div class="stat-icon">
                ⚖
            </div>

            <div class="stat-content">

                <span class="stat-label">
                    Sampah Terkumpul
                </span>

                <h3>
                    <?= number_format(
                        $totalBerat,
                        2,
                        ',',
                        '.'
                    ) ?>

                    <small>
                        kg
                    </small>
                </h3>

            </div>

        </div>


    </div>



    <!-- ==================================================
         QUICK ACTION
    ================================================== -->

    <div class="section-header">

        <div>

            <h2>
                Aksi Cepat
            </h2>

            <p>
                Kelola aktivitas bank sampah kamu.
            </p>

        </div>

    </div>


    <div class="action-grid">


        <a
            href="setor.php"
            class="action-card"
        >

            <div class="action-icon">
                ♻
            </div>

            <div class="action-content">

                <h3>
                    Setor Sampah
                </h3>

                <p>
                    Ajukan setoran sampah baru.
                </p>

            </div>

            <span class="action-arrow">
                →
            </span>

        </a>



        <a
            href="riwayat.php"
            class="action-card"
        >

            <div class="action-icon">
                ↕
            </div>

            <div class="action-content">

                <h3>
                    Riwayat Setor
                </h3>

                <p>
                    Lihat seluruh riwayat setoran.
                </p>

            </div>

            <span class="action-arrow">
                →
            </span>

        </a>



        <a
            href="saldo.php"
            class="action-card"
        >

            <div class="action-icon">
                💰
            </div>

            <div class="action-content">

                <h3>
                    Lihat Saldo
                </h3>

                <p>
                    Cek saldo tabungan kamu.
                </p>

            </div>

            <span class="action-arrow">
                →
            </span>

        </a>


    </div>



    <!-- ==================================================
         TRANSAKSI TERBARU
    ================================================== -->

    <div class="section-header transaction-header">

        <div>

            <h2>
                Setoran Terbaru
            </h2>

            <p>
                Lima transaksi setoran terakhir kamu.
            </p>

        </div>


        <?php if (!empty($setoranTerbaru)): ?>

            <a
                href="riwayat.php"
                class="view-all"
            >
                Lihat Semua →
            </a>

        <?php endif; ?>

    </div>



    <div class="transaction-card">


        <?php if (empty($setoranTerbaru)): ?>


            <div class="empty-dashboard">

                <div class="empty-dashboard-icon">
                    ♻
                </div>

                <h3>
                    Belum Ada Setoran
                </h3>

                <p>
                    Kamu belum memiliki transaksi setoran.
                </p>

                <a
                    href="setor.php"
                    class="btn-primary"
                >
                    + Setor Sampah
                </a>

            </div>


        <?php else: ?>


            <div class="transaction-list">


                <?php foreach ($setoranTerbaru as $item): ?>


                    <?php

                    $status = strtolower(
                        $item['status']
                    );

                    ?>


                    <div class="transaction-item">


                        <!-- ICON -->

                        <div class="transaction-icon">
                            ♻
                        </div>


                        <!-- INFO -->

                        <div class="transaction-info">

                            <h3>

                                <?= htmlspecialchars(
                                    $item['nama_sampah']
                                ) ?>

                            </h3>

                            <p>

                                <?= number_format(
                                    (float) $item['berat'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>

                                kg

                                •
                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime(
                                        $item['created_at']
                                    )
                                ) ?>

                            </p>

                        </div>


                        <!-- TOTAL -->

                        <div class="transaction-total">

                            <strong>

                                Rp
                                <?= number_format(
                                    (float) $item['total_harga'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>

                            </strong>


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


                        <!-- DETAIL -->

                        <a
                            href="detail-setoran.php?id=<?= (int) $item['id'] ?>"
                            class="transaction-detail"
                        >
                            Detail
                        </a>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </div>



</main>



<style>

/* ======================================================
   WELCOME
====================================================== */

.welcome-card {

    margin-top: 25px;

    padding: 28px 30px;

    border-radius: 16px;

    background: linear-gradient(
        135deg,
        #166534,
        #16a34a
    );

    color: white;

    overflow: hidden;

}


.welcome-content {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 30px;

}


.welcome-label {

    font-size: 11px;

    font-weight: 800;

    letter-spacing: 1.5px;

    opacity: .8;

}


.welcome-content h2 {

    margin: 8px 0 7px;

    font-size: 25px;

}


.welcome-content p {

    margin: 0;

    max-width: 650px;

    color: rgba(255,255,255,.85);

    font-size: 14px;

    line-height: 1.6;

}


.welcome-icon {

    width: 80px;

    height: 80px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 22px;

    background: rgba(255,255,255,.15);

    font-size: 42px;

}



/* ======================================================
   STATISTICS
====================================================== */

.stats-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;

    margin-top: 22px;

}


.stat-card {

    min-height: 125px;

    padding: 22px;

    background: #ffffff;

    border-radius: 14px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.05);

    display: flex;

    align-items: center;

    gap: 16px;

}


.stat-icon {

    width: 52px;

    height: 52px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    background: #dcfce7;

    color: #166534;

    font-size: 24px;

}


.stat-label {

    display: block;

    margin-bottom: 5px;

    color: #6b7280;

    font-size: 13px;

}


.stat-content h3 {

    margin: 0;

    color: #166534;

    font-size: 22px;

}


.stat-content small {

    color: #9ca3af;

    font-size: 12px;

    font-weight: 500;

}



/* ======================================================
   SECTION HEADER
====================================================== */

.section-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-top: 32px;

    margin-bottom: 15px;

}


.section-header h2 {

    margin: 0 0 5px;

    color: #166534;

    font-size: 19px;

}


.section-header p {

    margin: 0;

    color: #6b7280;

    font-size: 13px;

}


.view-all {

    color: #166534;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

}


.view-all:hover {

    text-decoration: underline;

}



/* ======================================================
   QUICK ACTION
====================================================== */

.action-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

}


.action-card {

    position: relative;

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 18px;

    background: #ffffff;

    border-radius: 13px;

    text-decoration: none;

    box-shadow:
        0 6px 20px rgba(0,0,0,.04);

    transition: .2s;

}


.action-card:hover {

    transform: translateY(-2px);

    box-shadow:
        0 10px 25px rgba(0,0,0,.08);

}


.action-icon {

    width: 45px;

    height: 45px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 11px;

    background: #f0fdf4;

    color: #166534;

    font-size: 20px;

}


.action-content h3 {

    margin: 0 0 4px;

    color: #1f2937;

    font-size: 14px;

}


.action-content p {

    margin: 0;

    color: #9ca3af;

    font-size: 12px;

}


.action-arrow {

    margin-left: auto;

    color: #166534;

    font-size: 18px;

}



/* ======================================================
   TRANSACTION
====================================================== */

.transaction-header {

    margin-top: 35px;

}


.transaction-card {

    background: #ffffff;

    border-radius: 14px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.05);

    overflow: hidden;

}


.transaction-list {

    width: 100%;

}


.transaction-item {

    display: grid;

    grid-template-columns:
        auto 1fr auto auto;

    align-items: center;

    gap: 15px;

    padding: 17px 20px;

    border-bottom: 1px solid #f0f0f0;

}


.transaction-item:last-child {

    border-bottom: none;

}


.transaction-icon {

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 11px;

    background: #dcfce7;

    color: #166534;

    font-size: 19px;

}


.transaction-info h3 {

    margin: 0 0 4px;

    color: #1f2937;

    font-size: 14px;

}


.transaction-info p {

    margin: 0;

    color: #9ca3af;

    font-size: 12px;

}


.transaction-total {

    display: flex;

    flex-direction: column;

    align-items: flex-end;

    gap: 5px;

}


.transaction-total strong {

    color: #166534;

    font-size: 14px;

}


.transaction-detail {

    padding: 7px 11px;

    border: 1px solid #bbf7d0;

    border-radius: 7px;

    background: #f0fdf4;

    color: #166534;

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;

}


.transaction-detail:hover {

    background: #166534;

    color: white;

}



/* ======================================================
   STATUS
====================================================== */

.status {

    display: inline-flex;

    align-items: center;

    gap: 4px;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 10px;

    font-weight: 700;

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
   EMPTY
====================================================== */

.empty-dashboard {

    padding: 45px 20px;

    text-align: center;

}


.empty-dashboard-icon {

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


.empty-dashboard h3 {

    margin: 0 0 7px;

    color: #374151;

}


.empty-dashboard p {

    margin: 0 0 18px;

    color: #9ca3af;

    font-size: 13px;

}


.btn-primary {

    display: inline-flex;

    padding: 10px 16px;

    border-radius: 8px;

    background: #166534;

    color: white;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

}


.btn-primary:hover {

    background: #14532d;

}



/* ======================================================
   RESPONSIVE
====================================================== */

@media (max-width: 900px) {

    .stats-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .stats-grid .stat-card:first-child {

        grid-column:
            span 2;

    }


    .action-grid {

        grid-template-columns:
            1fr;

    }

}


@media (max-width: 650px) {

    .welcome-card {

        padding: 22px;

    }


    .welcome-icon {

        display: none;

    }


    .welcome-content h2 {

        font-size: 21px;

    }


    .stats-grid {

        grid-template-columns: 1fr;

    }


    .stats-grid .stat-card:first-child {

        grid-column: auto;

    }


    .transaction-item {

        grid-template-columns:
            auto 1fr auto;

    }


    .transaction-total {

        grid-column: 2;

        align-items: flex-start;

    }


    .transaction-detail {

        grid-column: 3;

        grid-row: 1 / span 2;

    }

}


@media (max-width: 450px) {

    .welcome-content h2 {

        font-size: 19px;

    }


    .stat-card {

        padding: 18px;

    }


    .stat-content h3 {

        font-size: 20px;

    }


    .transaction-item {

        gap: 10px;

        padding: 14px;

    }

}

</style>



<?php

require_once __DIR__ . '/../includes/footer.php';

?>