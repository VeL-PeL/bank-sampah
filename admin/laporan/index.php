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
// HITUNG DATA
// ========================================

try {

    // JUMLAH NASABAH
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM nasabah
    ");

    $total_nasabah = (int) $stmt->fetchColumn();


    // TOTAL SETORAN YANG DITERIMA
    $stmt = $pdo->query("
        SELECT
            COUNT(*) AS jumlah,
            COALESCE(SUM(total_harga), 0) AS total
        FROM setoran
        WHERE status = 'diterima'
    ");

    $data_setoran = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_setoran = (int) ($data_setoran['jumlah'] ?? 0);

    $total_nilai_setoran =
        (float) ($data_setoran['total'] ?? 0);


    // TOTAL PENARIKAN YANG DITERIMA
    $stmt = $pdo->query("
        SELECT
            COUNT(*) AS jumlah,
            COALESCE(SUM(jumlah), 0) AS total
        FROM penarikan
        WHERE status = 'diterima'
    ");

    $data_penarikan = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_penarikan =
        (int) ($data_penarikan['jumlah'] ?? 0);

    $total_nilai_penarikan =
        (float) ($data_penarikan['total'] ?? 0);

    // ========================================
// DATA TRANSAKSI SETOR
// ========================================

$stmt = $pdo->query("
    SELECT
        setoran.id,
        users.nama,
        jenis_sampah.nama_sampah,
        setoran.berat,
        setoran.harga_per_kg,
        setoran.total_harga,
        setoran.status,
        setoran.created_at
    FROM setoran

    INNER JOIN users
        ON setoran.user_id = users.id

    INNER JOIN jenis_sampah
        ON setoran.jenis_sampah_id = jenis_sampah.id

    ORDER BY setoran.created_at DESC
");

$transaksi_setor = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    $error = 'Gagal mengambil data laporan.';

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


        <?php if ($error !== ''): ?>

            <div
                style="
                    background: #fee2e2;
                    color: #991b1b;
                    padding: 15px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                "
            >

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <!-- STATISTICS -->

        <div
            style="
                display: grid;
                grid-template-columns:
                    repeat(4, minmax(0, 1fr));
                gap: 20px;
                margin-bottom: 25px;
            "
        >


            <!-- NASABAH -->

            <div
                style="
                    background: white;
                    padding: 22px;
                    border-radius: 15px;
                    box-shadow:
                        0 5px 20px
                        rgba(0,0,0,.06);
                "
            >

                <div
                    style="
                        font-size: 13px;
                        color: #6b7280;
                        margin-bottom: 8px;
                    "
                >
                    Total Nasabah
                </div>

                <div
                    style="
                        font-size: 28px;
                        font-weight: bold;
                        color: #166534;
                    "
                >
                    <?= number_format(
                        $total_nasabah,
                        0,
                        ',',
                        '.'
                    ) ?>
                </div>

            </div>


            <!-- SETORAN -->

            <div
                style="
                    background: white;
                    padding: 22px;
                    border-radius: 15px;
                    box-shadow:
                        0 5px 20px
                        rgba(0,0,0,.06);
                "
            >

                <div
                    style="
                        font-size: 13px;
                        color: #6b7280;
                        margin-bottom: 8px;
                    "
                >
                    Setoran Diterima
                </div>

                <div
                    style="
                        font-size: 28px;
                        font-weight: bold;
                        color: #166534;
                    "
                >
                    <?= number_format(
                        $total_setoran,
                        0,
                        ',',
                        '.'
                    ) ?>
                </div>

            </div>


            <!-- NILAI SETORAN -->

            <div
                style="
                    background: white;
                    padding: 22px;
                    border-radius: 15px;
                    box-shadow:
                        0 5px 20px
                        rgba(0,0,0,.06);
                "
            >

                <div
                    style="
                        font-size: 13px;
                        color: #6b7280;
                        margin-bottom: 8px;
                    "
                >
                    Nilai Setoran
                </div>

                <div
                    style="
                        font-size: 22px;
                        font-weight: bold;
                        color: #166534;
                    "
                >
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

            <div
                style="
                    background: white;
                    padding: 22px;
                    border-radius: 15px;
                    box-shadow:
                        0 5px 20px
                        rgba(0,0,0,.06);
                "
            >

                <div
                    style="
                        font-size: 13px;
                        color: #6b7280;
                        margin-bottom: 8px;
                    "
                >
                    Total Penarikan
                </div>

                <div
                    style="
                        font-size: 22px;
                        font-weight: bold;
                        color: #b45309;
                    "
                >
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


        <!-- SUMMARY -->

        <div
            style="
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow:
                    0 10px 30px
                    rgba(0,0,0,.06);
            "
        >

            <h2
                style="
                    margin-top: 0;
                    color: #166534;
                "
            >
                Ringkasan Laporan
            </h2>


            <p
                style="
                    color: #6b7280;
                    margin-top: 8px;
                "
            >
                Berikut adalah ringkasan data
                Bank Sampah berdasarkan transaksi
                yang sudah diterima.
            </p>


            <div
                style="
                    margin-top: 25px;
                    padding: 18px;
                    background: #f0fdf4;
                    border-radius: 10px;
                "
            >

                <strong>
                    Status Laporan
                </strong>

                <p
                    style="
                        margin-top: 8px;
                        color: #166534;
                    "
                >
                    Sistem laporan berhasil terhubung
                    dengan database.
                </p>

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

<!-- LAPORAN PENARIKAN -->
<div class="card" style="margin-top: 30px;">

    <div class="card-header">
        <div>
            <h2>Laporan Penarikan</h2>
            <p>Daftar seluruh pengajuan penarikan saldo nasabah.</p>
        </div>
    </div>

    <?php
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

    $result_penarikan = mysqli_query($conn, $query_penarikan);
    ?>

    <?php if ($result_penarikan && mysqli_num_rows($result_penarikan) > 0): ?>

        <div class="table-responsive">

            <table class="data-table">

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

                    <?php
                    $no = 1;

                    while ($row = mysqli_fetch_assoc($result_penarikan)):
                    ?>

                        <tr>

                            <td>
                                <?= $no++ ?>
                            </td>

                            <td>
                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime($row['tanggal_pengajuan'])
                                ) ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars(
                                        $row['kode_penarikan']
                                    ) ?>
                                </strong>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['nama']
                                ) ?>
                            </td>

                            <td>
                                <strong style="color: #087f3f;">
                                    Rp <?= number_format(
                                        $row['jumlah'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </strong>
                            </td>

                            <td>
                                <?= ucfirst(
                                    htmlspecialchars(
                                        $row['metode']
                                    )
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['nomor_tujuan']
                                ) ?>
                            </td>

                            <td>

                                <?php if ($row['status'] === 'pending'): ?>

                                    <span class="status-badge pending">
                                        Menunggu
                                    </span>

                                <?php elseif ($row['status'] === 'disetujui'): ?>

                                    <span class="status-badge approved">
                                        Disetujui
                                    </span>

                                <?php elseif ($row['status'] === 'ditolak'): ?>

                                    <span class="status-badge rejected">
                                        Ditolak
                                    </span>

                                <?php elseif ($row['status'] === 'selesai'): ?>

                                    <span class="status-badge success">
                                        Selesai
                                    </span>

                                <?php else: ?>

                                    <span class="status-badge">
                                        <?= htmlspecialchars(
                                            $row['status']
                                        ) ?>
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <div style="
            padding: 40px;
            text-align: center;
            color: #777;
        ">
            Belum ada data penarikan.
        </div>

    <?php endif; ?>

</div>


    </div>


</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>
```
