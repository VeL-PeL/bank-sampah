
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Jenis Sampah';


// ========================================
// AMBIL DATA JENIS SAMPAH
// ========================================

$data_sampah = [];
$error = '';

try {

    $stmt = $pdo->query("
        SELECT
            id,
            nama_sampah,
            harga_per_kg,
            deskripsi,
            status,
            created_at
        FROM jenis_sampah
        ORDER BY id DESC
    ");

    $data_sampah =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error =
        'Gagal mengambil data jenis sampah.';

}


// ========================================
// HEADER
// ========================================

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
                    Jenis Sampah
                </h1>

                <p>
                    Kelola jenis sampah dan harga per kilogram.
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


    <!-- ========================================
         KONTEN
    ======================================== -->

    <div
        style="
            max-width: 1200px;
            margin: 30px auto;
        "
    >


        <!-- ========================================
             HEADER CARD
        ======================================== -->

        <div
            style="
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow:
                    0 10px 30px rgba(0,0,0,.06);
                margin-bottom: 25px;
            "
        >

            <div
                style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 15px;
                    flex-wrap: wrap;
                "
            >

                <div>

                    <h2
                        style="
                            margin: 0 0 6px 0;
                            color: #166534;
                        "
                    >
                        Data Jenis Sampah
                    </h2>

                    <p
                        style="
                            margin: 0;
                            color: #6b7280;
                        "
                    >
                        Daftar jenis sampah yang tersedia di Bank Sampah.
                    </p>

                </div>


                <a
                    href="tambah.php"
                    style="
                        display: inline-block;
                        padding: 12px 18px;
                        border-radius: 10px;
                        background: #166534;
                        color: white;
                        text-decoration: none;
                        font-weight: 600;
                    "
                >
                    + Tambah Jenis Sampah
                </a>

            </div>

        </div>


        <!-- ========================================
             JUMLAH DATA
        ======================================== -->

        <div
            style="
                background: white;
                padding: 20px;
                border-radius: 15px;
                box-shadow:
                    0 10px 30px rgba(0,0,0,.06);
                margin-bottom: 25px;
            "
        >

            <div
                style="
                    color: #6b7280;
                    font-size: 14px;
                "
            >
                Total Jenis Sampah
            </div>

            <strong
                style="
                    display: block;
                    margin-top: 5px;
                    font-size: 28px;
                    color: #166534;
                "
            >
                <?= number_format(
                    count($data_sampah),
                    0,
                    ',',
                    '.'
                ) ?>
            </strong>

        </div>


        <!-- ========================================
             ERROR
        ======================================== -->

        <?php if ($error !== ''): ?>

            <div
                style="
                    padding: 15px 20px;
                    margin-bottom: 25px;
                    border-radius: 10px;
                    background: #fee2e2;
                    color: #991b1b;
                "
            >

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <!-- ========================================
             TABEL
        ======================================== -->

        <div
            style="
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow:
                    0 10px 30px rgba(0,0,0,.06);
                overflow-x: auto;
            "
        >

            <table
                style="
                    width: 100%;
                    border-collapse: collapse;
                    min-width: 850px;
                "
            >

                <thead>

                    <tr>

                        <th
                            style="
                                padding: 13px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            No
                        </th>

                        <th
                            style="
                                padding: 13px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Nama Sampah
                        </th>

                        <th
                            style="
                                padding: 13px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Harga / Kg
                        </th>

                        <th
                            style="
                                padding: 13px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Deskripsi
                        </th>

                        <th
                            style="
                                padding: 13px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Status
                        </th>

                        <th
                            style="
                                padding: 13px;
                                text-align: left;
                                background: #f0fdf4;
                                color: #166534;
                            "
                        >
                            Terdaftar
                        </th>

                        <th
    style="
        padding: 13px;
        text-align: left;
        background: #f0fdf4;
        color: #166534;
    "
>
    Aksi
</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (
                        !empty($data_sampah)
                    ): ?>

                        <?php $no = 1; ?>


                        <?php foreach (
                            $data_sampah
                            as $row
                        ): ?>

                            <tr>

                                <!-- NO -->

                                <td
                                    style="
                                        padding: 13px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                    "
                                >
                                    <?= $no++ ?>
                                </td>


                                <!-- NAMA -->

                                <td
                                    style="
                                        padding: 13px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        font-weight: 600;
                                    "
                                >
                                    <?= htmlspecialchars(
                                        $row['nama_sampah']
                                    ) ?>
                                </td>


                                <!-- HARGA -->

                                <td
                                    style="
                                        padding: 13px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        white-space: nowrap;
                                        color: #166534;
                                        font-weight: 600;
                                    "
                                >
                                    Rp
                                    <?= number_format(
                                        $row['harga_per_kg'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>


                                <!-- DESKRIPSI -->

                                <td
                                    style="
                                        padding: 13px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        color: #6b7280;
                                    "
                                >

                                    <?php if (
                                        !empty(
                                            $row['deskripsi']
                                        )
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $row['deskripsi']
                                        ) ?>

                                    <?php else: ?>

                                        <span
                                            style="
                                                color: #9ca3af;
                                            "
                                        >
                                            -
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- STATUS -->

                                <td
                                    style="
                                        padding: 13px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                    "
                                >

                                    <?php if (
                                        $row['status']
                                        === 'aktif'
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
                                            Aktif
                                        </span>

                                    <?php else: ?>

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
                                            Nonaktif
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- TANGGAL -->

                                <td
                                    style="
                                        padding: 13px;
                                        border-bottom:
                                            1px solid #e5e7eb;
                                        white-space: nowrap;
                                        color: #6b7280;
                                    "
                                >

                                    <?= date(
                                        'd-m-Y H:i',
                                        strtotime(
                                            $row['created_at']
                                        )
                                    ) ?>

                                </td>

                                <td
    style="
        padding: 13px;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    "
>
    <a
        href="edit.php?id=<?= (int) $row['id'] ?>"
        style="
            display: inline-block;
            padding: 7px 12px;
            border-radius: 8px;
            background: #dbeafe;
            color: #1d4ed8;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        "
    >
        ✏️ Edit
    </a>

    <a
        href="detail.php?id=<?= (int) $row['id'] ?>"
        style="
            display: inline-block;
            padding: 7px 12px;
            border-radius: 8px;
            background: #dcfce7;
            color: #166534;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        "
    >
        👁️ Detail
    </a>

<?php if ($row['status'] === 'aktif'): ?>

    <a
        href="status.php?id=<?= (int) $row['id'] ?>"
        onclick="return confirm(
            'Yakin ingin menonaktifkan jenis sampah ini?'
        );"
        style="
            display: inline-block;
            padding: 7px 12px;
            border-radius: 8px;
            background: #fee2e2;
            color: #b91c1c;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-right: 5px;
        "
    >
        🔴 Nonaktifkan
    </a>

<?php else: ?>

    <a
        href="status.php?id=<?= (int) $row['id'] ?>"
        onclick="return confirm(
            'Yakin ingin mengaktifkan jenis sampah ini?'
        );"
        style="
            display: inline-block;
            padding: 7px 12px;
            border-radius: 8px;
            background: #dcfce7;
            color: #166534;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-right: 5px;
        "
    >
        🟢 Aktifkan
    </a>

<?php endif; ?>


</td>

                            </tr>

                        <?php endforeach; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                style="
                                    padding: 40px;
                                    text-align: center;
                                    color: #6b7280;
                                    background: #f9fafb;
                                "
                            >
                                Belum ada data jenis sampah.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>
