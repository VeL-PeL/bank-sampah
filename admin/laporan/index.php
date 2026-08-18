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


// STATUS

if ($status_filter !== '') {

    $where_setor[] = 's.status = :status';

    $params_setor[':status'] = $status_filter;
}


// TANGGAL DARI

if ($tanggal_dari !== '') {

    $where_setor[] = '
        DATE(s.created_at) >= :tanggal_dari
    ';

    $params_setor[':tanggal_dari'] = $tanggal_dari;
}


// TANGGAL SAMPAI

if ($tanggal_sampai !== '') {

    $where_setor[] = '
        DATE(s.created_at) <= :tanggal_sampai
    ';

    $params_setor[':tanggal_sampai'] = $tanggal_sampai;
}


// ======================================================
// FILTER PENARIKAN
// ======================================================

$where_penarikan = [];
$params_penarikan = [];


// STATUS

if ($status_filter !== '') {

    $where_penarikan[] = 'p.status = :status_penarikan';

    $params_penarikan[':status_penarikan'] =
        $status_filter;
}


// TANGGAL DARI

if ($tanggal_dari !== '') {

    $where_penarikan[] = '
        DATE(p.tanggal_pengajuan) >= :tanggal_dari_penarikan
    ';

    $params_penarikan[':tanggal_dari_penarikan'] =
        $tanggal_dari;
}


// TANGGAL SAMPAI

if ($tanggal_sampai !== '') {

    $where_penarikan[] = '
        DATE(p.tanggal_pengajuan) <= :tanggal_sampai_penarikan
    ';

    $params_penarikan[':tanggal_sampai_penarikan'] =
        $tanggal_sampai;
}


// ======================================================
// AMBIL SEMUA DATA
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
    //
    // PENTING:
    // Halaman transaksi setor menggunakan:
    //
    // s.user_id = users.id
    //
    // Jadi laporan juga harus menggunakan user_id.
    //
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

        $query_setor .= "
            WHERE "
            . implode(
                ' AND ',
                $where_setor
            );
    }


    $query_setor .= "
        ORDER BY s.created_at DESC
    ";


    $stmt_setor = $pdo->prepare(
        $query_setor
    );

    $stmt_setor->execute(
        $params_setor
    );


    $transaksi_setor =
        $stmt_setor->fetchAll(
            PDO::FETCH_ASSOC
        );


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

        $query_penarikan .= "
            WHERE "
            . implode(
                ' AND ',
                $where_penarikan
            );
    }


    $query_penarikan .= "
        ORDER BY p.tanggal_pengajuan DESC
    ";


    $stmt_penarikan = $pdo->prepare(
        $query_penarikan
    );

    $stmt_penarikan->execute(
        $params_penarikan
    );


    $transaksi_penarikan =
        $stmt_penarikan->fetchAll(
            PDO::FETCH_ASSOC
        );


    // ==================================================
    // REKAP SETORAN
    // ==================================================

    $total_setoran =
        count($transaksi_setor);


    foreach ($transaksi_setor as $item) {

        if ($item['status'] === 'diterima') {

            $total_nilai_setoran +=
                (float) $item['total_harga'];
        }
    }


    // ==================================================
    // REKAP PENARIKAN
    // ==================================================

    $total_penarikan =
        count($transaksi_penarikan);


    foreach ($transaksi_penarikan as $item) {

        if ($item['status'] === 'diterima') {

            $total_nilai_penarikan +=
                (float) $item['jumlah'];
        }
    }


} catch (PDOException $e) {

    $error =
        'Gagal mengambil data laporan.';

}


// ======================================================
// HEADER
// ======================================================

require_once __DIR__ . '/../../includes/header.php';

require_once __DIR__ . '/../../includes/sidebar.php';

?>


