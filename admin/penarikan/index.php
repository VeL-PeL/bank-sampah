<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');

require_once __DIR__ . '/../../config/database.php';


// ========================================
// FILTER
// ========================================

$status_filter = $_GET['status'] ?? '';

$status_valid = [
    '',
    'pending',
    'diterima',
    'ditolak'
];

if (!in_array($status_filter, $status_valid, true)) {
    $status_filter = '';
}


// ========================================
// QUERY
// ========================================

$where = [];
$params = [];

if ($status_filter !== '') {
    $where[] = 'p.status = ?';
    $params[] = $status_filter;
}


$penarikan = [];
$error = '';

try {

    $sql = "
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

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= "
        ORDER BY p.tanggal_pengajuan DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $penarikan = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = 'Gagal mengambil data penarikan.';
}


$total_penarikan = count($penarikan);


// ========================================
// HITUNG JUMLAH BERDASARKAN STATUS
// ========================================

$total_pending = 0;
$total_diterima = 0;
$total_ditolak = 0;

foreach ($penarikan as $item) {

    if ($item['status'] === 'pending') {
        $total_pending++;
    }

    if ($item['status'] === 'diterima') {
        $total_diterima++;
    }

    if ($item['status'] === 'ditolak') {
        $total_ditolak++;
    }
}


// ========================================
// HEADER & SIDEBAR
// ========================================

$page_title = 'Penarikan';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

?>


