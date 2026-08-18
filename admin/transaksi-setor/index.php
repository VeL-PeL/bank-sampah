
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
$tanggal_dari = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';


// ========================================
// VALIDASI STATUS
// ========================================

$status_valid = [
    '',
    'menunggu',
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


// FILTER STATUS

if ($status_filter !== '') {

    $where[] = 's.status = ?';

    $params[] = $status_filter;

}


// FILTER TANGGAL DARI

if ($tanggal_dari !== '') {

    $where[] = 'DATE(s.created_at) >= ?';

    $params[] = $tanggal_dari;

}


// FILTER TANGGAL SAMPAI

if ($tanggal_sampai !== '') {

    $where[] = 'DATE(s.created_at) <= ?';

    $params[] = $tanggal_sampai;

}


$setoran = [];
$error = '';


// ========================================
// AMBIL DATA
// ========================================

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

        $sql .= ' WHERE '
            . implode(' AND ', $where);

    }


    $sql .= "
        ORDER BY s.created_at DESC
    ";


    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    $setoran = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    $error =
        'Gagal mengambil data transaksi setor.';

}


// ========================================
// JUMLAH TRANSAKSI
// ========================================

$total_transaksi = count($setoran);


// ========================================
// HEADER & SIDEBAR
// ========================================

$page_title = 'Transaksi Setor';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

?>


<main class="main-content">

<!-- KEMBALI KE DASHBOARD -->

<div style="margin-top: 25px; margin-bottom: 10px;">

    <a
        href="../dashboard.php"
        style="
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 8px;
            background: white;
            color: #166534;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,.05);
            border: 1px solid #e5e7eb;
        "
    >
        ← Kembali ke Dashboard
    </a>

