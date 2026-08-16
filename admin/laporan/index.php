<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Laporan';


// ========================================
// DATA LAPORAN
// ========================================

$total_setoran = 0;
$total_nilai_setoran = 0;

$total_penarikan = 0;
$total_nilai_penarikan = 0;

$total_nasabah = 0;

$error = '';

$transaksi_setor = [];


// ========================================
// FILTER LAPORAN
// ========================================

$tanggal_dari = $_GET['tanggal_dari'] ?? '';

$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';

$status_filter = $_GET['status'] ?? '';


// ========================================
// HITUNG DATA
// ========================================

try {

    // ========================================
    // JUMLAH NASABAH
    // ========================================

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM nasabah
    ");

    $total_nasabah = (int) $stmt->fetchColumn();


    // ========================================
    // TOTAL SETORAN YANG DITERIMA
    // ========================================

    $stmt = $pdo->query("
        SELECT
            COUNT(*) AS jumlah,
            COALESCE(
                SUM(total_harga),
                0
            ) AS total
        FROM setoran
        WHERE status = 'diterima'
    ");

    $data_setoran =
        $stmt->fetch(PDO::FETCH_ASSOC);

    $total_setoran =
        (int) ($data_setoran['jumlah'] ?? 0);

    $total_nilai_setoran =
        (float) ($data_setoran['total'] ?? 0);


    // ========================================
    // TOTAL PENARIKAN YANG DITERIMA
    // ========================================

    $stmt = $pdo->query("
        SELECT
            COUNT(*) AS jumlah,
            COALESCE(
                SUM(jumlah),
                0
            ) AS total
        FROM penarikan
        WHERE status = 'diterima'
    ");

    $data_penarikan =
        $stmt->fetch(PDO::FETCH_ASSOC);

    $total_penarikan =
        (int) ($data_penarikan['jumlah'] ?? 0);

    $total_nilai_penarikan =
        (float) ($data_penarikan['total'] ?? 0);


// ========================================
// QUERY LAPORAN PENARIKAN
// ========================================

$query_penarikan = "
    SELECT
        p.id,
        p.kode_penarikan,
        p.jumlah,
        p.metode,
        p.nomor_tujuan,
        p.status,
        p.tanggal_pengajuan,
        n.nama
    FROM penarikan p
    INNER JOIN nasabah n
        ON p.nasabah_id = n.id
    WHERE 1=1
";

$params_penarikan = [];
$types_penarikan = "";


// FILTER TANGGAL DARI
if (!empty($tanggal_dari)) {

    $query_penarikan .= "
        AND DATE(p.tanggal_pengajuan) >= ?
    ";

    $params_penarikan[] = $tanggal_dari;
    $types_penarikan .= "s";
}


// FILTER TANGGAL SAMPAI
if (!empty($tanggal_sampai)) {

    $query_penarikan .= "
        AND DATE(p.tanggal_pengajuan) <= ?
    ";

    $params_penarikan[] = $tanggal_sampai;
    $types_penarikan .= "s";
}


// FILTER STATUS
if (!empty($status_filter)) {

    $query_penarikan .= "
        AND p.status = ?
    ";

    $params_penarikan[] = $status_filter;
    $types_penarikan .= "s";
}


$query_penarikan .= "
    ORDER BY p.tanggal_pengajuan DESC
";


// EKSEKUSI QUERY
$stmt_penarikan = $pdo->prepare($query_penarikan);

if (!empty($params_penarikan)) {

    $stmt_penarikan->execute($params_penarikan);

} else {

    $stmt_penarikan->execute();

}

$penarikan_data = $stmt_penarikan->fetchAll(PDO::FETCH_ASSOC);

$query_setor = "
    SELECT
        setoran.id,
        setoran.created_at,
        setoran.berat,
        setoran.harga_per_kg,
        setoran.total_harga,
        setoran.status,
        nasabah.nama,
        jenis_sampah.nama_sampah
    FROM setoran
    INNER JOIN nasabah
        ON setoran.nasabah_id = nasabah.id
    INNER JOIN jenis_sampah
        ON setoran.jenis_sampah_id = jenis_sampah.id
    WHERE 1=1
";

$params_setor = [];

    // ========================================
    // FILTER TANGGAL DARI
    // ========================================

    if ($tanggal_dari !== '') {

        $query_setor .= "
            AND DATE(setoran.created_at)
            >= :tanggal_dari
        ";

        $params_setor[':tanggal_dari'] =
            $tanggal_dari;
    }


    // ========================================
    // FILTER TANGGAL SAMPAI
    // ========================================

    if ($tanggal_sampai !== '') {

        $query_setor .= "
            AND DATE(setoran.created_at)
            <= :tanggal_sampai
        ";

        $params_setor[':tanggal_sampai'] =
            $tanggal_sampai;
    }


    // ========================================
    // FILTER STATUS
    // ========================================

    if ($status_filter !== '') {

        $query_setor .= "
            AND setoran.status = :status
        ";

        $params_setor[':status'] =
            $status_filter;
    }


    // ========================================
    // URUTKAN DATA
    // ========================================

    $query_setor .= "
        ORDER BY setoran.created_at DESC
    ";


    // ========================================
    // EKSEKUSI QUERY
    // ========================================

    $stmt = $pdo->prepare(
        $query_setor
    );

    $stmt->execute(
        $params_setor
    );


    $transaksi_setor =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );


} catch (PDOException $e) {

    $error =
        'Gagal mengambil data laporan.';

}


// ========================================
// HEADER & SIDEBAR
// ========================================

require_once __DIR__ . '/../../includes/header.php';

require_once __DIR__ . '/../../includes/sidebar.php';

?>


<main class="main-content">


    <!-- TOPBAR -->

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
                        Administrator
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- CONTENT -->

    <div
        style="
            margin-top: 30px;
        "
    >

<!-- ========================================
     FILTER LAPORAN
======================================== -->

<div class="card" style="margin-top: 30px;">

    <div style="margin-bottom: 20px;">
        <h2 style="margin-bottom: 5px;">
            Filter Laporan
        </h2>

        <p style="color: #64748b;">
            Gunakan filter untuk menampilkan
            transaksi berdasarkan tanggal dan status.
        </p>
    </div>


    <form method="GET"
          action=""
          style="
              display: grid;
              grid-template-columns:
                  repeat(auto-fit, minmax(200px, 1fr));
              gap: 15px;
              align-items: end;
          ">


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
                value="<?= htmlspecialchars($tanggal_dari) ?>"
                style="
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #cbd5e1;
                    border-radius: 8px;
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
                value="<?= htmlspecialchars($tanggal_sampai) ?>"
                style="
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #cbd5e1;
                    border-radius: 8px;
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
                Status Setoran
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

        <div
            style="
                display: flex;
                gap: 10px;
            "
        >

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
                href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>"
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

<br>
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

<?php

// ========================================
// REKAP SETORAN SESUAI FILTER
// ========================================

$rekap_query = "
    SELECT
        COUNT(*) AS jumlah_transaksi,
        COALESCE(
            SUM(total_harga),
            0
        ) AS total_nilai
    FROM setoran
    WHERE 1=1
";

$rekap_params = [];


// FILTER TANGGAL DARI

if ($tanggal_dari !== '') {

    $rekap_query .= "
        AND DATE(created_at)
        >= :tanggal_dari
    ";

    $rekap_params[':tanggal_dari'] =
        $tanggal_dari;
}


// FILTER TANGGAL SAMPAI

if ($tanggal_sampai !== '') {

    $rekap_query .= "
        AND DATE(created_at)
        <= :tanggal_sampai
    ";

    $rekap_params[':tanggal_sampai'] =
        $tanggal_sampai;
}


// FILTER STATUS

if ($status_filter !== '') {

    $rekap_query .= "
        AND status = :status
    ";

    $rekap_params[':status'] =
        $status_filter;
}


$stmt_rekap = $pdo->prepare(
    $rekap_query
);

$stmt_rekap->execute(
    $rekap_params
);

$rekap_setoran =
    $stmt_rekap->fetch(PDO::FETCH_ASSOC);


$jumlah_transaksi =
    (int) (
        $rekap_setoran['jumlah_transaksi']
        ?? 0
    );


$total_nilai =
    (float) (
        $rekap_setoran['total_nilai']
        ?? 0
    );


// ========================================
// TOTAL NASABAH
// ========================================

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE role = 'nasabah'
");

$total_nasabah_filter =
    (int) $stmt->fetchColumn();


// ========================================
// TOTAL PENARIKAN
// ========================================

$stmt = $pdo->query("
    SELECT
        COALESCE(
            SUM(jumlah),
            0
        )
    FROM penarikan
");

$total_penarikan_filter =
    (float) $stmt->fetchColumn();

?>

<!-- ========================================
     KARTU REKAP
======================================== -->

<div
    style="
        display: grid;
        grid-template-columns:
            repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin: 25px 0;
    "
>


    <!-- TOTAL NASABAH -->

    <div
        style="
            background: white;
            padding: 22px;
            border-radius: 15px;
            box-shadow:
                0 8px 25px rgba(0,0,0,.06);
            border-left:
                5px solid #166534;
        "
    >

        <div
            style="
                color: #6b7280;
                font-size: 14px;
                margin-bottom: 8px;
            "
        >
            👥 Total Nasabah
        </div>

        <div
            style="
                font-size: 28px;
                font-weight: bold;
                color: #166534;
            "
        >
            <?= number_format(
                $total_nasabah_filter,
                0,
                ',',
                '.'
            ) ?>
        </div>

    </div>


    <!-- JUMLAH SETORAN -->

    <div
        style="
            background: white;
            padding: 22px;
            border-radius: 15px;
            box-shadow:
                0 8px 25px rgba(0,0,0,.06);
            border-left:
                5px solid #2563eb;
        "
    >

        <div
            style="
                color: #6b7280;
                font-size: 14px;
                margin-bottom: 8px;
            "
        >
            ♻️ Transaksi Setor
        </div>

        <div
            style="
                font-size: 28px;
                font-weight: bold;
                color: #2563eb;
            "
        >
            <?= number_format(
                $jumlah_transaksi,
                0,
                ',',
                '.'
            ) ?>
        </div>

    </div>


    <!-- TOTAL NILAI SETORAN -->

    <div
        style="
            background: white;
            padding: 22px;
            border-radius: 15px;
            box-shadow:
                0 8px 25px rgba(0,0,0,.06);
            border-left:
                5px solid #16a34a;
        "
    >

        <div
            style="
                color: #6b7280;
                font-size: 14px;
                margin-bottom: 8px;
            "
        >
            💰 Total Nilai Setoran
        </div>

        <div
            style="
                font-size: 24px;
                font-weight: bold;
                color: #16a34a;
            "
        >
            Rp
            <?= number_format(
                $total_nilai,
                0,
                ',',
                '.'
            ) ?>
        </div>

    </div>


    <!-- TOTAL PENARIKAN -->

    <div
        style="
            background: white;
            padding: 22px;
            border-radius: 15px;
            box-shadow:
                0 8px 25px rgba(0,0,0,.06);
            border-left:
                5px solid #dc2626;
        "
    >

        <div
            style="
                color: #6b7280;
                font-size: 14px;
                margin-bottom: 8px;
            "
        >
            💸 Total Penarikan
        </div>

        <div
            style="
                font-size: 24px;
                font-weight: bold;
                color: #dc2626;
            "
        >
            Rp
            <?= number_format(
                $total_penarikan_filter,
                0,
                ',',
                '.'
            ) ?>
        </div>

    </div>

</div>


        <!-- ======================================== -->
<!-- TRANSAKSI SETOR -->
<!-- ======================================== -->

<div
    style="
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow:
            0 10px 30px
            rgba(0,0,0,.06);
        margin-top: 25px;
    "
>

    <h2
        style="
            margin-top: 0;
            color: #166534;
        "
    >
        Laporan Transaksi Setor
    </h2>

    <p
        style="
            color: #6b7280;
            margin-bottom: 20px;
        "
    >
        Daftar seluruh transaksi setor sampah.
    </p>


    <?php if (empty($transaksi_setor)): ?>

        <div
            style="
                padding: 25px;
                text-align: center;
                color: #6b7280;
                background: #f9fafb;
                border-radius: 10px;
            "
        >
            Belum ada transaksi setor.
        </div>

    <?php else: ?>

        <div
            style="
                overflow-x: auto;
            "
        >

            <table
                style="
                    width: 100%;
                    border-collapse: collapse;
                "
            >

                <thead>

                    <tr>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                white-space: nowrap;
                            "
                        >
                            No
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                white-space: nowrap;
                            "
                        >
                            Tanggal
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                white-space: nowrap;
                            "
                        >
                            Nasabah
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                white-space: nowrap;
                            "
                        >
                            Jenis Sampah
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                white-space: nowrap;
                            "
                        >
                            Berat
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                white-space: nowrap;
                            "
                        >
                            Harga/kg
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                white-space: nowrap;
                            "
                        >
                            Total
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                white-space: nowrap;
                            "
                        >
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

                            <td
                                style="
                                    padding: 12px;
                                    border-bottom:
                                        1px solid #e5e7eb;
                                "
                            >
                                <?= $index + 1 ?>
                            </td>


                            <td
                                style="
                                    padding: 12px;
                                    border-bottom:
                                        1px solid #e5e7eb;
                                    white-space: nowrap;
                                "
                            >
                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime(
                                        $item['created_at']
                                    )
                                ) ?>
                            </td>


                            <td
                                style="
                                    padding: 12px;
                                    border-bottom:
                                        1px solid #e5e7eb;
                                "
                            >
                                <?= htmlspecialchars(
                                    $item['nama']
                                ) ?>
                            </td>


                            <td
                                style="
                                    padding: 12px;
                                    border-bottom:
                                        1px solid #e5e7eb;
                                "
                            >
                                <?= htmlspecialchars(
                                    $item['nama_sampah']
                                ) ?>
                            </td>


                            <td
                                style="
                                    padding: 12px;
                                    border-bottom:
                                        1px solid #e5e7eb;
                                    white-space: nowrap;
                                "
                            >
                                <?= number_format(
                                    $item['berat'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                                kg
                            </td>


                            <td
                                style="
                                    padding: 12px;
                                    border-bottom:
                                        1px solid #e5e7eb;
                                    white-space: nowrap;
                                "
                            >
                                Rp
                                <?= number_format(
                                    $item['harga_per_kg'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>


                            <td
                                style="
                                    padding: 12px;
                                    border-bottom:
                                        1px solid #e5e7eb;
                                    white-space: nowrap;
                                "
                            >
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


                            <td
                                style="
                                    padding: 12px;
                                    border-bottom:
                                        1px solid #e5e7eb;
                                "
                            >

                                <?php if (
                                    $item['status']
                                    === 'pending'
                                ): ?>

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 6px 10px;
                                            border-radius: 20px;
                                            background: #fef3c7;
                                            color: #92400e;
                                            font-size: 13px;
                                            font-weight: bold;
                                        "
                                    >
                                        Pending
                                    </span>

                                <?php elseif (
                                    $item['status']
                                    === 'diterima'
                                ): ?>

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 6px 10px;
                                            border-radius: 20px;
                                            background: #dcfce7;
                                            color: #166534;
                                            font-size: 13px;
                                            font-weight: bold;
                                        "
                                    >
                                        Diterima
                                    </span>

                                <?php elseif (
                                    $item['status']
                                    === 'ditolak'
                                ): ?>

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 6px 10px;
                                            border-radius: 20px;
                                            background: #fee2e2;
                                            color: #b91c1c;
                                            font-size: 13px;
                                            font-weight: bold;
                                        "
                                    >
                                        Ditolak
                                    </span>

                                <?php else: ?>

                                    <span
                                        style="
                                            color: #6b7280;
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $item['status']
                                        ) ?>
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

<!-- ======================================== -->
<!-- LAPORAN PENARIKAN -->
<!-- ======================================== -->

<div
    style="
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,.06);
        margin-top: 25px;
        overflow-x: auto;
    "
>

    <h2
        style="
            margin-top: 0;
            color: #166534;
        "
    >
        Laporan Penarikan
    </h2>

    <p
        style="
            color: #6b7280;
            margin-bottom: 20px;
        "
    >
        Daftar seluruh pengajuan penarikan saldo nasabah.
    </p>

    <?php

    try {

        $query_penarikan = "
            SELECT
                p.id,
                p.kode_penarikan,
                p.jumlah,
                p.metode,
                p.nomor_tujuan,
                p.status,
                p.tanggal_pengajuan,
                n.nama
            FROM penarikan p
            INNER JOIN nasabah n
                ON p.nasabah_id = n.id
            ORDER BY p.tanggal_pengajuan DESC
        ";

        $stmt_penarikan = $pdo->query($query_penarikan);

        $data_penarikan = $stmt_penarikan->fetchAll(
            PDO::FETCH_ASSOC
        );

    } catch (PDOException $e) {

        $data_penarikan = [];

    }

    ?>

    <?php if (!empty($data_penarikan)): ?>

        <div
            style="
                overflow-x: auto;
            "
        >

            <table
                style="
                    width: 100%;
                    border-collapse: collapse;
                    min-width: 900px;
                "
            >

                <thead>

                    <tr>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            No
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Tanggal
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Kode
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Nasabah
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Jumlah
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Metode
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Nomor Tujuan
                        </th>

                        <th
                            style="
                                padding: 12px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Status
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php $no = 1; ?>

                    <?php foreach ($data_penarikan as $row): ?>

                        <tr>

                            <!-- NO -->

                            <td
                                style="
                                    padding: 12px;
                                    border-bottom: 1px solid #e5e7eb;
                                "
                            >
                                <?= $no++ ?>
                            </td>


                            <!-- TANGGAL -->

                            <td
                                style="
                                    padding: 12px;
                                    border-bottom: 1px solid #e5e7eb;
                                    white-space: nowrap;
                                "
                            >
                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime(
                                        $row['tanggal_pengajuan']
                                    )
                                ) ?>
                            </td>


                            <!-- KODE -->

                            <td
                                style="
                                    padding: 12px;
                                    border-bottom: 1px solid #e5e7eb;
                                    font-weight: bold;
                                    white-space: nowrap;
                                "
                            >
                                <?= htmlspecialchars(
                                    $row['kode_penarikan']
                                ) ?>
                            </td>


                            <!-- NASABAH -->

                            <td
                                style="
                                    padding: 12px;
                                    border-bottom: 1px solid #e5e7eb;
                                "
                            >
                                <?= htmlspecialchars(
                                    $row['nama']
                                ) ?>
                            </td>


                            <!-- JUMLAH -->

                            <td
                                style="
                                    padding: 12px;
                                    border-bottom: 1px solid #e5e7eb;
                                    font-weight: bold;
                                    color: #166534;
                                    white-space: nowrap;
                                "
                            >
                                Rp
                                <?= number_format(
                                    $row['jumlah'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>


                            <!-- METODE -->

                            <td
                                style="
                                    padding: 12px;
                                    border-bottom: 1px solid #e5e7eb;
                                "
                            >
                                <?= ucfirst(
                                    htmlspecialchars(
                                        $row['metode']
                                    )
                                ) ?>
                            </td>


                            <!-- NOMOR TUJUAN -->

                            <td
                                style="
                                    padding: 12px;
                                    border-bottom: 1px solid #e5e7eb;
                                    white-space: nowrap;
                                "
                            >
                                <?= htmlspecialchars(
                                    $row['nomor_tujuan']
                                ) ?>
                            </td>


                            <!-- STATUS -->

                            <td
                                style="
                                    padding: 12px;
                                    border-bottom: 1px solid #e5e7eb;
                                "
                            >

                                <?php if (
                                    $row['status'] === 'pending'
                                ): ?>

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 6px 10px;
                                            border-radius: 20px;
                                            background: #fef3c7;
                                            color: #92400e;
                                            font-size: 13px;
                                            font-weight: bold;
                                        "
                                    >
                                        Pending
                                    </span>

                                <?php elseif (
                                    $row['status'] === 'diterima'
                                ): ?>

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 6px 10px;
                                            border-radius: 20px;
                                            background: #dcfce7;
                                            color: #166534;
                                            font-size: 13px;
                                            font-weight: bold;
                                        "
                                    >
                                        Diterima
                                    </span>

                                <?php elseif (
                                    $row['status'] === 'ditolak'
                                ): ?>

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 6px 10px;
                                            border-radius: 20px;
                                            background: #fee2e2;
                                            color: #b91c1c;
                                            font-size: 13px;
                                            font-weight: bold;
                                        "
                                    >
                                        Ditolak
                                    </span>

                                <?php else: ?>

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 6px 10px;
                                            border-radius: 20px;
                                            background: #e5e7eb;
                                            color: #374151;
                                            font-size: 13px;
                                            font-weight: bold;
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $row['status']
                                        ) ?>
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <div
            style="
                padding: 30px;
                text-align: center;
                color: #6b7280;
                background: #f9fafb;
                border-radius: 10px;
            "
        >
            Belum ada data penarikan.
        </div>

    <?php endif; ?>

</div>


    </div>


</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>