<main class="main-content">


    <!-- ========================================
         TOPBAR
    ======================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Penarikan
                </h1>

                <p>
                    Kelola pengajuan penarikan saldo nasabah.
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



    <!-- ========================================
         HEADER HALAMAN
    ======================================== -->

    <div
        style="
            margin-top: 30px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        "
    >

        <div>

            <h2
                style="
                    margin: 0;
                    font-size: 24px;
                    color: #14532d;
                "
            >
                Pengajuan Penarikan
            </h2>

            <p
                style="
                    margin: 7px 0 0;
                    color: #6b7280;
                    font-size: 14px;
                "
            >
                Pantau dan proses pengajuan penarikan saldo nasabah.
            </p>

        </div>


        <a
            href="../dashboard.php"
            style="
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 16px;
                border-radius: 10px;
                background: white;
                color: #166534;
                text-decoration: none;
                font-weight: 600;
                border: 1px solid #e5e7eb;
                box-shadow: 0 4px 15px rgba(0,0,0,.04);
            "
        >
            ← Dashboard
        </a>

    </div>



    <!-- ========================================
         STATISTIK
    ======================================== -->

    <div
        style="
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        "
    >


        <!-- TOTAL -->

        <div
            style="
                background: white;
                padding: 22px;
                border-radius: 16px;
                border: 1px solid #f0f0f0;
                box-shadow: 0 8px 25px rgba(0,0,0,.05);
            "
        >

            <div
                style="
                    width: 44px;
                    height: 44px;
                    border-radius: 12px;
                    background: #f0fdf4;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 21px;
                "
            >
                💰
            </div>

            <p
                style="
                    margin: 14px 0 5px;
                    color: #6b7280;
                    font-size: 14px;
                "
            >
                Total Pengajuan
            </p>

            <h3
                style="
                    margin: 0;
                    font-size: 27px;
                    color: #166534;
                "
            >
                <?= number_format(
                    $total_penarikan,
                    0,
                    ',',
                    '.'
                ) ?>
            </h3>

        </div>



        <!-- PENDING -->

        <div
            style="
                background: white;
                padding: 22px;
                border-radius: 16px;
                border: 1px solid #f0f0f0;
                box-shadow: 0 8px 25px rgba(0,0,0,.05);
            "
        >

            <div
                style="
                    width: 44px;
                    height: 44px;
                    border-radius: 12px;
                    background: #fffbeb;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 21px;
                "
            >
                ⏳
            </div>

            <p
                style="
                    margin: 14px 0 5px;
                    color: #6b7280;
                    font-size: 14px;
                "
            >
                Pending
            </p>

            <h3
                style="
                    margin: 0;
                    font-size: 27px;
                    color: #b45309;
                "
            >
                <?= number_format(
                    $total_pending,
                    0,
                    ',',
                    '.'
                ) ?>
            </h3>

        </div>



        <!-- DITERIMA -->

        <div
            style="
                background: white;
                padding: 22px;
                border-radius: 16px;
                border: 1px solid #f0f0f0;
                box-shadow: 0 8px 25px rgba(0,0,0,.05);
            "
        >

            <div
                style="
                    width: 44px;
                    height: 44px;
                    border-radius: 12px;
                    background: #f0fdf4;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 21px;
                "
            >
                ✓
            </div>

            <p
                style="
                    margin: 14px 0 5px;
                    color: #6b7280;
                    font-size: 14px;
                "
            >
                Diterima
            </p>

            <h3
                style="
                    margin: 0;
                    font-size: 27px;
                    color: #166534;
                "
            >
                <?= number_format(
                    $total_diterima,
                    0,
                    ',',
                    '.'
                ) ?>
            </h3>

        </div>



        <!-- DITOLAK -->

        <div
            style="
                background: white;
                padding: 22px;
                border-radius: 16px;
                border: 1px solid #f0f0f0;
                box-shadow: 0 8px 25px rgba(0,0,0,.05);
            "
        >

            <div
                style="
                    width: 44px;
                    height: 44px;
                    border-radius: 12px;
                    background: #fef2f2;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 21px;
                "
            >
                ×
            </div>

            <p
                style="
                    margin: 14px 0 5px;
                    color: #6b7280;
                    font-size: 14px;
                "
            >
                Ditolak
            </p>

            <h3
                style="
                    margin: 0;
                    font-size: 27px;
                    color: #b91c1c;
                "
            >
                <?= number_format(
                    $total_ditolak,
                    0,
                    ',',
                    '.'
                ) ?>
            </h3>

        </div>

    </div>



    <!-- ========================================
         FILTER
    ======================================== -->

    <div
        style="
            background: white;
            padding: 22px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,.05);
            border: 1px solid #f0f0f0;
            margin-bottom: 20px;
        "
    >

        <div
            style="
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 17px;
            "
        >

            <span
                style="
                    width: 36px;
                    height: 36px;
                    border-radius: 10px;
                    background: #f0fdf4;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                "
            >
                🔎
            </span>

            <div>

                <h3
                    style="
                        margin: 0;
                        font-size: 16px;
                        color: #374151;
                    "
                >
                    Filter Data
                </h3>

                <p
                    style="
                        margin: 3px 0 0;
                        color: #9ca3af;
                        font-size: 12px;
                    "
                >
                    Pilih status untuk melihat data tertentu.
                </p>

            </div>

        </div>


        <form method="GET" action="">

            <div
                style="
                    display: flex;
                    align-items: end;
                    gap: 12px;
                    flex-wrap: wrap;
                "
            >

                <div>

                    <label
                        for="status"
                        style="
                            display: block;
                            margin-bottom: 7px;
                            font-size: 13px;
                            font-weight: 600;
                            color: #374151;
                        "
                    >
                        Status Pengajuan
                    </label>

                    <select
                        name="status"
                        id="status"
                        style="
                            min-width: 210px;
                            padding: 11px 13px;
                            border: 1px solid #d1d5db;
                            border-radius: 9px;
                            background: white;
                            color: #374151;
                            outline: none;
                        "
                    >

                        <option value="">
                            Semua Status
                        </option>

                        <option
                            value="pending"
                            <?= $status_filter === 'pending'
                                ? 'selected'
                                : '' ?>
                        >
                            Pending
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


                <button
                    type="submit"
                    style="
                        padding: 11px 18px;
                        border: none;
                        border-radius: 9px;
                        background: #166534;
                        color: white;
                        font-weight: 600;
                        cursor: pointer;
                    "
                >
                    🔎 Terapkan
                </button>


                <a
                    href="index.php"
                    style="
                        padding: 11px 18px;
                        border-radius: 9px;
                        background: #f3f4f6;
                        color: #374151;
                        text-decoration: none;
                        font-weight: 600;
                    "
                >
                    ↻ Reset
                </a>

            </div>

        </form>

    </div>



    <!-- ========================================
         INFO JUMLAH
    ======================================== -->

    <div
        style="
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            background: #f0fdf4;
            padding: 15px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #dcfce7;
        "
    >

        <div
            style="
                color: #166534;
                font-weight: 600;
                font-size: 14px;
            "
        >
            📊
            <?= number_format(
                $total_penarikan,
                0,
                ',',
                '.'
            ) ?>
            pengajuan ditemukan
        </div>


        <?php if ($status_filter !== ''): ?>

            <div
                style="
                    padding: 5px 10px;
                    border-radius: 20px;
                    background: white;
                    color: #166534;
                    font-size: 12px;
                    font-weight: 600;
                "
            >
                Filter:
                <?= ucfirst(
                    htmlspecialchars($status_filter)
                ) ?>
            </div>

        <?php endif; ?>

    </div>



    <?php if ($error !== ''): ?>


        <!-- ========================================
             ERROR
        ======================================== -->

        <div
            style="
                background: #fef2f2;
                color: #b91c1c;
                padding: 18px;
                border-radius: 12px;
                border: 1px solid #fecaca;
            "
        >

            ⚠️
            <?= htmlspecialchars($error) ?>

        </div>


    <?php elseif (empty($penarikan)): ?>


        <!-- ========================================
             EMPTY
        ======================================== -->

        <div
            style="
                background: white;
                padding: 65px 20px;
                border-radius: 16px;
                text-align: center;
                color: #6b7280;
                box-shadow: 0 8px 25px rgba(0,0,0,.05);
                border: 1px solid #f0f0f0;
            "
        >

            <div
                style="
                    width: 70px;
                    height: 70px;
                    margin: 0 auto 18px;
                    border-radius: 20px;
                    background: #f0fdf4;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 34px;
                "
            >
                💸
            </div>

            <h3
                style="
                    margin: 0 0 8px;
                    color: #374151;
                    font-size: 18px;
                "
            >
                Belum Ada Pengajuan
            </h3>

            <p
                style="
                    margin: 0;
                    font-size: 14px;
                "
            >
                Belum ada pengajuan penarikan
                sesuai filter yang dipilih.
            </p>

        </div>


    <?php else: ?>


        <!-- ========================================
             TABLE
        ======================================== -->

        <div
            style="
                background: white;
                padding: 22px;
                border-radius: 16px;
                box-shadow: 0 8px 25px rgba(0,0,0,.05);
                border: 1px solid #f0f0f0;
                overflow-x: auto;
            "
        >

            <div
                style="
                    margin-bottom: 18px;
                "
            >

                <h3
                    style="
                        margin: 0;
                        color: #14532d;
                        font-size: 18px;
                    "
                >
                    Daftar Penarikan
                </h3>

                <p
                    style="
                        margin: 5px 0 0;
                        color: #9ca3af;
                        font-size: 13px;
                    "
                >
                    Data pengajuan penarikan saldo nasabah.
                </p>

            </div>


            <table
                style="
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                    min-width: 1050px;
                "
            >

                <thead>

                    <tr>

                        <th style="
                            padding: 13px;
                            text-align: left;
                            background: #f0fdf4;
                            color: #166534;
                            font-size: 13px;
                            border-bottom: 1px solid #dcfce7;
                        ">
                            No
                        </th>

                        <th style="
                            padding: 13px;
                            text-align: left;
                            background: #f0fdf4;
                            color: #166534;
                            font-size: 13px;
                            border-bottom: 1px solid #dcfce7;
                        ">
                            Kode
                        </th>

                        <th style="
                            padding: 13px;
                            text-align: left;
                            background: #f0fdf4;
                            color: #166534;
                            font-size: 13px;
                            border-bottom: 1px solid #dcfce7;
                        ">
                            Nasabah
                        </th>

                        <th style="
                            padding: 13px;
                            text-align: left;
                            background: #f0fdf4;
                            color: #166534;
                            font-size: 13px;
                            border-bottom: 1px solid #dcfce7;
                        ">
                            Jumlah
                        </th>

                        <th style="
                            padding: 13px;
                            text-align: left;
                            background: #f0fdf4;
                            color: #166534;
                            font-size: 13px;
                            border-bottom: 1px solid #dcfce7;
                        ">
                            Metode
                        </th>

                        <th style="
                            padding: 13px;
                            text-align: left;
                            background: #f0fdf4;
                            color: #166534;
                            font-size: 13px;
                            border-bottom: 1px solid #dcfce7;
                        ">
                            Nomor Tujuan
                        </th>

                        <th style="
                            padding: 13px;
                            text-align: left;
                            background: #f0fdf4;
                            color: #166534;
                            font-size: 13px;
                            border-bottom: 1px solid #dcfce7;
                        ">
                            Status
                        </th>

                        <th style="
                            padding: 13px;
                            text-align: left;
                            background: #f0fdf4;
                            color: #166534;
                            font-size: 13px;
                            border-bottom: 1px solid #dcfce7;
                        ">
                            Tanggal
                        </th>

                        <th style="
                            padding: 13px;
                            text-align: center;
                            background: #f0fdf4;
                            color: #166534;
                            font-size: 13px;
                            border-bottom: 1px solid #dcfce7;
                        ">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php foreach (
                    $penarikan
                    as $index => $item
                ): ?>


                    <?php

                    $status = $item['status'];

                    if ($status === 'pending') {

                        $status_background = '#fef3c7';
                        $status_color = '#92400e';
                        $status_text = 'Pending';

                    } elseif ($status === 'diterima') {

                        $status_background = '#dcfce7';
                        $status_color = '#166534';
                        $status_text = 'Diterima';

                    } elseif ($status === 'ditolak') {

                        $status_background = '#fee2e2';
                        $status_color = '#b91c1c';
                        $status_text = 'Ditolak';

                    } else {

                        $status_background = '#e5e7eb';
                        $status_color = '#374151';
                        $status_text = ucfirst($status);

                    }

                    ?>


                    <tr>


                        <!-- NO -->

                        <td
                            style="
                                padding: 14px;
                                border-bottom: 1px solid #f1f5f9;
                                color: #6b7280;
                            "
                        >
                            <?= $index + 1 ?>
                        </td>


                        <!-- KODE -->

                        <td
                            style="
                                padding: 14px;
                                border-bottom: 1px solid #f1f5f9;
                                white-space: nowrap;
                            "
                        >

                            <span
                                style="
                                    padding: 6px 9px;
                                    background: #f8fafc;
                                    border-radius: 7px;
                                    color: #374151;
                                    font-size: 12px;
                                    font-weight: 700;
                                "
                            >
                                <?= htmlspecialchars(
                                    $item['kode_penarikan']
                                ) ?>
                            </span>

                        </td>


                        <!-- NASABAH -->

                        <td
                            style="
                                padding: 14px;
                                border-bottom: 1px solid #f1f5f9;
                            "
                        >

                            <div
                                style="
                                    display: flex;
                                    align-items: center;
                                    gap: 10px;
                                "
                            >

                                <div
                                    style="
                                        width: 34px;
                                        height: 34px;
                                        flex-shrink: 0;
                                        border-radius: 50%;
                                        background: #dcfce7;
                                        color: #166534;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        font-weight: 700;
                                        font-size: 13px;
                                    "
                                >
                                    <?= strtoupper(
                                        substr(
                                            $item['nama_nasabah'],
                                            0,
                                            1
                                        )
                                    ) ?>
                                </div>

                                <strong
                                    style="
                                        color: #374151;
                                    "
                                >
                                    <?= htmlspecialchars(
                                        $item['nama_nasabah']
                                    ) ?>
                                </strong>

                            </div>

                        </td>


                        <!-- JUMLAH -->

                        <td
                            style="
                                padding: 14px;
                                border-bottom: 1px solid #f1f5f9;
                                white-space: nowrap;
                            "
                        >

                            <strong
                                style="
                                    color: #166534;
                                "
                            >
                                Rp
                                <?= number_format(
                                    $item['jumlah'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </strong>

                        </td>


                        <!-- METODE -->

                        <td
                            style="
                                padding: 14px;
                                border-bottom: 1px solid #f1f5f9;
                            "
                        >
                            <?= htmlspecialchars(
                                $item['metode']
                            ) ?>
                        </td>


                        <!-- NOMOR -->

                        <td
                            style="
                                padding: 14px;
                                border-bottom: 1px solid #f1f5f9;
                                white-space: nowrap;
                            "
                        >
                            <?= htmlspecialchars(
                                $item['nomor_tujuan']
                            ) ?>
                        </td>


                        <!-- STATUS -->

                        <td
                            style="
                                padding: 14px;
                                border-bottom: 1px solid #f1f5f9;
                            "
                        >

                            <span
                                style="
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 5px;
                                    padding: 6px 11px;
                                    border-radius: 20px;
                                    background: <?= $status_background ?>;
                                    color: <?= $status_color ?>;
                                    font-size: 12px;
                                    font-weight: 700;
                                "
                            >

                                <span>
                                    ●
                                </span>

                                <?= htmlspecialchars(
                                    $status_text
                                ) ?>

                            </span>

                        </td>


                        <!-- TANGGAL -->

                        <td
                            style="
                                padding: 14px;
                                border-bottom: 1px solid #f1f5f9;
                                white-space: nowrap;
                                color: #6b7280;
                                font-size: 13px;
                            "
                        >

                            <?= date(
                                'd-m-Y H:i',
                                strtotime(
                                    $item['tanggal_pengajuan']
                                )
                            ) ?>

                        </td>


                        <!-- AKSI -->

                        <td
                            style="
                                padding: 14px;
                                border-bottom: 1px solid #f1f5f9;
                                text-align: center;
                            "
                        >

                            <a
                                href="detail.php?id=<?= (int) $item['id'] ?>"
                                style="
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 5px;
                                    padding: 8px 13px;
                                    border-radius: 8px;
                                    background: #166534;
                                    color: white;
                                    text-decoration: none;
                                    font-size: 12px;
                                    font-weight: 600;
                                "
                            >
                                👁 Detail
                            </a>

                        </td>


                    </tr>


                <?php endforeach; ?>


                </tbody>

            </table>

        </div>


    <?php endif; ?>


    <!-- FOOTER SPACING -->

    <div style="height: 30px;"></div>


</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>