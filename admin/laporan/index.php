```php
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Laporan';


// ======================================================
// FILTER
// ======================================================

$status_filter = $_GET['status'] ?? '';
$tanggal_dari = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';


// ======================================================
// VALIDASI STATUS
// ======================================================

$status_valid = [
    '',
    'menunggu',
    'diterima',
    'ditolak'
];

if (!in_array($status_filter, $status_valid, true)) {
    $status_filter = '';
}


// ======================================================
// VARIABEL DATA
// ======================================================

$error = '';

$transaksi_setor = [];
$transaksi_penarikan = [];

$total_nasabah = 0;

$total_setoran = 0;
$total_nilai_setoran = 0;

$total_penarikan = 0;
$total_nilai_penarikan = 0;


// ======================================================
// FILTER SETORAN
// ======================================================

$where_setor = [];
$params_setor = [];

if ($status_filter !== '') {
    $where_setor[] = 's.status = :status';
    $params_setor[':status'] = $status_filter;
}

if ($tanggal_dari !== '') {
    $where_setor[] = 'DATE(s.created_at) >= :tanggal_dari';
    $params_setor[':tanggal_dari'] = $tanggal_dari;
}

if ($tanggal_sampai !== '') {
    $where_setor[] = 'DATE(s.created_at) <= :tanggal_sampai';
    $params_setor[':tanggal_sampai'] = $tanggal_sampai;
}


// ======================================================
// FILTER PENARIKAN
// ======================================================

$where_penarikan = [];
$params_penarikan = [];

if ($status_filter !== '') {

    /*
     * Filter laporan menggunakan "menunggu".
     * Database penarikan menggunakan "pending".
     */
    if ($status_filter === 'menunggu') {

        $where_penarikan[] = 'p.status = :status_penarikan';

        $params_penarikan[':status_penarikan'] = 'pending';

    } else {

        $where_penarikan[] = 'p.status = :status_penarikan';

        $params_penarikan[':status_penarikan'] = $status_filter;
    }
}

if ($tanggal_dari !== '') {
    $where_penarikan[] =
        'DATE(p.tanggal_pengajuan) >= :tanggal_dari_penarikan';

    $params_penarikan[':tanggal_dari_penarikan'] = $tanggal_dari;
}

if ($tanggal_sampai !== '') {
    $where_penarikan[] =
        'DATE(p.tanggal_pengajuan) <= :tanggal_sampai_penarikan';

    $params_penarikan[':tanggal_sampai_penarikan'] = $tanggal_sampai;
}


// ======================================================
// AMBIL DATA
// ======================================================

try {

    // ==================================================
    // TOTAL NASABAH
    // ==================================================

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM users
        WHERE role = 'nasabah'
    ");

    $total_nasabah = (int) $stmt->fetchColumn();


    // ==================================================
    // TRANSAKSI SETOR
    // ==================================================

    $query_setor = "
        SELECT
            s.id,
            s.created_at,
            s.berat,
            s.harga_per_kg,
            s.total_harga,
            s.status,
            users.nama AS nama_nasabah,
            jenis_sampah.nama_sampah
        FROM setoran s
        INNER JOIN users
            ON s.user_id = users.id
        INNER JOIN jenis_sampah
            ON s.jenis_sampah_id = jenis_sampah.id
    ";

    if (!empty($where_setor)) {
        $query_setor .=
            " WHERE " . implode(' AND ', $where_setor);
    }

    $query_setor .= " ORDER BY s.created_at DESC";

    $stmt_setor = $pdo->prepare($query_setor);
    $stmt_setor->execute($params_setor);

    $transaksi_setor =
        $stmt_setor->fetchAll(PDO::FETCH_ASSOC);


    // ==================================================
    // TRANSAKSI PENARIKAN
    // ==================================================

    $query_penarikan = "
        SELECT
            p.id,
            p.kode_penarikan,
            p.jumlah,
            p.metode,
            p.nomor_tujuan,
            p.status,
            p.tanggal_pengajuan,
            n.nama AS nama_nasabah
        FROM penarikan p
        INNER JOIN nasabah n
            ON p.nasabah_id = n.id
    ";

    if (!empty($where_penarikan)) {
        $query_penarikan .=
            " WHERE " . implode(' AND ', $where_penarikan);
    }

    $query_penarikan .=
        " ORDER BY p.tanggal_pengajuan DESC";

    $stmt_penarikan = $pdo->prepare($query_penarikan);
    $stmt_penarikan->execute($params_penarikan);

    $transaksi_penarikan =
        $stmt_penarikan->fetchAll(PDO::FETCH_ASSOC);


    // ==================================================
    // REKAP SETORAN
    // ==================================================

    $total_setoran = count($transaksi_setor);

    foreach ($transaksi_setor as $item) {

        if ($item['status'] === 'diterima') {

            $total_nilai_setoran +=
                (float) $item['total_harga'];
        }
    }


    // ==================================================
    // REKAP PENARIKAN
    // ==================================================

    $total_penarikan = count($transaksi_penarikan);

    foreach ($transaksi_penarikan as $item) {

        if ($item['status'] === 'diterima') {

            $total_nilai_penarikan +=
                (float) $item['jumlah'];
        }
    }

} catch (PDOException $e) {

    $error = 'Gagal mengambil data laporan.';
}


// ======================================================
// HEADER
// ======================================================

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

?>


<style>

/* ======================================================
   LAPORAN
====================================================== */

.report-page {
    padding: 28px 30px 50px;
}


/* ======================================================
   TOPBAR
====================================================== */

.report-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 28px;
}

.report-title h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 750;
    color: #0f172a;
}

.report-title p {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 14px;
}

.report-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.report-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #166534;
    color: white;
    font-weight: 700;
    font-size: 17px;
}

.report-user-name {
    font-weight: 700;
    color: #0f172a;
}

.report-user-role {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}


/* ======================================================
   ERROR
====================================================== */

.report-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
}


/* ======================================================
   FILTER CARD
====================================================== */

.filter-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 22px;
}

.filter-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.filter-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #ecfdf5;
    color: #166534;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.filter-title h2 {
    margin: 0;
    font-size: 17px;
    color: #0f172a;
}

.filter-title p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 13px;
}

.filter-form {
    display: grid;
    grid-template-columns:
        repeat(3, minmax(180px, 1fr)) auto;
    gap: 15px;
    align-items: end;
}

.filter-group label {
    display: block;
    margin-bottom: 7px;
    font-size: 13px;
    font-weight: 700;
    color: #334155;
}

.filter-input,
.filter-select {
    width: 100%;
    height: 44px;
    padding: 0 13px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    color: #334155;
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
    transition: .2s;
}

.filter-input:focus,
.filter-select:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, .10);
}

.filter-actions {
    display: flex;
    gap: 8px;
}

.btn-filter,
.btn-reset,
.btn-print {
    height: 44px;
    padding: 0 17px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    box-sizing: border-box;
}

.btn-filter {
    border: none;
    background: #166534;
    color: white;
}

.btn-filter:hover {
    background: #14532d;
}

.btn-reset {
    background: #f1f5f9;
    color: #475569;
}

.btn-reset:hover {
    background: #e2e8f0;
}


/* ======================================================
   PRINT
====================================================== */

.print-area {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
}

.btn-print {
    background: #0f172a;
    color: white;
}

.btn-print:hover {
    background: #1e293b;
}


/* ======================================================
   STATISTIC CARDS
====================================================== */

.report-stats {
    display: grid;
    grid-template-columns:
        repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.stat-card {
    position: relative;
    overflow: hidden;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
}

.stat-card::after {
    content: "";
    position: absolute;
    width: 80px;
    height: 80px;
    right: -25px;
    bottom: -25px;
    border-radius: 50%;
    background: rgba(22, 163, 74, .06);
}

.stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
}

.icon-green {
    background: #dcfce7;
}

.icon-blue {
    background: #dbeafe;
}

.icon-emerald {
    background: #d1fae5;
}

.icon-red {
    background: #fee2e2;
}

.stat-label {
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
}

.stat-value {
    font-size: 25px;
    font-weight: 800;
    line-height: 1.2;
    color: #0f172a;
}

.stat-sub {
    margin-top: 7px;
    font-size: 12px;
    color: #94a3b8;
}


/* ======================================================
   REPORT CARD
====================================================== */

.report-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 22px;
    margin-bottom: 22px;
    box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
}

.report-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 20px;
}

.report-heading {
    display: flex;
    align-items: center;
    gap: 11px;
}

.report-heading-icon {
    width: 40px;
    height: 40px;
    border-radius: 11px;
    background: #ecfdf5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #166534;
}

.report-heading h2 {
    margin: 0;
    font-size: 17px;
    color: #0f172a;
}

.report-heading p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #64748b;
}

.report-count {
    background: #f1f5f9;
    color: #475569;
    border-radius: 20px;
    padding: 6px 11px;
    font-size: 12px;
    font-weight: 700;
}


/* ======================================================
   TABLE
====================================================== */

.table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 13px;
}

.report-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
}

.report-table th {
    padding: 13px 14px;
    text-align: left;
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-weight: 800;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.report-table td {
    padding: 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 13px;
    vertical-align: middle;
}

.report-table tbody tr:last-child td {
    border-bottom: none;
}

.report-table tbody tr:hover {
    background: #f8fafc;
}

.table-number {
    color: #94a3b8;
    font-weight: 700;
}

.table-name {
    font-weight: 700;
    color: #0f172a;
}

.table-code {
    font-family: monospace;
    font-weight: 700;
    color: #166534;
    background: #f0fdf4;
    padding: 5px 8px;
    border-radius: 6px;
}

.money {
    font-weight: 750;
    color: #166534;
    white-space: nowrap;
}

.nowrap {
    white-space: nowrap;
}


/* ======================================================
   STATUS
====================================================== */

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.status-badge::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.status-menunggu {
    background: #fef3c7;
    color: #92400e;
}

.status-menunggu::before {
    background: #f59e0b;
}

.status-diterima {
    background: #dcfce7;
    color: #166534;
}

.status-diterima::before {
    background: #16a34a;
}

.status-ditolak {
    background: #fee2e2;
    color: #b91c1c;
}

.status-ditolak::before {
    background: #ef4444;
}


/* ======================================================
   EMPTY STATE
====================================================== */

.empty-state {
    padding: 45px 20px;
    text-align: center;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px dashed #cbd5e1;
}

.empty-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.empty-state strong {
    display: block;
    color: #334155;
    font-size: 14px;
}

.empty-state p {
    margin: 5px 0 0;
    color: #94a3b8;
    font-size: 12px;
}


/* ======================================================
   RESPONSIVE
====================================================== */

@media (max-width: 1100px) {

    .report-stats {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .filter-form {
        grid-template-columns:
            repeat(2, minmax(180px, 1fr));
    }

    .filter-actions {
        grid-column: span 2;
    }

}

@media (max-width: 700px) {

    .report-page {
        padding: 20px 15px 35px;
    }

    .report-topbar {
        align-items: flex-start;
    }

    .report-title h1 {
        font-size: 23px;
    }

    .report-user {
        display: none;
    }

    .filter-form {
        grid-template-columns: 1fr;
    }

    .filter-actions {
        grid-column: auto;
    }

    .filter-actions a,
    .filter-actions button {
        flex: 1;
    }

    .report-stats {
        grid-template-columns: 1fr;
    }

    .report-card {
        padding: 16px;
    }

    .report-card-header {
        align-items: flex-start;
    }

    .print-area {
        justify-content: stretch;
    }

    .btn-print {
        width: 100%;
    }
}

</style>


<main class="main-content">

<div class="report-page">


    <!-- ==================================================
         TOPBAR
    ================================================== -->

    <div class="report-topbar">

        <div class="report-title">

            <h1>Laporan</h1>

            <p>
                Pantau dan kelola aktivitas transaksi Bank Sampah.
            </p>

        </div>


        <div class="report-user">

            <div class="report-avatar">

                <?= strtoupper(
                    substr(
                        $_SESSION['nama'] ?? 'A',
                        0,
                        1
                    )
                ) ?>

            </div>

            <div>

                <div class="report-user-name">

                    <?= htmlspecialchars(
                        $_SESSION['nama']
                        ?? 'Administrator'
                    ) ?>

                </div>

                <div class="report-user-role">
                    Administrator
                </div>

            </div>

        </div>

    </div>


    <!-- ==================================================
         ERROR
    ================================================== -->

    <?php if ($error !== ''): ?>

        <div class="report-error">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- ==================================================
         FILTER
    ================================================== -->

    <div class="filter-card">

        <div class="filter-header">

            <div class="filter-title">

                <div class="filter-icon">
                    🔎
                </div>

                <div>

                    <h2>Filter Laporan</h2>

                    <p>
                        Pilih periode dan status transaksi.
                    </p>

                </div>

            </div>

        </div>


        <form
            method="GET"
            action=""
            class="filter-form"
        >

            <div class="filter-group">

                <label for="tanggal_dari">
                    Tanggal Dari
                </label>

                <input
                    class="filter-input"
                    type="date"
                    id="tanggal_dari"
                    name="tanggal_dari"
                    value="<?= htmlspecialchars($tanggal_dari) ?>"
                >

            </div>


            <div class="filter-group">

                <label for="tanggal_sampai">
                    Tanggal Sampai
                </label>

                <input
                    class="filter-input"
                    type="date"
                    id="tanggal_sampai"
                    name="tanggal_sampai"
                    value="<?= htmlspecialchars($tanggal_sampai) ?>"
                >

            </div>


            <div class="filter-group">

                <label for="status">
                    Status
                </label>

                <select
                    class="filter-select"
                    id="status"
                    name="status"
                >

                    <option value="">
                        Semua Status
                    </option>

                    <option
                        value="menunggu"
                        <?= $status_filter === 'menunggu'
                            ? 'selected'
                            : '' ?>
                    >
                        Menunggu
                    </option>

                    <option
                        value="diterima"
                        <?= $status_filter === 'diterima'
                            ? 'selected'
                            : '' ?>
                    >
                        Diterima
                    </option>

                    <option
                        value="ditolak"
                        <?= $status_filter === 'ditolak'
                            ? 'selected'
                            : '' ?>
                    >
                        Ditolak
                    </option>

                </select>

            </div>


            <div class="filter-actions">

                <button
                    type="submit"
                    class="btn-filter"
                >
                    🔎 Filter
                </button>

                <a
                    href="index.php"
                    class="btn-reset"
                >
                    Reset
                </a>

            </div>

        </form>

    </div>


    <!-- ==================================================
         CETAK
    ================================================== -->

    <div class="print-area">

        <a
            href="cetak.php?<?= http_build_query($_GET) ?>"
            target="_blank"
            class="btn-print"
        >
            🖨️ Cetak Laporan
        </a>

    </div>


    <!-- ==================================================
         STATISTIK
    ================================================== -->

    <div class="report-stats">


        <!-- NASABAH -->

        <div class="stat-card">

            <div class="stat-top">

                <div class="stat-icon icon-green">
                    👥
                </div>

                <span class="stat-label">
                    NASABAH
                </span>

            </div>

            <div class="stat-value">
                <?= number_format(
                    $total_nasabah,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div class="stat-sub">
                Total nasabah terdaftar
            </div>

        </div>


        <!-- SETORAN -->

        <div class="stat-card">

            <div class="stat-top">

                <div class="stat-icon icon-blue">
                    ♻️
                </div>

                <span class="stat-label">
                    SETORAN
                </span>

            </div>

            <div class="stat-value">
                <?= number_format(
                    $total_setoran,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div class="stat-sub">
                Transaksi sesuai filter
            </div>

        </div>


        <!-- NILAI SETORAN -->

        <div class="stat-card">

            <div class="stat-top">

                <div class="stat-icon icon-emerald">
                    💰
                </div>

                <span class="stat-label">
                    NILAI SETORAN
                </span>

            </div>

            <div class="stat-value">
                Rp <?= number_format(
                    $total_nilai_setoran,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div class="stat-sub">
                Total transaksi diterima
            </div>

        </div>


        <!-- PENARIKAN -->

        <div class="stat-card">

            <div class="stat-top">

                <div class="stat-icon icon-red">
                    💸
                </div>

                <span class="stat-label">
                    PENARIKAN
                </span>

            </div>

            <div class="stat-value">
                Rp <?= number_format(
                    $total_nilai_penarikan,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

            <div class="stat-sub">
                Total penarikan diterima
            </div>

        </div>

    </div>


    <!-- ==================================================
         TRANSAKSI SETOR
    ================================================== -->

    <div class="report-card">

        <div class="report-card-header">

            <div class="report-heading">

                <div class="report-heading-icon">
                    ♻️
                </div>

                <div>

                    <h2>
                        Laporan Transaksi Setor
                    </h2>

                    <p>
                        Daftar transaksi setor berdasarkan filter.
                    </p>

                </div>

            </div>


            <div class="report-count">

                <?= number_format(
                    count($transaksi_setor),
                    0,
                    ',',
                    '.'
                ) ?>

                transaksi

            </div>

        </div>


        <?php if (empty($transaksi_setor)): ?>

            <div class="empty-state">

                <div class="empty-icon">
                    📭
                </div>

                <strong>
                    Tidak ada transaksi setor
                </strong>

                <p>
                    Tidak ditemukan data sesuai filter yang dipilih.
                </p>

            </div>

        <?php else: ?>

            <div class="table-wrapper">

                <table class="report-table">

                    <thead>

                        <tr>

                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nasabah</th>
                            <th>Jenis Sampah</th>
                            <th>Berat</th>
                            <th>Harga / Kg</th>
                            <th>Total</th>
                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach (
                            $transaksi_setor
                            as $index => $item
                        ): ?>

                            <tr>

                                <td class="table-number">
                                    <?= $index + 1 ?>
                                </td>


                                <td class="nowrap">

                                    <?= date(
                                        'd M Y, H:i',
                                        strtotime(
                                            $item['created_at']
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <span class="table-name">

                                        <?= htmlspecialchars(
                                            $item['nama_nasabah']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $item['nama_sampah']
                                    ) ?>

                                </td>


                                <td class="nowrap">

                                    <?= number_format(
                                        $item['berat'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>

                                    kg

                                </td>


                                <td class="nowrap">

                                    Rp
                                    <?= number_format(
                                        $item['harga_per_kg'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>


                                <td class="money">

                                    Rp
                                    <?= number_format(
                                        $item['total_harga'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>


                                <td>

                                    <?php if (
                                        $item['status']
                                        === 'menunggu'
                                    ): ?>

                                        <span class="status-badge status-menunggu">
                                            Menunggu
                                        </span>

                                    <?php elseif (
                                        $item['status']
                                        === 'diterima'
                                    ): ?>

                                        <span class="status-badge status-diterima">
                                            Diterima
                                        </span>

                                    <?php elseif (
                                        $item['status']
                                        === 'ditolak'
                                    ): ?>

                                        <span class="status-badge status-ditolak">
                                            Ditolak
                                        </span>

                                    <?php else: ?>

                                        <?= htmlspecialchars(
                                            $item['status']
                                        ) ?>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>


    <!-- ==================================================
         PENARIKAN
    ================================================== -->

    <div class="report-card">

        <div class="report-card-header">

            <div class="report-heading">

                <div class="report-heading-icon">
                    💸
                </div>

                <div>

                    <h2>
                        Laporan Penarikan
                    </h2>

                    <p>
                        Daftar pengajuan penarikan berdasarkan filter.
                    </p>

                </div>

            </div>


            <div class="report-count">

                <?= number_format(
                    count($transaksi_penarikan),
                    0,
                    ',',
                    '.'
                ) ?>

                transaksi

            </div>

        </div>


        <?php if (empty($transaksi_penarikan)): ?>

            <div class="empty-state">

                <div class="empty-icon">
                    📭
                </div>

                <strong>
                    Tidak ada transaksi penarikan
                </strong>

                <p>
                    Tidak ditemukan data sesuai filter yang dipilih.
                </p>

            </div>

        <?php else: ?>

            <div class="table-wrapper">

                <table class="report-table">

                    <thead>

                        <tr>

                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Kode</th>
                            <th>Nasabah</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Nomor Tujuan</th>
                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach (
                            $transaksi_penarikan
                            as $index => $row
                        ): ?>

                            <tr>

                                <td class="table-number">
                                    <?= $index + 1 ?>
                                </td>


                                <td class="nowrap">

                                    <?= date(
                                        'd M Y, H:i',
                                        strtotime(
                                            $row['tanggal_pengajuan']
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <span class="table-code">

                                        <?= htmlspecialchars(
                                            $row['kode_penarikan']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="table-name">

                                        <?= htmlspecialchars(
                                            $row['nama_nasabah']
                                        ) ?>

                                    </span>

                                </td>


                                <td class="money">

                                    Rp
                                    <?= number_format(
                                        $row['jumlah'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>


                                <td>

                                    <?= ucfirst(
                                        htmlspecialchars(
                                            $row['metode']
                                        )
                                    ) ?>

                                </td>


                                <td class="nowrap">

                                    <?= htmlspecialchars(
                                        $row['nomor_tujuan']
                                    ) ?>

                                </td>


                                <td>

                                    <?php if (
                                        $row['status']
                                        === 'pending'
                                    ): ?>

                                        <span class="status-badge status-menunggu">
                                            Menunggu
                                        </span>

                                    <?php elseif (
                                        $row['status']
                                        === 'diterima'
                                    ): ?>

                                        <span class="status-badge status-diterima">
                                            Diterima
                                        </span>

                                    <?php elseif (
                                        $row['status']
                                        === 'ditolak'
                                    ): ?>

                                        <span class="status-badge status-ditolak">
                                            Ditolak
                                        </span>

                                    <?php else: ?>

                                        <?= htmlspecialchars(
                                            $row['status']
                                        ) ?>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>


</div>

</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>
```
