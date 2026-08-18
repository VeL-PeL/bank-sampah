<?php

require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../config/database.php';

$page_title = 'Dashboard Admin';


// ========================================
// DEFAULT DATA
// ========================================

$totalNasabah = 0;
$totalSetoran = 0;
$totalNilaiSetoran = 0;
$totalPenarikan = 0;
$setoranPending = 0;
$penarikanPending = 0;

$transaksiTerbaru = [];


// ========================================
// TOTAL NASABAH AKTIF
// ========================================

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


// ========================================
// TOTAL TRANSAKSI SETOR
// ========================================

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM setoran
    ");

    $totalSetoran = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    $totalSetoran = 0;

}


// ========================================
// TOTAL NILAI SETORAN
// ========================================

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


// ========================================
// TOTAL PENARIKAN
// ========================================

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


// ========================================
// SETORAN PENDING
// ========================================

try {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM setoran
        WHERE status = 'pending'
    ");

    $setoranPending = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    $setoranPending = 0;

}


// ========================================
// PENARIKAN PENDING
// ========================================

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


// ========================================
// TRANSAKSI SETOR TERBARU
// ========================================

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


// ========================================
// HEADER & SIDEBAR
// ========================================

require_once __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../includes/sidebar.php';

?>


<main class="main-content">


    <!-- ========================================
         TOPBAR
    ======================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Dashboard
                </h1>

                <p>
                    Selamat datang kembali,
                    <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?>!
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
                    <?= $setoranPending + $penarikanPending ?>
                </span>

            </button>


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
         JUDUL
    ======================================== -->

    <div style="margin-top: 30px;">

        <h2 style="margin-bottom: 0;">
            Ringkasan Bank Sampah
        </h2>

        <p
            style="
                margin-top: 8px;
                color: #6b7280;
            "
        >
            Pantau aktivitas dan transaksi Bank Sampah dari sini.
        </p>

    </div>


    <!-- ========================================
         STATISTIK
    ======================================== -->

    <div
        style="
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 25px;
        "
    >


        <!-- NASABAH -->

        <div
            style="
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
            "
        >

            <div style="font-size: 32px;">
                👥
            </div>

            <div
                style="
                    color: #6b7280;
                    margin-top: 10px;
                "
            >
                Total Nasabah
            </div>

            <div
                style="
                    margin-top: 5px;
                    font-size: 28px;
                    font-weight: bold;
                    color: #166534;
                "
            >
                <?= number_format(
                    $totalNasabah,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div
                style="
                    color: #9ca3af;
                    font-size: 13px;
                    margin-top: 5px;
                "
            >
                Nasabah aktif
            </div>

        </div>


        <!-- TRANSAKSI SETOR -->

        <div
            style="
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
            "
        >

            <div style="font-size: 32px;">
                ♻️
            </div>

            <div
                style="
                    color: #6b7280;
                    margin-top: 10px;
                "
            >
                Transaksi Setor
            </div>

            <div
                style="
                    margin-top: 5px;
                    font-size: 28px;
                    font-weight: bold;
                    color: #166534;
                "
            >
                <?= number_format(
                    $totalSetoran,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div
                style="
                    color: #9ca3af;
                    font-size: 13px;
                    margin-top: 5px;
                "
            >
                Semua transaksi
            </div>

        </div>


        <!-- NILAI SETORAN -->

        <div
            style="
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
            "
        >

            <div style="font-size: 32px;">
                💰
            </div>

            <div
                style="
                    color: #6b7280;
                    margin-top: 10px;
                "
            >
                Nilai Setoran
            </div>

            <div
                style="
                    margin-top: 5px;
                    font-size: 23px;
                    font-weight: bold;
                    color: #166534;
                "
            >
                Rp <?= number_format(
                    $totalNilaiSetoran,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div
                style="
                    color: #9ca3af;
                    font-size: 13px;
                    margin-top: 5px;
                "
            >
                Setoran diterima
            </div>

        </div>


        <!-- PENARIKAN -->

        <div
            style="
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
            "
        >

            <div style="font-size: 32px;">
                💸
            </div>

            <div
                style="
                    color: #6b7280;
                    margin-top: 10px;
                "
            >
                Total Penarikan
            </div>

            <div
                style="
                    margin-top: 5px;
                    font-size: 23px;
                    font-weight: bold;
                    color: #166534;
                "
            >
                Rp <?= number_format(
                    $totalPenarikan,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div
                style="
                    color: #9ca3af;
                    font-size: 13px;
                    margin-top: 5px;
                "
            >
                Diterima / selesai
            </div>

        </div>


        <!-- SETORAN PENDING -->

        <div
            style="
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
            "
        >

            <div style="font-size: 32px;">
                ⏳
            </div>

            <div
                style="
                    color: #6b7280;
                    margin-top: 10px;
                "
            >
                Setoran Pending
            </div>

            <div
                style="
                    margin-top: 5px;
                    font-size: 28px;
                    font-weight: bold;
                    color: #d97706;
                "
            >
                <?= number_format(
                    $setoranPending,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div
                style="
                    color: #9ca3af;
                    font-size: 13px;
                    margin-top: 5px;
                "
            >
                Menunggu diproses
            </div>

        </div>


        <!-- PENARIKAN PENDING -->

        <div
            style="
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
            "
        >

            <div style="font-size: 32px;">
                💳
            </div>

            <div
                style="
                    color: #6b7280;
                    margin-top: 10px;
                "
            >
                Penarikan Pending
            </div>

            <div
                style="
                    margin-top: 5px;
                    font-size: 28px;
                    font-weight: bold;
                    color: #d97706;
                "
            >
                <?= number_format(
                    $penarikanPending,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div
                style="
                    color: #9ca3af;
                    font-size: 13px;
                    margin-top: 5px;
                "
            >
                Menunggu diproses
            </div>

        </div>

    </div>


    <!-- ========================================
         TRANSAKSI TERBARU
    ======================================== -->

    <div
        style="
            background: white;
            margin-top: 30px;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,.06);
        "
    >

        <div
            style="
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 15px;
                margin-bottom: 20px;
            "
        >

            <div>

                <h2
                    style="
                        margin: 0;
                        color: #166534;
                    "
                >
                    Transaksi Setor Terbaru
                </h2>

                <p
                    style="
                        margin: 6px 0 0;
                        color: #6b7280;
                        font-size: 14px;
                    "
                >
                    Lima transaksi setor terbaru dari nasabah.
                </p>

            </div>


            <a
                href="transaksi-setor/index.php"
                style="
                    padding: 9px 14px;
                    border-radius: 8px;
                    background: #166534;
                    color: white;
                    text-decoration: none;
                    font-size: 13px;
                    font-weight: bold;
                "
            >
                Lihat Semua
            </a>

        </div>


        <?php if (empty($transaksiTerbaru)): ?>

            <div
                style="
                    padding: 30px;
                    text-align: center;
                    color: #6b7280;
                    background: #f9fafb;
                    border-radius: 10px;
                "
            >
                Belum ada transaksi setor.
            </div>

        <?php else: ?>

            <div style="overflow-x: auto;">

                <table
                    style="
                        width: 100%;
                        border-collapse: collapse;
                    "
                >

                    <thead>

                        <tr>

                            <th style="padding:13px;text-align:left;background:#f0fdf4;color:#166534;">
                                Nasabah
                            </th>

                            <th style="padding:13px;text-align:left;background:#f0fdf4;color:#166534;">
                                Jenis Sampah
                            </th>

                            <th style="padding:13px;text-align:left;background:#f0fdf4;color:#166534;">
                                Berat
                            </th>

                            <th style="padding:13px;text-align:left;background:#f0fdf4;color:#166534;">
                                Total
                            </th>

                            <th style="padding:13px;text-align:left;background:#f0fdf4;color:#166534;">
                                Status
                            </th>

                            <th style="padding:13px;text-align:left;background:#f0fdf4;color:#166534;">
                                Tanggal
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($transaksiTerbaru as $transaksi): ?>

                        <?php

                        $status = strtolower(
                            $transaksi['status'] ?? ''
                        );

                        if ($status === 'diterima') {

                            $statusBg = '#dcfce7';
                            $statusColor = '#166534';

                        } elseif ($status === 'ditolak') {

                            $statusBg = '#fee2e2';
                            $statusColor = '#b91c1c';

                        } else {

                            $statusBg = '#fef3c7';
                            $statusColor = '#92400e';

                        }

                        ?>

                        <tr>

                            <td
                                style="
                                    padding:13px;
                                    border-bottom:1px solid #e5e7eb;
                                    font-weight:600;
                                "
                            >
                                <?= htmlspecialchars(
                                    $transaksi['nama_nasabah']
                                ) ?>
                            </td>


                            <td
                                style="
                                    padding:13px;
                                    border-bottom:1px solid #e5e7eb;
                                "
                            >
                                <?= htmlspecialchars(
                                    $transaksi['nama_sampah']
                                ) ?>
                            </td>


                            <td
                                style="
                                    padding:13px;
                                    border-bottom:1px solid #e5e7eb;
                                    white-space:nowrap;
                                "
                            >
                                <?= number_format(
                                    (float) $transaksi['berat'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                                kg
                            </td>


                            <td
                                style="
                                    padding:13px;
                                    border-bottom:1px solid #e5e7eb;
                                    font-weight:600;
                                    white-space:nowrap;
                                "
                            >
                                Rp
                                <?= number_format(
                                    (float) $transaksi['total_harga'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>


                            <td
                                style="
                                    padding:13px;
                                    border-bottom:1px solid #e5e7eb;
                                "
                            >

                                <span
                                    style="
                                        display:inline-block;
                                        padding:6px 10px;
                                        border-radius:20px;
                                        background:<?= $statusBg ?>;
                                        color:<?= $statusColor ?>;
                                        font-size:12px;
                                        font-weight:bold;
                                    "
                                >
                                    <?= ucfirst(
                                        htmlspecialchars($status)
                                    ) ?>
                                </span>

                            </td>


                            <td
                                style="
                                    padding:13px;
                                    border-bottom:1px solid #e5e7eb;
                                    white-space:nowrap;
                                    color:#6b7280;
                                "
                            >
                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime(
                                        $transaksi['created_at']
                                    )
                                ) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>


    <!-- ========================================
         QUICK ACTION
    ======================================== -->

    <div
        style="
            display:grid;
            grid-template-columns:
                repeat(auto-fit,minmax(220px,1fr));
            gap:20px;
            margin-top:25px;
            margin-bottom:30px;
        "
    >

        <a
            href="transaksi-setor/index.php"
            style="
                background:white;
                padding:20px;
                border-radius:12px;
                text-decoration:none;
                color:#166534;
                box-shadow:0 8px 25px rgba(0,0,0,.05);
                font-weight:bold;
            "
        >
            ♻️ Kelola Transaksi Setor
        </a>


        <a
            href="penarikan/index.php"
            style="
                background:white;
                padding:20px;
                border-radius:12px;
                text-decoration:none;
                color:#166534;
                box-shadow:0 8px 25px rgba(0,0,0,.05);
                font-weight:bold;
            "
        >
            💸 Kelola Penarikan
        </a>

    </div>


</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>