</div>


    <!-- ========================================
         TOPBAR
    ======================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Transaksi Setor
                </h1>

                <p>
                    Kelola pengajuan setoran sampah dari nasabah.
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



    <!-- ========================================
         CONTENT
    ======================================== -->

    <div style="
        margin-top: 30px;
    ">


        <!-- ========================================
             FILTER CARD
        ======================================== -->

        <div style="
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,.06);
            margin-bottom: 20px;
        ">


            <form
                method="GET"
                action=""
            >

                <div style="
                    display: grid;
                    grid-template-columns:
                        repeat(auto-fit, minmax(180px, 1fr));
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
                                padding: 11px 12px;
                                border: 1px solid #d1d5db;
                                border-radius: 8px;
                                background: white;
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
                                padding: 10px 12px;
                                border: 1px solid #d1d5db;
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
                                padding: 10px 12px;
                                border: 1px solid #d1d5db;
                                border-radius: 8px;
                            "
                        >

                    </div>



                    <!-- TOMBOL -->

                    <div style="
                        display: flex;
                        gap: 8px;
                        flex-wrap: wrap;
                    ">


                        <button
                            type="submit"
                            style="
                                padding: 11px 18px;
                                border: none;
                                border-radius: 8px;
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
                                display: inline-block;
                                padding: 11px 18px;
                                border-radius: 8px;
                                background: #f3f4f6;
                                color: #374151;
                                text-decoration: none;
                                font-weight: 600;
                            "
                        >
                            ↻ Reset
                        </a>

                    </div>


                </div>

            </form>

        </div>



        <!-- ========================================
             INFO JUMLAH
        ======================================== -->

        <div style="
            background: #f0fdf4;
            color: #166534;
            padding: 15px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        ">

            📊
            <?= $total_transaksi ?>
            transaksi ditemukan.

        </div>



        <?php if ($error !== ''): ?>


            <!-- ERROR -->

            <div style="
                background: #fee2e2;
                color: #b91c1c;
                padding: 18px;
                border-radius: 10px;
                margin-bottom: 20px;
            ">

                <?= htmlspecialchars($error) ?>

            </div>


        <?php elseif (empty($setoran)): ?>


            <!-- EMPTY -->

            <div style="
                background: white;
                padding: 50px 20px;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
                text-align: center;
                color: #6b7280;
            ">

                <div style="
                    font-size: 45px;
                    margin-bottom: 15px;
                ">
                    📭
                </div>


                <h3 style="
                    margin: 0 0 8px;
                    color: #374151;
                ">
                    Tidak ada transaksi
                </h3>


                <p style="
                    margin: 0;
                ">
                    Tidak ditemukan transaksi
                    sesuai filter yang dipilih.
                </p>

            </div>


        <?php else: ?>


            <!-- ========================================
                 TABLE
            ======================================== -->

            <div style="
                background: white;
                padding: 24px;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
                overflow-x: auto;
            ">


                <table style="
                    width: 100%;
                    border-collapse: collapse;
                ">

                    <thead>

                        <tr>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                border-bottom: 1px solid #e5e7eb;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                No
                            </th>


                            <th style="
                                padding: 14px;
                                text-align: left;
                                border-bottom: 1px solid #e5e7eb;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Nasabah
                            </th>


                            <th style="
                                padding: 14px;
                                text-align: left;
                                border-bottom: 1px solid #e5e7eb;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Jenis Sampah
                            </th>


                            <th style="
                                padding: 14px;
                                text-align: left;
                                border-bottom: 1px solid #e5e7eb;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Berat
                            </th>


                            <th style="
                                padding: 14px;
                                text-align: left;
                                border-bottom: 1px solid #e5e7eb;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Total
                            </th>


                            <th style="
                                padding: 14px;
                                text-align: left;
                                border-bottom: 1px solid #e5e7eb;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Status
                            </th>


                            <th style="
                                padding: 14px;
                                text-align: left;
                                border-bottom: 1px solid #e5e7eb;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Tanggal
                            </th>


                            <th style="
                                padding: 14px;
                                text-align: left;
                                border-bottom: 1px solid #e5e7eb;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach (
                        $setoran
                        as $index => $item
                    ): ?>


                        <?php

                        $status =
                            $item['status'];

                        if (
                            $status === 'menunggu'
                        ) {

                            $status_background =
                                '#fef3c7';

                            $status_color =
                                '#92400e';

                            $status_text =
                                'Menunggu';

                        } elseif (
                            $status === 'diterima'
                        ) {

                            $status_background =
                                '#dcfce7';

                            $status_color =
                                '#166534';

                            $status_text =
                                'Diterima';

                        } elseif (
                            $status === 'ditolak'
                        ) {

                            $status_background =
                                '#fee2e2';

                            $status_color =
                                '#b91c1c';

                            $status_text =
                                'Ditolak';

                        } else {

                            $status_background =
                                '#e5e7eb';

                            $status_color =
                                '#374151';

                            $status_text =
                                ucfirst($status);

                        }

                        ?>


                        <tr>


                            <td style="
                                padding: 14px;
                                border-bottom:
                                    1px solid #e5e7eb;
                            ">

                                <?= $index + 1 ?>

                            </td>


                            <td style="
                                padding: 14px;
                                border-bottom:
                                    1px solid #e5e7eb;
                            ">

                                <strong>

                                    <?= htmlspecialchars(
                                        $item[
                                            'nama_nasabah'
                                        ]
                                    ) ?>

                                </strong>

                            </td>


                            <td style="
                                padding: 14px;
                                border-bottom:
                                    1px solid #e5e7eb;
                            ">

                                <?= htmlspecialchars(
                                    $item[
                                        'nama_sampah'
                                    ]
                                ) ?>

                            </td>


                            <td style="
                                padding: 14px;
                                border-bottom:
                                    1px solid #e5e7eb;
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
                                padding: 14px;
                                border-bottom:
                                    1px solid #e5e7eb;
                            ">

                                <strong>

                                    Rp
                                    <?= number_format(
                                        $item[
                                            'total_harga'
                                        ],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </strong>

                            </td>


                            <td style="
                                padding: 14px;
                                border-bottom:
                                    1px solid #e5e7eb;
                            ">

                                <span style="
                                    display: inline-block;
                                    padding: 6px 10px;
                                    border-radius: 20px;
                                    background:
                                        <?= $status_background ?>;
                                    color:
                                        <?= $status_color ?>;
                                    font-size: 13px;
                                    font-weight: bold;
                                ">

                                    <?= htmlspecialchars(
                                        $status_text
                                    ) ?>

                                </span>

                            </td>


                            <td style="
                                padding: 14px;
                                border-bottom:
                                    1px solid #e5e7eb;
                                white-space: nowrap;
                            ">

                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime(
                                        $item[
                                            'created_at'
                                        ]
                                    )
                                ) ?>

                            </td>


                            <td style="
                                padding: 14px;
                                border-bottom:
                                    1px solid #e5e7eb;
                            ">

                                <a
                                    href="detail.php?id=<?= (int) $item['id'] ?>"
                                    style="
                                        display: inline-block;
                                        padding: 8px 13px;
                                        border-radius: 7px;
                                        background: #166534;
                                        color: white;
                                        text-decoration: none;
                                        font-size: 13px;
                                        font-weight: 600;
                                    "
                                >
                                    Detail
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

