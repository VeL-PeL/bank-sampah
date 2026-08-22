<?php

require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../config/database.php';

$page_title = 'Dashboard Admin';


// ======================================================
// DEFAULT DATA
// ======================================================

$totalNasabah      = 0;
$totalSetoran      = 0;
$totalNilaiSetoran = 0;
$totalPenarikan    = 0;
$setoranPending    = 0;
$penarikanPending  = 0;

$transaksiTerbaru = [];


// ======================================================
// TOTAL NASABAH AKTIF
// ======================================================

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM nasabah
        WHERE status = 'aktif'
    ");

    $totalNasabah = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    $totalNasabah = 0;

}


// ======================================================
// TOTAL TRANSAKSI SETOR
// ======================================================

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM setoran
    ");

    $totalSetoran = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    $totalSetoran = 0;

}


// ======================================================
// TOTAL NILAI SETORAN DITERIMA
// ======================================================

try {

    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total_harga), 0)
        FROM setoran
        WHERE status = 'diterima'
    ");

    $totalNilaiSetoran = (float) $stmt->fetchColumn();

} catch (PDOException $e) {

    $totalNilaiSetoran = 0;

}


// ======================================================
// TOTAL PENARIKAN
// ======================================================

try {

    $stmt = $pdo->query("
        SELECT COALESCE(SUM(jumlah), 0)
        FROM penarikan
        WHERE status IN ('diterima', 'selesai')
    ");

    $totalPenarikan = (float) $stmt->fetchColumn();

} catch (PDOException $e) {

    $totalPenarikan = 0;

}


// ======================================================
// SETORAN MENUNGGU
// ======================================================

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM setoran
        WHERE status = 'menunggu'
    ");

    $setoranPending = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    $setoranPending = 0;

}


// ======================================================
// PENARIKAN PENDING
// ======================================================

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM penarikan
        WHERE status = 'pending'
    ");

    $penarikanPending = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    $penarikanPending = 0;

}


// ======================================================
// TRANSAKSI TERBARU
// ======================================================

