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
            " . implode(" AND ", $where);

    }

    $sql .= "
        ORDER BY created_at DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    $data_sampah =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    // Gunakan nilai 0.

}


// ========================================
// HEADER
// ========================================

require_once __DIR__ . '/../../includes/header.php';

require_once __DIR__ . '/../../includes/sidebar.php';

?>

<style>

/* ==================================================
   JENIS SAMPAH - MODERN UI
================================================== */

.jenis-page {
    margin-top: 25px;
}


/* ==================================================
   ALERT
================================================== */

.jenis-alert {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 14px 18px;

    margin-bottom: 20px;

    border-radius: 12px;

    font-size: 14px;
    font-weight: 600;
}

.jenis-alert.success {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}


/* ==================================================
   STATISTIC CARDS
================================================== */

.jenis-stat-grid {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(210px, 1fr)
        );

    gap: 18px;

    margin-bottom: 25px;
}


.jenis-stat-card {

    position: relative;

    background: #ffffff;

    padding: 22px;

    border-radius: 16px;

    border: 1px solid #eef2f7;

    box-shadow:
        0 8px 25px rgba(15, 23, 42, .06);

    overflow: hidden;

    transition:
        transform .2s ease,
        box-shadow .2s ease;
}


.jenis-stat-card:hover {

    transform: translateY(-3px);

    box-shadow:
        0 14px 30px rgba(15, 23, 42, .09);
}


.jenis-stat-card::after {

    content: "";

    position: absolute;

    width: 80px;
    height: 80px;

    right: -25px;
    bottom: -25px;

    border-radius: 50%;

    background: rgba(22, 101, 52, .05);
}


.jenis-stat-top {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 15px;
}


.jenis-stat-icon {

    width: 45px;
    height: 45px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    font-size: 21px;
}


.icon-total {
    background: #ecfdf5;
}

.icon-active {
    background: #dcfce7;
}

.icon-inactive {
    background: #fef2f2;
}


.jenis-stat-label {

    color: #64748b;

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 5px;
}


.jenis-stat-number {

    font-size: 29px;

    font-weight: 800;

    line-height: 1;

    color: #0f172a;
}


.jenis-stat-card.total {
    border-top: 3px solid #166534;
}

.jenis-stat-card.active {
    border-top: 3px solid #22c55e;
}

.jenis-stat-card.inactive {
    border-top: 3px solid #ef4444;
}


/* ==================================================
   MAIN DATA CARD
================================================== */

.jenis-data-card {

    background: #ffffff;

    border-radius: 18px;

    border: 1px solid #eef2f7;

    box-shadow:
        0 10px 30px rgba(15, 23, 42, .06);

    overflow: hidden;
}


/* ==================================================
   DATA HEADER
================================================== */

.jenis-data-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    padding: 25px;

    border-bottom: 1px solid #eef2f7;

    flex-wrap: wrap;
}


.jenis-title-area {

    display: flex;

    align-items: center;

    gap: 14px;
}


.jenis-title-icon {

    width: 46px;
    height: 46px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: #ecfdf5;

    font-size: 22px;
}


.jenis-data-title {

    margin: 0;

    color: #0f172a;

    font-size: 19px;

    font-weight: 750;
}


.jenis-data-subtitle {

    margin: 4px 0 0;

    color: #64748b;

    font-size: 13px;
}


/* ==================================================
   ADD BUTTON
================================================== */

.jenis-add-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 11px 17px;

    border-radius: 10px;

    background: #166534;

    color: white;

    text-decoration: none;

    font-size: 14px;

    font-weight: 700;

    box-shadow:
        0 5px 12px rgba(22, 101, 52, .18);

    transition: .2s ease;
}


.jenis-add-btn:hover {

    background: #14532d;

    transform: translateY(-1px);

}


/* ==================================================
   FILTER AREA
================================================== */

.jenis-filter {

    margin: 20px 25px;

    padding: 16px;

    background: #f8fafc;

    border: 1px solid #eef2f7;

    border-radius: 13px;
}


.jenis-filter-form {

    display: flex;

    align-items: center;

    gap: 10px;

    flex-wrap: wrap;
}


.jenis-search {

    position: relative;

    flex: 1;

    min-width: 230px;
}


.jenis-search-icon {

    position: absolute;

    left: 13px;
    top: 50%;

    transform:
        translateY(-50%);

    color: #94a3b8;

    font-size: 15px;
}


