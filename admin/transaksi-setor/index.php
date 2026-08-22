<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Transaksi Setor';


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
// QUERY
// ======================================================

$where = [];
$params = [];

if ($status_filter !== '') {
    $where[] = 's.status = ?';
    $params[] = $status_filter;
}

if ($tanggal_dari !== '') {
    $where[] = 'DATE(s.created_at) >= ?';
    $params[] = $tanggal_dari;
}

if ($tanggal_sampai !== '') {
    $where[] = 'DATE(s.created_at) <= ?';
    $params[] = $tanggal_sampai;
}


$setoran = [];
$error = '';


// ======================================================
// AMBIL DATA TRANSAKSI
// ======================================================

try {

    $sql = "
        SELECT
            s.id,
            users.nama AS nama_nasabah,
            jenis_sampah.nama_sampah,
            s.berat,
            s.harga_per_kg,
            s.total_harga,
            s.status,
            s.created_at

        FROM setoran s

        INNER JOIN users
            ON s.user_id = users.id

        INNER JOIN jenis_sampah
            ON s.jenis_sampah_id = jenis_sampah.id
    ";

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= "
        ORDER BY s.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $setoran = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = 'Gagal mengambil data transaksi setor.';
}


$total_transaksi = count($setoran);


// ======================================================
// HEADER & SIDEBAR
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
                    Transaksi Setor
                </h1>

                <p>
                    Kelola dan pantau seluruh transaksi setor nasabah.
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
                            $_SESSION['nama'] ?? 'Administrator'
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

    <div style="
        margin-top: 30px;
    ">


        <!-- ==================================================
             HEADER
        ================================================== -->

        <div style="
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        ">

            <div>

                <h2 style="
                    margin: 0;
                    color: #166534;
                    font-size: 24px;
                ">
                    Daftar Transaksi Setor
                </h2>

                <p style="
                    margin: 7px 0 0;
                    color: #6b7280;
                    font-size: 14px;
                ">
                    Lihat transaksi setoran sampah dari seluruh nasabah.
                </p>

            </div>


            <!-- TOTAL -->

            <div style="
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 13px 18px;
                background: #f0fdf4;
                border: 1px solid #dcfce7;
                border-radius: 12px;
                color: #166534;
                font-weight: 700;
            ">

                <span style="
                    font-size: 20px;
                ">
                    ♻️
                </span>

                <?= number_format(
                    $total_transaksi,
                    0,
                    ',',
                    '.'
                ) ?>

                Transaksi

            </div>

        </div>


        <!-- ==================================================
             FILTER
        ================================================== -->

        <div style="
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,.05);
            margin-bottom: 20px;
            border: 1px solid #f1f5f9;
        ">

            <div style="
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 20px;
            ">

                <div style="
                    width: 38px;
                    height: 38px;
                    border-radius: 10px;
                    background: #ecfdf5;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 18px;
                ">
                    🔎
                </div>

                <div>

                    <h3 style="
                        margin: 0;
                        color: #166534;
                        font-size: 17px;
                    ">
                        Filter Transaksi
                    </h3>

                    <p style="
                        margin: 3px 0 0;
                        color: #9ca3af;
                        font-size: 13px;
                    ">
                        Gunakan filter untuk mencari transaksi tertentu.
                    </p>

                </div>

            </div>


            <form method="GET" action="">


                <div style="
                    display: grid;
                    grid-template-columns:
                        repeat(auto-fit, minmax(190px, 1fr));
                    gap: 16px;
                    align-items: end;
                ">


                    <!-- STATUS -->

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
                            Status
                        </label>

                        <select
                            name="status"
                            id="status"
                            style="
                                width: 100%;
                                box-sizing: border-box;
                                padding: 11px 13px;
                                border: 1px solid #d1d5db;
                                border-radius: 9px;
                                background: #fff;
                                color: #374151;
                                outline: none;
                            "
                        >

                            <option
                                value=""
                                <?= $status_filter === ''
                                    ? 'selected'
                                    : '' ?>
                            >
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


                    <!-- TANGGAL DARI -->

                    <div>

                        <label
                            for="tanggal_dari"
                            style="
                                display: block;
                                margin-bottom: 7px;
                                font-size: 13px;
                                font-weight: 600;
                                color: #374151;
                            "
                        >
                            Tanggal Dari
                        </label>

                        <input
                            type="date"
                            name="tanggal_dari"
                            id="tanggal_dari"
                            value="<?= htmlspecialchars(
                                $tanggal_dari
                            ) ?>"
                            style="
                                width: 100%;
                                box-sizing: border-box;
                                padding: 10px 13px;
                                border: 1px solid #d1d5db;
                                border-radius: 9px;
                                color: #374151;
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
                                font-size: 13px;
                                font-weight: 600;
                                color: #374151;
                            "
                        >
                            Tanggal Sampai
                        </label>

                        <input
                            type="date"
                            name="tanggal_sampai"
                            id="tanggal_sampai"
                            value="<?= htmlspecialchars(
                                $tanggal_sampai
                            ) ?>"
                            style="
                                width: 100%;
                                box-sizing: border-box;
                                padding: 10px 13px;
                                border: 1px solid #d1d5db;
                                border-radius: 9px;
                                color: #374151;
                            "
                        >

                    </div>


                    <!-- BUTTON -->

                    <div style="
                        display: flex;
                        gap: 8px;
                    ">

                        <button
                            type="submit"
                            style="
                                flex: 1;
                                padding: 11px 17px;
                                border: none;
                                border-radius: 9px;
                                background: #166534;
                                color: white;
                                font-weight: 600;
                                cursor: pointer;
                            "
                        >
                            🔎 Filter
                        </button>


                        <a
                            href="index.php"
                            style="
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                padding: 11px 15px;
                                border-radius: 9px;
                                background: #f3f4f6;
                                color: #374151;
                                text-decoration: none;
                                font-weight: 600;
                            "
                        >
                            Reset
                        </a>

                    </div>

                </div>

            </form>

        </div>


        <!-- ==================================================
             ERROR
        ================================================== -->

        <?php if ($error !== ''): ?>

            <div style="
                background: #fef2f2;
                color: #b91c1c;
                padding: 16px 18px;
                border-radius: 12px;
                margin-bottom: 20px;
                border: 1px solid #fecaca;
            ">

                ⚠️
                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             TABLE
        ================================================== -->

        <?php if (empty($setoran)): ?>

            <div style="
                background: white;
                padding: 55px 20px;
                border-radius: 16px;
                box-shadow: 0 8px 25px rgba(0,0,0,.05);
                text-align: center;
            ">

                <div style="
                    width: 70px;
                    height: 70px;
                    margin: 0 auto 18px;
                    border-radius: 50%;
                    background: #f0fdf4;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 32px;
                ">
                    📭
                </div>

                <h3 style="
                    margin: 0 0 8px;
                    color: #374151;
                ">
                    Tidak Ada Transaksi
                </h3>

                <p style="
                    margin: 0;
                    color: #9ca3af;
                    font-size: 14px;
                ">
                    Belum ada transaksi yang sesuai dengan filter.
                </p>

            </div>

        <?php else: ?>


            <div style="
                background: white;
                padding: 24px;
                border-radius: 16px;
                box-shadow: 0 8px 25px rgba(0,0,0,.05);
                border: 1px solid #f1f5f9;
                overflow-x: auto;
            ">


                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 18px;
                ">

                    <div>

                        <h3 style="
                            margin: 0;
                            color: #374151;
                            font-size: 17px;
                        ">
                            Data Setoran
                        </h3>

                        <p style="
                            margin: 4px 0 0;
                            color: #9ca3af;
                            font-size: 13px;
                        ">
                            <?= $total_transaksi ?>
                            transaksi ditampilkan
                        </p>

                    </div>

                </div>


                <table style="
                    width: 100%;
                    border-collapse: collapse;
                    min-width: 950px;
                ">

                    <thead>

                        <tr>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                font-size: 13px;
                                border-bottom: 1px solid #dcfce7;
                            ">
                                No
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                font-size: 13px;
                                border-bottom: 1px solid #dcfce7;
                            ">
                                Nasabah
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                font-size: 13px;
                                border-bottom: 1px solid #dcfce7;
                            ">
                                Jenis Sampah
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                font-size: 13px;
                                border-bottom: 1px solid #dcfce7;
                            ">
                                Berat
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                font-size: 13px;
                                border-bottom: 1px solid #dcfce7;
                            ">
                                Total
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                font-size: 13px;
                                border-bottom: 1px solid #dcfce7;
                            ">
                                Status
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                                font-size: 13px;
                                border-bottom: 1px solid #dcfce7;
                            ">
                                Tanggal
                            </th>

                            <th style="
                                padding: 14px;
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

                    <?php foreach ($setoran as $index => $item): ?>

                        <?php

                        $status = strtolower(
                            $item['status'] ?? ''
                        );

                        switch ($status) {

                            case 'diterima':

                                $status_background = '#dcfce7';
                                $status_color = '#166534';
                                $status_text = 'Diterima';

                                break;

                            case 'ditolak':

                                $status_background = '#fee2e2';
                                $status_color = '#b91c1c';
                                $status_text = 'Ditolak';

                                break;

                            case 'menunggu':

                                $status_background = '#fef3c7';
                                $status_color = '#92400e';
                                $status_text = 'Menunggu';

                                break;

                            default:

                                $status_background = '#f3f4f6';
                                $status_color = '#374151';
                                $status_text = ucfirst($status);

                                break;
                        }

                        ?>


                        <tr>


                            <!-- NO -->

                            <td style="
                                padding: 15px 14px;
                                border-bottom: 1px solid #f1f5f9;
                                color: #6b7280;
                            ">
                                <?= $index + 1 ?>
                            </td>


                            <!-- NASABAH -->

                            <td style="
                                padding: 15px 14px;
                                border-bottom: 1px solid #f1f5f9;
                            ">

                                <div style="
                                    display: flex;
                                    align-items: center;
                                    gap: 10px;
                                ">

                                    <div style="
                                        width: 36px;
                                        height: 36px;
                                        border-radius: 50%;
                                        background: #dcfce7;
                                        color: #166534;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        font-weight: 700;
                                        flex-shrink: 0;
                                    ">

                                        <?= strtoupper(
                                            substr(
                                                $item['nama_nasabah'],
                                                0,
                                                1
                                            )
                                        ) ?>

                                    </div>


                                    <div>

                                        <div style="
                                            font-weight: 600;
                                            color: #1f2937;
                                        ">
                                            <?= htmlspecialchars(
                                                $item['nama_nasabah']
                                            ) ?>
                                        </div>

                                        <div style="
                                            font-size: 12px;
                                            color: #9ca3af;
                                        ">
                                            Nasabah
                                        </div>

                                    </div>

                                </div>

                            </td>


                            <!-- JENIS SAMPAH -->

                            <td style="
                                padding: 15px 14px;
                                border-bottom: 1px solid #f1f5f9;
                                color: #374151;
                            ">

                                <?= htmlspecialchars(
                                    $item['nama_sampah']
                                ) ?>

                            </td>


                            <!-- BERAT -->

                            <td style="
                                padding: 15px 14px;
                                border-bottom: 1px solid #f1f5f9;
                                white-space: nowrap;
                                color: #374151;
                            ">

                                <strong>
                                    <?= number_format(
                                        (float) $item['berat'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </strong>

                                kg

                            </td>


                            <!-- TOTAL -->

                            <td style="
                                padding: 15px 14px;
                                border-bottom: 1px solid #f1f5f9;
                                white-space: nowrap;
                            ">

                                <strong style="
                                    color: #166534;
                                ">

                                    Rp
                                    <?= number_format(
                                        (float) $item['total_harga'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </strong>

                            </td>


                            <!-- STATUS -->

                            <td style="
                                padding: 15px 14px;
                                border-bottom: 1px solid #f1f5f9;
                            ">

                                <span style="
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 5px;
                                    padding: 6px 11px;
                                    border-radius: 20px;
                                    background: <?= $status_background ?>;
                                    color: <?= $status_color ?>;
                                    font-size: 12px;
                                    font-weight: 700;
                                ">

                                    <span style="
                                        width: 6px;
                                        height: 6px;
                                        border-radius: 50%;
                                        background: <?= $status_color ?>;
                                    "></span>

                                    <?= htmlspecialchars(
                                        $status_text
                                    ) ?>

                                </span>

                            </td>


                            <!-- TANGGAL -->

                            <td style="
                                padding: 15px 14px;
                                border-bottom: 1px solid #f1f5f9;
                                white-space: nowrap;
                                color: #6b7280;
                                font-size: 13px;
                            ">

                                <?= date(
                                    'd-m-Y',
                                    strtotime(
                                        $item['created_at']
                                    )
                                ) ?>

                                <br>

                                <span style="
                                    color: #9ca3af;
                                ">

                                    <?= date(
                                        'H:i',
                                        strtotime(
                                            $item['created_at']
                                        )
                                    ) ?>

                                </span>

                            </td>


                            <!-- AKSI -->

                            <td style="
                                padding: 15px 14px;
                                border-bottom: 1px solid #f1f5f9;
                                text-align: center;
                            ">

                                <a
                                    href="detail.php?id=<?= (int) $item['id'] ?>"
                                    style="
                                        display: inline-flex;
                                        align-items: center;
                                        gap: 6px;
                                        padding: 8px 13px;
                                        border-radius: 8px;
                                        background: #166534;
                                        color: white;
                                        text-decoration: none;
                                        font-size: 12px;
                                        font-weight: 600;
                                    "
                                >
                                    👁️ Detail
                                </a>

                            </td>

                        </tr>


                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>


    </div>


</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>