try {

    $stmt = $pdo->query("
        SELECT
            setoran.id,
            users.nama AS nama_nasabah,
            jenis_sampah.nama_sampah,
            setoran.berat,
            setoran.total_harga,
            setoran.status,
            setoran.created_at
        FROM setoran

        INNER JOIN users
            ON setoran.user_id = users.id

        INNER JOIN jenis_sampah
            ON setoran.jenis_sampah_id = jenis_sampah.id

        ORDER BY setoran.created_at DESC

        LIMIT 5
    ");

    $transaksiTerbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $transaksiTerbaru = [];

}


// ======================================================
// HEADER & SIDEBAR
// ======================================================

require_once __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../includes/sidebar.php';

?>


<style>

    /* ==================================================
       DASHBOARD ADMIN
    ================================================== */

    .admin-dashboard {
        padding-bottom: 40px;
    }


    /* ==================================================
       WELCOME
    ================================================== */

    .welcome-section {
        margin-top: 25px;
        margin-bottom: 25px;
    }

    .welcome-section h2 {
        margin: 0;
        color: #14532d;
        font-size: 24px;
    }

    .welcome-section p {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
    }


    /* ==================================================
       STAT CARD
    ================================================== */

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .stat-card {
        position: relative;
        overflow: hidden;
        background: #ffffff;
        border-radius: 16px;
        padding: 22px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 6px 20px rgba(15, 23, 42, .05);
        transition: .2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(15, 23, 42, .08);
    }

    .stat-card::after {
        content: "";
        position: absolute;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        right: -35px;
        top: -35px;
        background: #f0fdf4;
    }

    .stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 23px;
        background: #dcfce7;
        position: relative;
        z-index: 1;
    }

    .stat-label {
        margin-top: 18px;
        color: #64748b;
        font-size: 13px;
    }

    .stat-number {
        margin-top: 5px;
        color: #14532d;
        font-size: 27px;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-description {
        margin-top: 7px;
        color: #94a3b8;
        font-size: 12px;
    }


    /* ==================================================
       PENDING CARD
       ================================================== */

    .pending-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-top: 18px;
    }

    .pending-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px;
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 6px 20px rgba(15, 23, 42, .05);
    }

    .pending-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .pending-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fef3c7;
        font-size: 21px;
    }

    .pending-title {
        font-weight: 700;
        color: #334155;
        font-size: 14px;
    }

    .pending-description {
        margin-top: 4px;
        color: #94a3b8;
        font-size: 12px;
    }

    .pending-number {
        font-size: 25px;
        font-weight: 700;
        color: #b45309;
    }


    /* ==================================================
       TRANSAKSI
       ================================================== */

    .transaction-card {
        margin-top: 25px;
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 6px 20px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .transaction-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 22px 24px;
        border-bottom: 1px solid #eef2f7;
    }

    .transaction-title h2 {
        margin: 0;
        color: #14532d;
        font-size: 18px;
    }

    .transaction-title p {
        margin: 5px 0 0;
        color: #94a3b8;
        font-size: 13px;
    }

    .btn-all {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 14px;
        background: #166534;
        color: #ffffff;
        border-radius: 9px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        transition: .2s ease;
    }

    .btn-all:hover {
        background: #14532d;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .transaction-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 750px;
    }

    .transaction-table th {
        padding: 13px 20px;
        text-align: left;
        background: #f0fdf4;
        color: #166534;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .transaction-table td {
        padding: 15px 20px;
        border-bottom: 1px solid #eef2f7;
        color: #334155;
        font-size: 13px;
    }

    .transaction-table tbody tr:last-child td {
        border-bottom: none;
    }

    .transaction-table tbody tr:hover {
        background: #f8fafc;
    }

    .nasabah-name {
        font-weight: 600;
        color: #1e293b;
    }

    .total-value {
        font-weight: 700;
        color: #166534;
        white-space: nowrap;
    }

    .date-value {
        color: #64748b;
        white-space: nowrap;
    }


    /* ==================================================
       STATUS
       ================================================== */

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-diterima {
        background: #dcfce7;
        color: #166534;
    }

    .status-ditolak {
        background: #fee2e2;
        color: #b91c1c;
    }

    .status-menunggu {
        background: #fef3c7;
        color: #92400e;
    }


    /* ==================================================
       EMPTY
       ================================================== */

    .empty-state {
        padding: 45px 20px;
        text-align: center;
        color: #94a3b8;
    }

    .empty-icon {
        font-size: 35px;
        margin-bottom: 10px;
    }

    .empty-state p {
        margin: 0;
        font-size: 14px;
    }


    /* ==================================================
       QUICK ACTION
       ================================================== */

    .quick-section {
        margin-top: 25px;
    }

    .quick-section-title {
        margin-bottom: 15px;
    }

    .quick-section-title h2 {
        margin: 0;
        color: #14532d;
        font-size: 18px;
    }

    .quick-section-title p {
        margin: 5px 0 0;
        color: #94a3b8;
        font-size: 13px;
    }

    .quick-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .quick-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 19px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 15px;
        text-decoration: none;
        box-shadow: 0 6px 20px rgba(15, 23, 42, .04);
        transition: .2s ease;
    }

    .quick-card:hover {
        transform: translateY(-2px);
        border-color: #bbf7d0;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .07);
    }

    .quick-icon {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 12px;
        background: #dcfce7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .quick-content {
        flex: 1;
    }

    .quick-content strong {
        display: block;
        color: #1e293b;
        font-size: 14px;
    }

    .quick-content span {
        display: block;
        margin-top: 4px;
        color: #94a3b8;
        font-size: 12px;
    }

    .quick-arrow {
        color: #166534;
        font-size: 20px;
    }


    /* ==================================================
       RESPONSIVE
       ================================================== */

    @media (max-width: 1100px) {

        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }


    @media (max-width: 700px) {

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .pending-grid {
            grid-template-columns: 1fr;
        }

        .quick-grid {
            grid-template-columns: 1fr;
        }

        .transaction-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .welcome-section h2 {
            font-size: 21px;
        }

    }


    @media (max-width: 500px) {

        .stat-card {
            padding: 18px;
        }

        .pending-card {
            padding: 17px;
        }

        .transaction-header {
            padding: 18px;
        }

    }

</style>


<main class="main-content">