.jenis-search input {

    width: 100%;

    box-sizing: border-box;

    padding: 11px 13px 11px 38px;

    border: 1px solid #dbe2ea;

    border-radius: 9px;

    background: white;

    color: #0f172a;

    outline: none;

    font-size: 14px;

    transition: .2s ease;
}


.jenis-search input:focus {

    border-color: #22c55e;

    box-shadow:
        0 0 0 3px rgba(34, 197, 94, .10);
}


.jenis-filter select {

    min-width: 155px;

    padding: 11px 13px;

    border: 1px solid #dbe2ea;

    border-radius: 9px;

    background: white;

    color: #334155;

    font-size: 14px;

    outline: none;
}


.jenis-filter-btn {

    padding: 11px 17px;

    border: none;

    border-radius: 9px;

    background: #166534;

    color: white;

    font-weight: 700;

    cursor: pointer;

    transition: .2s ease;
}


.jenis-filter-btn:hover {

    background: #14532d;
}


.jenis-reset-btn {

    display: inline-flex;

    align-items: center;

    padding: 11px 16px;

    border-radius: 9px;

    background: #e2e8f0;

    color: #334155;

    text-decoration: none;

    font-size: 14px;

    font-weight: 700;
}


.jenis-reset-btn:hover {

    background: #cbd5e1;
}


/* ==================================================
   RESULT INFO
================================================== */

.jenis-result {

    padding: 0 25px 15px;

    color: #64748b;

    font-size: 13px;
}


.jenis-result strong {

    color: #166534;
}


/* ==================================================
   TABLE
================================================== */

.jenis-table-wrapper {

    overflow-x: auto;

    padding: 0 25px 25px;
}


.jenis-table {

    width: 100%;

    min-width: 950px;

    border-collapse: separate;

    border-spacing: 0;

    font-size: 14px;
}


.jenis-table thead th {

    padding: 13px 14px;

    text-align: left;

    background: #f0fdf4;

    color: #166534;

    font-size: 12px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .3px;

    border-bottom: 1px solid #dcfce7;

}


.jenis-table thead th:first-child {

    border-radius: 9px 0 0 9px;
}


.jenis-table thead th:last-child {

    border-radius: 0 9px 9px 0;
}


.jenis-table tbody td {

    padding: 15px 14px;

    border-bottom: 1px solid #f1f5f9;

    color: #334155;

    vertical-align: middle;
}


.jenis-table tbody tr {

    transition: .15s ease;
}


.jenis-table tbody tr:hover {

    background: #f8fffa;
}


.jenis-no {

    width: 45px;

    color: #94a3b8 !important;

    font-weight: 700;
}


.jenis-name {

    color: #0f172a !important;

    font-weight: 700;
}


.jenis-price {

    color: #166534 !important;

    font-weight: 700;

    white-space: nowrap;
}


.jenis-description {

    max-width: 260px;

    color: #64748b !important;

    line-height: 1.5;
}


/* ==================================================
   STATUS BADGE
================================================== */

.jenis-status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 800;
}


.jenis-status.active {

    background: #dcfce7;

    color: #166534;
}


.jenis-status.inactive {

    background: #fee2e2;

    color: #b91c1c;
}


/* ==================================================
   ACTION BUTTON
================================================== */

.jenis-actions {

    display: flex;

    align-items: center;

    gap: 6px;

    white-space: nowrap;
}


.jenis-action {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding: 7px 9px;

    border-radius: 8px;

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;

    transition: .15s ease;
}


.jenis-action.detail {

    background: #f0fdf4;

    color: #166534;
}


.jenis-action.detail:hover {

    background: #dcfce7;
}


.jenis-action.edit {

    background: #eff6ff;

    color: #1d4ed8;
}


.jenis-action.edit:hover {

    background: #dbeafe;
}


.jenis-action.disable {

    background: #fef2f2;

    color: #b91c1c;
}


.jenis-action.disable:hover {

    background: #fee2e2;
}


.jenis-action.enable {

    background: #ecfdf5;

    color: #047857;
}


.jenis-action.enable:hover {

    background: #d1fae5;
}


/* ==================================================
   EMPTY STATE
================================================== */

.jenis-empty {

    margin: 0 25px 25px;

    padding: 50px 20px;

    text-align: center;

    background: #f8fafc;

    border: 1px dashed #cbd5e1;

    border-radius: 14px;

    color: #64748b;
}


.jenis-empty-icon {

    width: 65px;
    height: 65px;

    margin: 0 auto 14px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #ecfdf5;

    font-size: 30px;
}


