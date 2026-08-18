
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

        $sql .= ' WHERE '
            . implode(' AND ', $where);

    }


    $sql .= "
        ORDER BY p.tanggal_pengajuan DESC
    ";


    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    $penarikan =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    $error =
        'Gagal mengambil data penarikan.';

}


$total_penarikan = count($penarikan);


// ========================================
// HEADER & SIDEBAR
// ========================================

$page_title = 'Penarikan';

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
                    Penarikan
                </h1>

                <p>
                    Kelola pengajuan penarikan saldo nasabah.
                </p>

            </div>

        </div>


        <div class="topbar-right">

            <button
                type="button"
                class="notification-btn"
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
             FILTER
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
                    display: flex;
                    gap: 12px;
                    align-items: end;
                    flex-wrap: wrap;
                ">


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
                                min-width: 190px;
                                padding: 11px 12px;
                                border: 1px solid #d1d5db;
                                border-radius: 8px;
                                background: white;
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

            </form>

        </div>



        <!-- ========================================
             JUMLAH DATA
        ======================================== -->

        <div style="
            background: #f0fdf4;
            color: #166534;
            padding: 15px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        ">

            💰
            <?= $total_penarikan ?>
            pengajuan penarikan ditemukan.

        </div>



        <?php if ($error !== ''): ?>


            <div style="
                background: #fee2e2;
                color: #b91c1c;
                padding: 18px;
                border-radius: 10px;
            ">

                <?= htmlspecialchars($error) ?>

            </div>


        <?php elseif (empty($penarikan)): ?>


            <div style="
                background: white;
                padding: 50px 20px;
                border-radius: 16px;
                text-align: center;
                color: #6b7280;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
            ">

                <div style="
                    font-size: 45px;
                    margin-bottom: 15px;
                ">
                    💸
                </div>

                <h3 style="
                    margin: 0 0 8px;
                    color: #374151;
                ">
                    Belum ada penarikan
                </h3>

                <p style="margin: 0;">
                    Belum ada pengajuan penarikan
                    sesuai filter.
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
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                No
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Kode
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Nasabah
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Jumlah
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Metode
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Nomor Tujuan
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Status
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            ">
                                Tanggal
                            </th>

                            <th style="
                                padding: 14px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
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

                        $status =
                            $item['status'];

                        if (
                            $status === 'pending'
                        ) {

                            $status_background =
                                '#fef3c7';

                            $status_color =
                                '#92400e';

                            $status_text =
                                'Pending';

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
                                white-space: nowrap;
                            ">

                                <strong>

                                    <?= htmlspecialchars(
                                        $item[
                                            'kode_penarikan'
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
                                        'nama_nasabah'
                                    ]
                                ) ?>

                            </td>


                            <td style="
                                padding: 14px;
                                border-bottom:
                                    1px solid #e5e7eb;
                            ">

                                <strong>

                                    Rp
                                    <?= number_format(
                                        $item['jumlah'],
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

                                <?= htmlspecialchars(
                                    $item['metode']
                                ) ?>

                            </td>


                            <td style="
                                padding: 14px;
                                border-bottom:
                                    1px solid #e5e7eb;
                            ">

                                <?= htmlspecialchars(
                                    $item[
                                        'nomor_tujuan'
                                    ]
                                ) ?>

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
                                            'tanggal_pengajuan'
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