<div class="admin-dashboard">


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
                    <?= htmlspecialchars(
                        $_SESSION['nama'] ?? 'Admin'
                    ) ?>!
                </p>

            </div>

        </div>


        <div class="topbar-right">

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


    <!-- ==================================================
         WELCOME
         ================================================== -->

    <div class="welcome-section">

        <h2>
            Ringkasan Bank Sampah
        </h2>

        <p>
            Pantau aktivitas, setoran, nasabah, dan penarikan
            dari satu halaman.
        </p>

    </div>


    <!-- ==================================================
         STATISTIK
         ================================================== -->

    <div class="stats-grid">


        <!-- NASABAH -->

        <div class="stat-card">

            <div class="stat-top">

                <div class="stat-icon">
                    👥
                </div>

            </div>

            <div class="stat-label">
                Total Nasabah
            </div>

            <div class="stat-number">

                <?= number_format(
                    $totalNasabah,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

            <div class="stat-description">
                Nasabah aktif
            </div>

        </div>


        <!-- SETORAN -->

        <div class="stat-card">

            <div class="stat-top">

                <div class="stat-icon">
                    ♻️
                </div>

            </div>

            <div class="stat-label">
                Transaksi Setor
            </div>

            <div class="stat-number">

                <?= number_format(
                    $totalSetoran,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

            <div class="stat-description">
                Semua transaksi setor
            </div>

        </div>


        <!-- NILAI SETORAN -->

        <div class="stat-card">

            <div class="stat-top">

                <div class="stat-icon">
                    💰
                </div>

            </div>

            <div class="stat-label">
                Nilai Setoran
            </div>

            <div class="stat-number"
                 style="font-size:22px;">

                Rp <?= number_format(
                    $totalNilaiSetoran,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

            <div class="stat-description">
                Total setoran diterima
            </div>

        </div>


        <!-- PENARIKAN -->

        <div class="stat-card">

            <div class="stat-top">

                <div class="stat-icon">
                    💸
                </div>

            </div>

            <div class="stat-label">
                Total Penarikan
            </div>

            <div class="stat-number"
                 style="font-size:22px;">

                Rp <?= number_format(
                    $totalPenarikan,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

            <div class="stat-description">
                Diterima / selesai
            </div>

        </div>

    </div>


    <!-- ==================================================
         PENDING
         ================================================== -->

    <div class="pending-grid">


        <!-- SETORAN -->

        <div class="pending-card">

            <div class="pending-left">

                <div class="pending-icon">
                    ⏳
                </div>

                <div>

                    <div class="pending-title">
                        Setoran Menunggu
                    </div>

                    <div class="pending-description">
                        Perlu diproses oleh admin
                    </div>

                </div>

            </div>

            <div class="pending-number">

                <?= number_format(
                    $setoranPending,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

        </div>


        <!-- PENARIKAN -->

        <div class="pending-card">

            <div class="pending-left">

                <div class="pending-icon">
                    💳
                </div>

                <div>

                    <div class="pending-title">
                        Penarikan Pending
                    </div>

                    <div class="pending-description">
                        Menunggu diproses
                    </div>

                </div>

            </div>

            <div class="pending-number">

                <?= number_format(
                    $penarikanPending,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

        </div>

    </div>


    <!-- ==================================================
         TRANSAKSI TERBARU
         ================================================== -->

    <div class="transaction-card">


        <div class="transaction-header">

            <div class="transaction-title">

                <h2>
                    Transaksi Setor Terbaru
                </h2>

                <p>
                    Lima transaksi setor terbaru dari nasabah.
                </p>

            </div>


            <a
                href="transaksi-setor/index.php"
                class="btn-all"
            >
                Lihat Semua →
            </a>

        </div>


        <?php if (empty($transaksiTerbaru)): ?>


            <div class="empty-state">

                <div class="empty-icon">
                    ♻️
                </div>

                <p>
                    Belum ada transaksi setor.
                </p>

            </div>


        <?php else: ?>


            <div class="table-wrapper">

                <table class="transaction-table">

                    <thead>

                        <tr>

                            <th>
                                Nasabah
                            </th>

                            <th>
                                Jenis Sampah
                            </th>

                            <th>
                                Berat
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Tanggal
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach (
                        $transaksiTerbaru
                        as $transaksi
                    ): ?>


                        <?php

                        $status = strtolower(
                            $transaksi['status'] ?? ''
                        );

                        $statusClass = 'status-menunggu';

                        if ($status === 'diterima') {

                            $statusClass = 'status-diterima';

                        } elseif ($status === 'ditolak') {

                            $statusClass = 'status-ditolak';

                        }

                        ?>


                        <tr>


                            <td>

                                <div class="nasabah-name">

                                    <?= htmlspecialchars(
                                        $transaksi[
                                            'nama_nasabah'
                                        ]
                                    ) ?>

                                </div>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $transaksi[
                                        'nama_sampah'
                                    ]
                                ) ?>

                            </td>


                            <td>

                                <?= number_format(
                                    (float)
                                    $transaksi['berat'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>

                                kg

                            </td>


                            <td>

                                <span class="total-value">

                                    Rp
                                    <?= number_format(
                                        (float)
                                        $transaksi[
                                            'total_harga'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <span
                                    class="status-badge
                                    <?= $statusClass ?>"
                                >

                                    <?= ucfirst(
                                        htmlspecialchars(
                                            $status
                                        )
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <span class="date-value">

                                    <?= date(
                                        'd-m-Y H:i',
                                        strtotime(
                                            $transaksi[
                                                'created_at'
                                            ]
                                        )
                                    ) ?>

                                </span>

                            </td>


                        </tr>


                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


        <?php endif; ?>

    </div>


    <!-- ==================================================
         QUICK ACTION
         ================================================== -->

    <div class="quick-section">


        <div class="quick-section-title">

            <h2>
                Akses Cepat
            </h2>

            <p>
                Kelola transaksi Bank Sampah dengan cepat.
            </p>

        </div>


        <div class="quick-grid">


            <!-- TRANSAKSI SETOR -->

            <a
                href="transaksi-setor/index.php"
                class="quick-card"
            >

                <div class="quick-icon">
                    ♻️
                </div>

                <div class="quick-content">

                    <strong>
                        Kelola Transaksi Setor
                    </strong>

                    <span>
                        Lihat dan proses setoran nasabah
                    </span>

                </div>

                <div class="quick-arrow">
                    →
                </div>

            </a>


            <!-- PENARIKAN -->

            <a
                href="penarikan/index.php"
                class="quick-card"
            >

                <div class="quick-icon">
                    💸
                </div>

                <div class="quick-content">

                    <strong>
                        Kelola Penarikan
                    </strong>

                    <span>
                        Kelola pengajuan penarikan saldo
                    </span>

                </div>

                <div class="quick-arrow">
                    →
                </div>

            </a>


        </div>

    </div>


</div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>