.jenis-empty-title {

    display: block;

    margin-bottom: 5px;

    color: #334155;

    font-size: 15px;

    font-weight: 750;
}


/* ==================================================
   RESPONSIVE
================================================== */

@media (max-width: 700px) {

    .jenis-page {
        margin-top: 15px;
    }

    .jenis-data-header {
        padding: 20px;
    }

    .jenis-add-btn {
        width: 100%;
        justify-content: center;
    }

    .jenis-filter {
        margin: 15px;
    }

    .jenis-filter-form {
        flex-direction: column;
        align-items: stretch;
    }

    .jenis-search {
        min-width: 100%;
    }

    .jenis-filter select,
    .jenis-filter-btn,
    .jenis-reset-btn {
        width: 100%;
        box-sizing: border-box;
        justify-content: center;
        text-align: center;
    }

    .jenis-result {
        padding-left: 15px;
        padding-right: 15px;
    }

    .jenis-table-wrapper {
        padding-left: 15px;
        padding-right: 15px;
    }

}

</style>


<main class="main-content">


    <!-- ==================================================
         TOPBAR
    ================================================== -->

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

    <div class="jenis-page">


        <!-- ==================================================
             NOTIFIKASI
        ================================================== -->

        <?php if (
            isset($_GET['success'])
        ): ?>

            <div class="jenis-alert success">

                <span>✅</span>

                <span>

                    <?php if (
                        $_GET['success'] == '1'
                    ): ?>

                        Jenis sampah berhasil ditambahkan.

                    <?php elseif (
                        $_GET['success'] == '2'
                    ): ?>

                        Jenis sampah berhasil diperbarui.

                    <?php endif; ?>

                </span>

            </div>

        <?php endif; ?>


        <?php if (
            isset($_GET['status'])
            && $_GET['status'] === 'success'
        ): ?>

            <div class="jenis-alert success">

                <span>✅</span>

                <span>
                    Status jenis sampah berhasil diubah.
                </span>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             STATISTIK
        ================================================== -->

        <div class="jenis-stat-grid">


            <!-- TOTAL -->

            <div class="jenis-stat-card total">

                <div class="jenis-stat-top">

                    <div>

                        <div class="jenis-stat-label">
                            Total Jenis Sampah
                        </div>

                        <div class="jenis-stat-number">
                            <?= $total_semua ?>
                        </div>

                    </div>

                    <div class="jenis-stat-icon icon-total">
                        ♻️
                    </div>

                </div>

            </div>


            <!-- AKTIF -->

            <div class="jenis-stat-card active">

                <div class="jenis-stat-top">

                    <div>

                        <div class="jenis-stat-label">
                            Jenis Aktif
                        </div>

                        <div class="jenis-stat-number">
                            <?= $total_aktif ?>
                        </div>

                    </div>

                    <div class="jenis-stat-icon icon-active">
                        🟢
                    </div>

                </div>

            </div>


            <!-- NONAKTIF -->

            <div class="jenis-stat-card inactive">

                <div class="jenis-stat-top">

                    <div>

                        <div class="jenis-stat-label">
                            Jenis Nonaktif
                        </div>

                        <div
                            class="jenis-stat-number"
                            style="color:#b91c1c;"
                        >
                            <?= $total_nonaktif ?>
                        </div>

                    </div>

                    <div class="jenis-stat-icon icon-inactive">
                        🔴
                    </div>

                </div>

            </div>

        </div>


        <!-- ==================================================
             DATA CARD
        ================================================== -->

        <div class="jenis-data-card">


            <!-- HEADER -->

            <div class="jenis-data-header">

                <div class="jenis-title-area">

                    <div class="jenis-title-icon">
                        ♻️
                    </div>

                    <div>

                        <h2 class="jenis-data-title">
                            Daftar Jenis Sampah
                        </h2>

                        <p class="jenis-data-subtitle">
                            Kelola seluruh jenis sampah dan harga per kilogram.
                        </p>

                    </div>

                </div>


                <a
                    href="tambah.php"
                    class="jenis-add-btn"
                >
                    <span>＋</span>
                    Tambah Jenis Sampah
                </a>

            </div>


            <!-- ==================================================
                 FILTER
            ================================================== -->

            <div class="jenis-filter">

                <form
                    method="GET"
                    action=""
                    class="jenis-filter-form"
                >


                    <!-- SEARCH -->

                    <div class="jenis-search">

                        <span class="jenis-search-icon">
                            🔍
                        </span>

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars(
                                $search
                            ) ?>"
                            placeholder="Cari nama atau deskripsi sampah..."
                        >

                    </div>


                    <!-- STATUS -->

                    <select name="status">

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


                    <!-- FILTER -->

                    <button
                        type="submit"
                        class="jenis-filter-btn"
                    >
                        🔎 Filter
                    </button>


                    <!-- RESET -->

                    <a
                        href="index.php"
                        class="jenis-reset-btn"
                    >
                        ↻ Reset
                    </a>

                </form>

            </div>


            <!-- ==================================================
                 HASIL
            ================================================== -->

            <div class="jenis-result">

                Menampilkan

                <strong>
                    <?= count($data_sampah) ?>
                </strong>

                data jenis sampah.

            </div>


            <!-- ==================================================
                 TABLE
            ================================================== -->

            <?php if (
                !empty($data_sampah)
            ): ?>

                <div class="jenis-table-wrapper">

                    <table class="jenis-table">

                        <thead>

                            <tr>

                                <th>
                                    No
                                </th>

                                <th>
                                    Nama Sampah
                                </th>

                                <th>
                                    Harga / Kg
                                </th>

                                <th>
                                    Deskripsi
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
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

                                    <td class="jenis-no">
                                        <?= $no++ ?>
                                    </td>


                                    <!-- NAMA -->

                                    <td class="jenis-name">

                                        <?= htmlspecialchars(
                                            $row['nama_sampah']
                                        ) ?>

                                    </td>


                                    <!-- HARGA -->

                                    <td class="jenis-price">

                                        Rp

                                        <?= number_format(
                                            $row['harga_per_kg'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </td>


                                    <!-- DESKRIPSI -->

                                    <td class="jenis-description">

                                        <?php if (
                                            !empty(
                                                $row['deskripsi']
                                            )
                                        ): ?>

                                            <?= htmlspecialchars(
                                                mb_strimwidth(
                                                    $row['deskripsi'],
                                                    0,
                                                    80,
                                                    '...'
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            <span
                                                style="
                                                    color:#94a3b8;
                                                    font-style:italic;
                                                "
                                            >
                                                Tidak ada deskripsi
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <?php if (
                                            $row['status']
                                            === 'aktif'
                                        ): ?>

                                            <span
                                                class="jenis-status active"
                                            >
                                                ● Aktif
                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="jenis-status inactive"
                                            >
                                                ● Nonaktif
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- AKSI -->

                                    <td>

                                        <div class="jenis-actions">


                                            <!-- DETAIL -->

                                            <a
                                                href="detail.php?id=<?= (int) $row['id'] ?>"
                                                class="jenis-action detail"
                                                title="Lihat detail"
                                            >
                                                👁 Detail
                                            </a>


                                            <!-- EDIT -->

                                            <a
                                                href="edit.php?id=<?= (int) $row['id'] ?>"
                                                class="jenis-action edit"
                                                title="Edit data"
                                            >
                                                ✏ Edit
                                            </a>


                                            <!-- STATUS -->

                                            <?php if (
                                                $row['status']
                                                === 'aktif'
                                            ): ?>

                                                <a
                                                    href="status.php?id=<?= (int) $row['id'] ?>"
                                                    class="jenis-action disable"
                                                    onclick="return confirm(
                                                        'Yakin ingin menonaktifkan jenis sampah ini?'
                                                    );"
                                                    title="Nonaktifkan"
                                                >
                                                    🔴 Nonaktif
                                                </a>

                                            <?php else: ?>

                                                <a
                                                    href="status.php?id=<?= (int) $row['id'] ?>"
                                                    class="jenis-action enable"
                                                    onclick="return confirm(
                                                        'Yakin ingin mengaktifkan jenis sampah ini?'
                                                    );"
                                                    title="Aktifkan"
                                                >
                                                    🟢 Aktifkan
                                                </a>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>


                <!-- ==================================================
                     EMPTY STATE
                ================================================== -->

                <div class="jenis-empty">

                    <div class="jenis-empty-icon">
                        ♻️
                    </div>

                    <strong class="jenis-empty-title">
                        Data tidak ditemukan
                    </strong>

                    <span>
                        Belum ada jenis sampah yang sesuai
                        dengan pencarian atau filter.
                    </span>

                </div>

            <?php endif; ?>

        </div>

    </div>

</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>