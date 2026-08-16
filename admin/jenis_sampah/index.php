
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Jenis Sampah';


// ========================================
// SEARCH & FILTER
// ========================================

$search = trim($_GET['search'] ?? '');

$status_filter = $_GET['status'] ?? '';

$where = [];

$params = [];


// SEARCH

if ($search !== '') {

    $where[] = "
        (
            nama_sampah LIKE ?
            OR deskripsi LIKE ?
        )
    ";

    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';

}


// FILTER STATUS

if (
    $status_filter === 'aktif'
    || $status_filter === 'nonaktif'
) {

    $where[] = "status = ?";

    $params[] = $status_filter;

}


// ========================================
// DATA JENIS SAMPAH
// ========================================

$data_sampah = [];

try {

    $sql = "
        SELECT
            id,
            nama_sampah,
            harga_per_kg,
            deskripsi,
            status,
            created_at,
            updated_at
        FROM jenis_sampah
    ";

    if (!empty($where)) {

        $sql .= "
            WHERE
            " . implode(
                " AND ",
                $where
            );

    }

    $sql .= "
        ORDER BY
            created_at DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    $data_sampah =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

} catch (PDOException $e) {

    $data_sampah = [];

}


// ========================================
// STATISTIK
// ========================================

$total_semua = 0;

$total_aktif = 0;

$total_nonaktif = 0;