<main class="main-content">


    <!-- ==================================================
         TOPBAR
    ================================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Laporan
                </h1>

                <p>
                    Ringkasan aktivitas Bank Sampah.
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
                            $_SESSION['nama'] ?? 'A',
                            0,
                            1
                        )
                    ) ?>

                </div>


                <div class="user-details">

                    <div class="user-name">

                        <?= htmlspecialchars(
                            $_SESSION['nama']
                            ?? 'Administrator'
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
         CONTENT
    ================================================== -->

    <div style="margin-top: 30px;">


        <?php if ($error !== ''): ?>

            <div style="
                background: #fee2e2;
                color: #b91c1c;
                padding: 15px 20px;
                border-radius: 10px;
                margin-bottom: 20px;
            ">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             FILTER
        ================================================== -->

        <div style="
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,.06);
            margin-bottom: 20px;
        ">

            <h2 style="
                margin-top: 0;
                margin-bottom: 5px;
            ">
                Filter Laporan
            </h2>

            <p style="
                color: #64748b;
                margin-bottom: 20px;
            ">
                Gunakan filter untuk menampilkan
                transaksi berdasarkan tanggal dan status.
            </p>


            <form
                method="GET"
                action=""
                style="
                    display: grid;
                    grid-template-columns:
                        repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    align-items: end;
                "
            >


                <!-- TANGGAL DARI -->

                <div>

                    <label
                        for="tanggal_dari"
                        style="
                            display: block;
                            margin-bottom: 7px;
                            font-weight: 600;
                        "
                    >
                        Tanggal Dari
                    </label>


                    <input
                        type="date"
                        id="tanggal_dari"
                        name="tanggal_dari"
                        value="<?= htmlspecialchars(
                            $tanggal_dari
                        ) ?>"
                        style="
                            width: 100%;
                            padding: 10px;
                            border: 1px solid #cbd5e1;
                            border-radius: 8px;
                            box-sizing: border-box;
                        "
                    >

                </div>


                <!-- TANGGAL SAMPAI -->

                <div>

                    <label
                        for="tanggal_sampai"
                        style="
                            display: block;
                            margin-bottom: 7px;
                            font-weight: 600;
                        "
                    >
                        Tanggal Sampai
                    </label>


                    <input
                        type="date"
                        id="tanggal_sampai"
                        name="tanggal_sampai"
                        value="<?= htmlspecialchars(
                            $tanggal_sampai
                        ) ?>"
                        style="
                            width: 100%;
                            padding: 10px;
                            border: 1px solid #cbd5e1;
                            border-radius: 8px;
                            box-sizing: border-box;
                        "
                    >

                </div>


                <!-- STATUS -->

                <div>

                    <label
                        for="status"
                        style="
                            display: block;
                            margin-bottom: 7px;
                            font-weight: 600;
                        "
                    >
                        Status
                    </label>


                    <select
                        id="status"
                        name="status"
                        style="
                            width: 100%;
                            padding: 10px;
                            border: 1px solid #cbd5e1;
                            border-radius: 8px;
                            background: white;
                            box-sizing: border-box;
                        "
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


                <!-- TOMBOL -->

                <div style="
                    display: flex;
                    gap: 10px;
                ">

                    <button
                        type="submit"
                        style="
                            padding: 10px 18px;
                            border: none;
                            border-radius: 8px;
                            background: #166534;
                            color: white;
                            cursor: pointer;
                            font-weight: 600;
                        "
                    >
                        🔎 Filter
                    </button>


                    <a
                        href="index.php"
                        style="
                            display: inline-flex;
                            align-items: center;
                            padding: 10px 18px;
                            border-radius: 8px;
                            background: #e2e8f0;
                            color: #334155;
                            text-decoration: none;
                            font-weight: 600;
                        "
                    >
                        Reset
                    </a>

                </div>

            </form>

        </div>



        <!-- ==================================================
             CETAK
        ================================================== -->

        <div style="margin-bottom: 20px;">

            <a
                href="cetak.php?<?= http_build_query($_GET) ?>"
                target="_blank"
                style="
                    display: inline-block;
                    padding: 10px 18px;
                    background: #166534;
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: bold;
                "
            >
                🖨️ Cetak Laporan
            </a>

        </div>



        <!-- ==================================================
             KARTU REKAP
        ================================================== -->

        <div style="
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin: 25px 0;
        ">


            <!-- NASABAH -->

            <div style="
                background: white;
                padding: 22px;
                border-radius: 15px;
                box-shadow: 0 8px 25px rgba(0,0,0,.06);
                border-left: 5px solid #166534;
            ">

                <div style="
                    color: #6b7280;
                    font-size: 14px;
                    margin-bottom: 8px;
                ">
                    👥 Total Nasabah
                </div>

                <div style="
                    font-size: 28px;
                    font-weight: bold;
                    color: #166534;
                ">
                    <?= number_format(
                        $total_nasabah,
                        0,
                        ',',
                        '.'
                    ) ?>
                </div>

            </div>


            <!-- TRANSAKSI SETOR -->

            <div style="
                background: white;
                padding: 22px;
                border-radius: 15px;
                box-shadow: 0 8px 25px rgba(0,0,0,.06);
                border-left: 5px solid #2563eb;
            ">

                <div style="
                    color: #6b7280;
                    font-size: 14px;
                    margin-bottom: 8px;
                ">
                    ♻️ Transaksi Setor
                </div>

                <div style="
                    font-size: 28px;
                    font-weight: bold;
                    color: #2563eb;
                ">
                    <?= number_format(
                        $total_setoran,
                        0,
                        ',',
                        '.'
                    ) ?>
                </div>

            </div>


            <!-- NILAI SETORAN -->

            <div style="
                background: white;
                padding: 22px;
                border-radius: 15px;
                box-shadow: 0 8px 25px rgba(0,0,0,.06);
                border-left: 5px solid #16a34a;
            ">

                <div style="
                    color: #6b7280;
                    font-size: 14px;
                    margin-bottom: 8px;
                ">
                    💰 Total Nilai Setoran Diterima
                </div>

                <div style="
                    font-size: 24px;
                    font-weight: bold;
                    color: #16a34a;
                ">
                    Rp
                    <?= number_format(
                        $total_nilai_setoran,
                        0,
                        ',',
                        '.'
                    ) ?>
                </div>

            </div>


            <!-- PENARIKAN -->

            <div style="
                background: white;
                padding: 22px;
                border-radius: 15px;
                box-shadow: 0 8px 25px rgba(0,0,0,.06);
                border-left: 5px solid #dc2626;
            ">

                <div style="
                    color: #6b7280;
                    font-size: 14px;
                    margin-bottom: 8px;
                ">
                    💸 Penarikan Diterima
                </div>

                <div style="
                    font-size: 24px;
                    font-weight: bold;
                    color: #dc2626;
                ">
                    Rp
                    <?= number_format(
                        $total_nilai_penarikan,
                        0,
                        ',',
                        '.'
                    ) ?>
                </div>

            </div>

        </div>



        <!-- ==================================================
             LAPORAN TRANSAKSI SETOR
        ================================================== -->

        <div style="
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,.06);
            margin-top: 25px;
        ">

            <h2 style="
                margin-top: 0;
                color: #166534;
            ">
                Laporan Transaksi Setor
            </h2>

            <p style="
                color: #6b7280;
                margin-bottom: 20px;
            ">
                Daftar transaksi setor sesuai filter.
            </p>


            <?php if (empty($transaksi_setor)): ?>

                <div style="
                    padding: 30px;
                    text-align: center;
                    color: #6b7280;
                    background: #f9fafb;
                    border-radius: 10px;
                ">

                    📭

                    <br><br>

                    Tidak ada transaksi setor
                    sesuai filter yang dipilih.

                </div>


            <?php else: ?>

                <div style="
                    overflow-x: auto;
                ">

                    <table style="
                        width: 100%;
                        border-collapse: collapse;
                    ">

                        <thead>

                            <tr>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    No
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Tanggal
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Nasabah
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Jenis Sampah
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Berat
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Harga/kg
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Total
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $transaksi_setor
                                as $index => $item
                            ): ?>

                                <tr>

                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                    ">
                                        <?= $index + 1 ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        white-space: nowrap;
                                    ">
                                        <?= date(
                                            'd-m-Y H:i',
                                            strtotime(
                                                $item['created_at']
                                            )
                                        ) ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                    ">
                                        <?= htmlspecialchars(
                                            $item['nama_nasabah']
                                        ) ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                    ">
                                        <?= htmlspecialchars(
                                            $item['nama_sampah']
                                        ) ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        white-space: nowrap;
                                    ">
                                        <?= number_format(
                                            $item['berat'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>
                                        kg
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        white-space: nowrap;
                                    ">
                                        Rp
                                        <?= number_format(
                                            $item['harga_per_kg'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        white-space: nowrap;
                                    ">
                                        <strong>
                                            Rp
                                            <?= number_format(
                                                $item['total_harga'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>
                                        </strong>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                    ">

                                        <?php if (
                                            $item['status']
                                            === 'menunggu'
                                        ): ?>

                                            <span style="
                                                display: inline-block;
                                                padding: 6px 10px;
                                                border-radius: 20px;
                                                background: #fef3c7;
                                                color: #92400e;
                                                font-size: 13px;
                                                font-weight: bold;
                                            ">
                                                Menunggu
                                            </span>


                                        <?php elseif (
                                            $item['status']
                                            === 'diterima'
                                        ): ?>

                                            <span style="
                                                display: inline-block;
                                                padding: 6px 10px;
                                                border-radius: 20px;
                                                background: #dcfce7;
                                                color: #166534;
                                                font-size: 13px;
                                                font-weight: bold;
                                            ">
                                                Diterima
                                            </span>


                                        <?php elseif (
                                            $item['status']
                                            === 'ditolak'
                                        ): ?>

                                            <span style="
                                                display: inline-block;
                                                padding: 6px 10px;
                                                border-radius: 20px;
                                                background: #fee2e2;
                                                color: #b91c1c;
                                                font-size: 13px;
                                                font-weight: bold;
                                            ">
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
             LAPORAN PENARIKAN
        ================================================== -->

        <div style="
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,.06);
            margin-top: 25px;
        ">

            <h2 style="
                margin-top: 0;
                color: #166534;
            ">
                Laporan Penarikan
            </h2>

            <p style="
                color: #6b7280;
                margin-bottom: 20px;
            ">
                Daftar pengajuan penarikan sesuai filter.
            </p>


            <?php if (empty($transaksi_penarikan)): ?>

                <div style="
                    padding: 30px;
                    text-align: center;
                    color: #6b7280;
                    background: #f9fafb;
                    border-radius: 10px;
                ">

                    📭

                    <br><br>

                    Tidak ada transaksi penarikan
                    sesuai filter yang dipilih.

                </div>


            <?php else: ?>

                <div style="
                    overflow-x: auto;
                ">

                    <table style="
                        width: 100%;
                        border-collapse: collapse;
                        min-width: 900px;
                    ">

                        <thead>

                            <tr>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    No
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Tanggal
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Kode
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Nasabah
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Jumlah
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Metode
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Nomor Tujuan
                                </th>

                                <th style="
                                    padding: 12px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                ">
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $transaksi_penarikan
                                as $index => $row
                            ): ?>

                                <tr>

                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                    ">
                                        <?= $index + 1 ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        white-space: nowrap;
                                    ">
                                        <?= date(
                                            'd-m-Y H:i',
                                            strtotime(
                                                $row['tanggal_pengajuan']
                                            )
                                        ) ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        font-weight: bold;
                                    ">
                                        <?= htmlspecialchars(
                                            $row['kode_penarikan']
                                        ) ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                    ">
                                        <?= htmlspecialchars(
                                            $row['nama_nasabah']
                                        ) ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        font-weight: bold;
                                        color: #166534;
                                        white-space: nowrap;
                                    ">
                                        Rp
                                        <?= number_format(
                                            $row['jumlah'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                    ">
                                        <?= ucfirst(
                                            htmlspecialchars(
                                                $row['metode']
                                            )
                                        ) ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        white-space: nowrap;
                                    ">
                                        <?= htmlspecialchars(
                                            $row['nomor_tujuan']
                                        ) ?>
                                    </td>


                                    <td style="
                                        padding: 12px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                    ">

                                        <?php if (
                                            $row['status']
                                            === 'pending'
                                        ): ?>

                                            <span style="
                                                display: inline-block;
                                                padding: 6px 10px;
                                                border-radius: 20px;
                                                background: #fef3c7;
                                                color: #92400e;
                                                font-size: 13px;
                                                font-weight: bold;
                                            ">
                                                Pending
                                            </span>


                                        <?php elseif (
                                            $row['status']
                                            === 'diterima'
                                        ): ?>

                                            <span style="
                                                display: inline-block;
                                                padding: 6px 10px;
                                                border-radius: 20px;
                                                background: #dcfce7;
                                                color: #166534;
                                                font-size: 13px;
                                                font-weight: bold;
                                            ">
                                                Diterima
                                            </span>


                                        <?php elseif (
                                            $row['status']
                                            === 'ditolak'
                                        ): ?>

                                            <span style="
                                                display: inline-block;
                                                padding: 6px 10px;
                                                border-radius: 20px;
                                                background: #fee2e2;
                                                color: #b91c1c;
                                                font-size: 13px;
                                                font-weight: bold;
                                            ">
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