try {

    $stmt = $pdo->query("
        SELECT
            COUNT(*) AS total,
            SUM(
                CASE
                    WHEN status = 'aktif'
                    THEN 1
                    ELSE 0
                END
            ) AS aktif,
            SUM(
                CASE
                    WHEN status = 'nonaktif'
                    THEN 1
                    ELSE 0
                END
            ) AS nonaktif
        FROM jenis_sampah
    ");

    $statistik =
        $stmt->fetch(PDO::FETCH_ASSOC);

    $total_semua =
        (int) ($statistik['total'] ?? 0);

    $total_aktif =
        (int) ($statistik['aktif'] ?? 0);

    $total_nonaktif =
        (int) ($statistik['nonaktif'] ?? 0);

} catch (PDOException $e) {

    // Tetap gunakan nilai 0.

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
                    Kelola data jenis sampah Bank Sampah.
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
         NOTIFIKASI
    ======================================== -->

    <?php if (
        isset($_GET['success'])
    ): ?>

        <div
            style="
                margin: 20px 0;
                padding: 14px 18px;
                border-radius: 10px;
                background: #dcfce7;
                color: #166534;
                font-weight: 600;
            "
        >

            <?php if (
                $_GET['success'] == '1'
            ): ?>

                ✅ Jenis sampah berhasil ditambahkan.

            <?php elseif (
                $_GET['success'] == '2'
            ): ?>

                ✅ Jenis sampah berhasil diperbarui.

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <?php if (
        isset($_GET['status'])
        && $_GET['status'] === 'success'
    ): ?>

        <div
            style="
                margin: 20px 0;
                padding: 14px 18px;
                border-radius: 10px;
                background: #dcfce7;
                color: #166534;
                font-weight: 600;
            "
        >
            ✅ Status jenis sampah berhasil diubah.
        </div>

    <?php endif; ?>


    <!-- ========================================
         STATISTIK
    ======================================== -->

    <div
        style="
            display: grid;
            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(180px, 1fr)
                );
            gap: 18px;
            margin: 25px 0;
        "
    >


        <!-- TOTAL -->

        <div
            style="
                background: white;
                padding: 22px;
                border-radius: 14px;
                box-shadow:
                    0 8px 25px rgba(0,0,0,.05);
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
                Total Jenis Sampah
            </div>

            <div
                style="
                    font-size: 28px;
                    font-weight: bold;
                    color: #166534;
                "
            >
                <?= $total_semua ?>
            </div>

        </div>


        <!-- AKTIF -->

        <div
            style="
                background: white;
                padding: 22px;
                border-radius: 14px;
                box-shadow:
                    0 8px 25px rgba(0,0,0,.05);
                border-left:
                    5px solid #22c55e;
            "
        >

            <div
                style="
                    color: #6b7280;
                    font-size: 14px;
                    margin-bottom: 8px;
                "
            >
                Jenis Aktif
            </div>

            <div
                style="
                    font-size: 28px;
                    font-weight: bold;
                    color: #166534;
                "
            >
                <?= $total_aktif ?>
            </div>

        </div>


        <!-- NONAKTIF -->

        <div
            style="
                background: white;
                padding: 22px;
                border-radius: 14px;
                box-shadow:
                    0 8px 25px rgba(0,0,0,.05);
                border-left:
                    5px solid #ef4444;
            "
        >

            <div
                style="
                    color: #6b7280;
                    font-size: 14px;
                    margin-bottom: 8px;
                "
            >
                Jenis Nonaktif
            </div>

            <div
                style="
                    font-size: 28px;
                    font-weight: bold;
                    color: #b91c1c;
                "
            >
                <?= $total_nonaktif ?>
            </div>

        </div>

    </div>


    <!-- ========================================
         DATA
    ======================================== -->

    <div
        style="
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow:
                0 10px 30px rgba(0,0,0,.06);
        "
    >


        <!-- HEADER -->

        <div
            style="
                display: flex;
                justify-content:
                    space-between;
                align-items: center;
                gap: 15px;
                flex-wrap: wrap;
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
                    Daftar Jenis Sampah
                </h2>

                <p
                    style="
                        margin: 6px 0 0;
                        color: #6b7280;
                    "
                >
                    Kelola seluruh jenis sampah.
                </p>

            </div>


            <a
                href="tambah.php"
                style="
                    display: inline-block;
                    padding: 11px 18px;
                    border-radius: 10px;
                    background: #166534;
                    color: white;
                    text-decoration: none;
                    font-weight: 600;
                "
            >
                ➕ Tambah Jenis Sampah
            </a>

        </div>


        <!-- ========================================
             FILTER
        ======================================== -->

        <form
            method="GET"
            action=""
            style="
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                padding: 18px;
                margin-bottom: 20px;
                background: #f9fafb;
                border-radius: 12px;
            "
        >

            <!-- SEARCH -->

            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars(
                    $search
                ) ?>"
                placeholder="🔍 Cari jenis sampah..."
                style="
                    flex: 1;
                    min-width: 220px;
                    padding: 11px 13px;
                    border: 1px solid #d1d5db;
                    border-radius: 9px;
                    font-size: 14px;
                "
            >


            <!-- STATUS -->

            <select
                name="status"
                style="
                    min-width: 160px;
                    padding: 11px 13px;
                    border: 1px solid #d1d5db;
                    border-radius: 9px;
                    background: white;
                    font-size: 14px;
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
                    value="aktif"
                    <?= $status_filter === 'aktif'
                        ? 'selected'
                        : '' ?>
                >
                    Aktif
                </option>

                <option
                    value="nonaktif"
                    <?= $status_filter === 'nonaktif'
                        ? 'selected'
                        : '' ?>
                >
                    Nonaktif
                </option>

            </select>


            <!-- BUTTON -->

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
                🔍 Filter
            </button>


            <!-- RESET -->

            <a
                href="index.php"
                style="
                    padding: 11px 18px;
                    border-radius: 9px;
                    background: #e5e7eb;
                    color: #374151;
                    text-decoration: none;
                    font-weight: 600;
                "
            >
                Reset
            </a>

        </form>


        <!-- ========================================
             HASIL
        ======================================== -->

        <div
            style="
                margin-bottom: 15px;
                color: #6b7280;
                font-size: 14px;
            "
        >

            Menampilkan
            <strong>
                <?= count($data_sampah) ?>
            </strong>
            data.

        </div>


        <!-- ========================================
             TABLE
        ======================================== -->

        <?php if (
            !empty($data_sampah)
        ): ?>

            <div
                style="
                    overflow-x: auto;
                "
            >

                <table
                    style="
                        width: 100%;
                        border-collapse:
                            collapse;
                        min-width: 950px;
                    "
                >

                    <thead>

                        <tr>

                            <th
                                style="
                                    padding: 13px;
                                    text-align: left;
                                    background:
                                        #f0fdf4;
                                    color:
                                        #166534;
                                "
                            >
                                No
                            </th>

                            <th
                                style="
                                    padding: 13px;
                                    text-align: left;
                                    background:
                                        #f0fdf4;
                                    color:
                                        #166534;
                                "
                            >
                                Nama Sampah
                            </th>

                            <th
                                style="
                                    padding: 13px;
                                    text-align: left;
                                    background:
                                        #f0fdf4;
                                    color:
                                        #166534;
                                "
                            >
                                Harga / Kg
                            </th>

                            <th
                                style="
                                    padding: 13px;
                                    text-align: left;
                                    background:
                                        #f0fdf4;
                                    color:
                                        #166534;
                                "
                            >
                                Deskripsi
                            </th>

                            <th
                                style="
                                    padding: 13px;
                                    text-align: left;
                                    background:
                                        #f0fdf4;
                                    color:
                                        #166534;
                                "
                            >
                                Status
                            </th>

                            <th
                                style="
                                    padding: 13px;
                                    text-align: left;
                                    background:
                                        #f0fdf4;
                                    color:
                                        #166534;
                                "
                            >
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

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
                                            1px solid
                                            #e5e7eb;
                                    "
                                >
                                    <?= $no++ ?>
                                </td>


                                <!-- NAMA -->

                                <td
                                    style="
                                        padding: 13px;
                                        border-bottom:
                                            1px solid
                                            #e5e7eb;
                                        font-weight:
                                            600;
                                    "
                                >
                                    <?= htmlspecialchars(
                                        $row[
                                            'nama_sampah'
                                        ]
                                    ) ?>
                                </td>


                                <!-- HARGA -->

                                <td
                                    style="
                                        padding: 13px;
                                        border-bottom:
                                            1px solid
                                            #e5e7eb;
                                        white-space:
                                            nowrap;
                                    "
                                >
                                    Rp
                                    <?= number_format(
                                        $row[
                                            'harga_per_kg'
                                        ],
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
                                            1px solid
                                            #e5e7eb;
                                        max-width: 250px;
                                    "
                                >

                                    <?php if (
                                        !empty(
                                            $row[
                                                'deskripsi'
                                            ]
                                        )
                                    ): ?>

                                        <?= htmlspecialchars(
                                            mb_strimwidth(
                                                $row[
                                                    'deskripsi'
                                                ],
                                                0,
                                                80,
                                                '...'
                                            )
                                        ) ?>

                                    <?php else: ?>

                                        <span
                                            style="
                                                color:
                                                    #9ca3af;
                                            "
                                        >
                                            Tidak ada deskripsi
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- STATUS -->

                                <td
                                    style="
                                        padding: 13px;
                                        border-bottom:
                                            1px solid
                                            #e5e7eb;
                                    "
                                >

                                    <?php if (
                                        $row[
                                            'status'
                                        ] === 'aktif'
                                    ): ?>

                                        <span
                                            style="
                                                display:
                                                    inline-block;
                                                padding:
                                                    6px 10px;
                                                border-radius:
                                                    20px;
                                                background:
                                                    #dcfce7;
                                                color:
                                                    #166534;
                                                font-size:
                                                    13px;
                                                font-weight:
                                                    bold;
                                            "
                                        >
                                            🟢 Aktif
                                        </span>

                                    <?php else: ?>

                                        <span
                                            style="
                                                display:
                                                    inline-block;
                                                padding:
                                                    6px 10px;
                                                border-radius:
                                                    20px;
                                                background:
                                                    #fee2e2;
                                                color:
                                                    #b91c1c;
                                                font-size:
                                                    13px;
                                                font-weight:
                                                    bold;
                                            "
                                        >
                                            🔴 Nonaktif
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- AKSI -->

                                <td
                                    style="
                                        padding: 13px;
                                        border-bottom:
                                            1px solid
                                            #e5e7eb;
                                        white-space:
                                            nowrap;
                                    "
                                >

                                    <a
                                        href="detail.php?id=<?= (int) $row['id'] ?>"
                                        style="
                                            display:
                                                inline-block;
                                            padding:
                                                7px 10px;
                                            margin-right:
                                                4px;
                                            border-radius:
                                                8px;
                                            background:
                                                #f0fdf4;
                                            color:
                                                #166534;
                                            text-decoration:
                                                none;
                                            font-size:
                                                13px;
                                            font-weight:
                                                600;
                                        "
                                    >
                                        👁️ Detail
                                    </a>


                                    <a
                                        href="edit.php?id=<?= (int) $row['id'] ?>"
                                        style="
                                            display:
                                                inline-block;
                                            padding:
                                                7px 10px;
                                            margin-right:
                                                4px;
                                            border-radius:
                                                8px;
                                            background:
                                                #dbeafe;
                                            color:
                                                #1d4ed8;
                                            text-decoration:
                                                none;
                                            font-size:
                                                13px;
                                            font-weight:
                                                600;
                                        "
                                    >
                                        ✏️ Edit
                                    </a>


                                    <?php if (
                                        $row[
                                            'status'
                                        ] === 'aktif'
                                    ): ?>

                                        <a
                                            href="status.php?id=<?= (int) $row['id'] ?>"
                                            onclick="return confirm(
                                                'Yakin ingin menonaktifkan jenis sampah ini?'
                                            );"
                                            style="
                                                display:
                                                    inline-block;
                                                padding:
                                                    7px 10px;
                                                border-radius:
                                                    8px;
                                                background:
                                                    #fee2e2;
                                                color:
                                                    #b91c1c;
                                                text-decoration:
                                                    none;
                                                font-size:
                                                    13px;
                                                font-weight:
                                                    600;
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
                                                display:
                                                    inline-block;
                                                padding:
                                                    7px 10px;
                                                border-radius:
                                                    8px;
                                                background:
                                                    #dcfce7;
                                                color:
                                                    #166534;
                                                text-decoration:
                                                    none;
                                                font-size:
                                                    13px;
                                                font-weight:
                                                    600;
                                            "
                                        >
                                            🟢 Aktifkan
                                        </a>

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
                    padding: 40px 20px;
                    text-align: center;
                    color: #6b7280;
                    background: #f9fafb;
                    border-radius: 12px;
                "
            >

                <div
                    style="
                        font-size: 40px;
                        margin-bottom: 10px;
                    "
                >
                    ♻️
                </div>

                <strong
                    style="
                        display: block;
                        color: #374151;
                        margin-bottom: 6px;
                    "
                >
                    Data tidak ditemukan
                </strong>

                <span>
                    Belum ada jenis sampah
                    yang sesuai dengan pencarian.
                </span>

            </div>

        <?php endif; ?>

    </div>

</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>

