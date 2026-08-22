<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Nasabah';


// ========================================
// AMBIL DATA NASABAH
// ========================================

$data_nasabah = [];

$error = '';

try {

    $stmt = $pdo->query("
        SELECT
            id,
            nama,
            email,
            role,
            status,
            created_at
        FROM users
        WHERE role = 'nasabah'
        ORDER BY id DESC
    ");

    $data_nasabah = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = 'Gagal mengambil data nasabah.';

}


// ========================================
// HEADER & SIDEBAR
// ========================================

require_once __DIR__ . '/../../includes/header.php';

require_once __DIR__ . '/../../includes/sidebar.php';

?>

<style>

/* ========================================
   NASABAH PAGE
======================================== */

.nasabah-page {
    margin-top: 25px;
}


/* ========================================
   PAGE HEADER
======================================== */

.nasabah-header {
    background: #ffffff;
    border-radius: 18px;
    padding: 25px;
    margin-bottom: 22px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
}

.nasabah-header-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.nasabah-title h2 {
    margin: 0;
    color: #166534;
    font-size: 22px;
}

.nasabah-title p {
    margin: 7px 0 0;
    color: #6b7280;
    font-size: 14px;
}


/* ========================================
   HEADER ACTION
======================================== */

.nasabah-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.total-nasabah {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 11px 16px;
    background: #f0fdf4;
    border: 1px solid #dcfce7;
    border-radius: 10px;
    color: #166534;
    font-size: 14px;
    font-weight: 600;
}

.total-nasabah-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: #dcfce7;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-tambah {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 11px 17px;
    background: #166534;
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: .2s ease;
}

.btn-tambah:hover {
    background: #14532d;
    transform: translateY(-1px);
}


/* ========================================
   ERROR
======================================== */

.nasabah-error {
    padding: 15px 18px;
    margin-bottom: 20px;
    border-radius: 12px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
    font-size: 14px;
}


/* ========================================
   TABLE CARD
======================================== */

.nasabah-table-card {
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
    overflow: hidden;
}

.table-header {
    padding: 20px 25px;
    border-bottom: 1px solid #f1f5f9;
}

.table-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 17px;
}

.table-header p {
    margin: 5px 0 0;
    color: #9ca3af;
    font-size: 13px;
}

.table-wrapper {
    overflow-x: auto;
}

.nasabah-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.nasabah-table th {
    padding: 14px 16px;
    text-align: left;
    background: #f0fdf4;
    color: #166534;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.nasabah-table td {
    padding: 15px 16px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
    color: #374151;
}

.nasabah-table tbody tr {
    transition: .15s ease;
}

.nasabah-table tbody tr:hover {
    background: #fafffb;
}

.nasabah-table tbody tr:last-child td {
    border-bottom: none;
}


/* ========================================
   USER NAME
======================================== */

.user-cell {
    display: flex;
    align-items: center;
    gap: 11px;
}

.nasabah-avatar {
    width: 38px;
    height: 38px;
    min-width: 38px;
    border-radius: 50%;
    background: #dcfce7;
    color: #166534;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 15px;
}

.user-name-cell {
    font-weight: 600;
    color: #1f2937;
}


/* ========================================
   EMAIL
======================================== */

.email-cell {
    color: #64748b;
}


/* ========================================
   ROLE
======================================== */

.role-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
}


/* ========================================
   STATUS
======================================== */

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.status-aktif {
    background: #dcfce7;
    color: #166534;
}

.status-aktif .status-dot {
    background: #16a34a;
}

.status-nonaktif {
    background: #fee2e2;
    color: #b91c1c;
}

.status-nonaktif .status-dot {
    background: #dc2626;
}


/* ========================================
   DATE
======================================== */

.date-cell {
    white-space: nowrap;
    color: #64748b;
}


/* ========================================
   ACTION
======================================== */

.action-buttons {
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 10px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: .2s ease;
}

.btn-edit {
    background: #eff6ff;
    color: #1d4ed8;
}

.btn-edit:hover {
    background: #dbeafe;
}

.btn-detail {
    background: #f0fdf4;
    color: #166534;
}

.btn-detail:hover {
    background: #dcfce7;
}

.btn-nonaktif {
    background: #fef2f2;
    color: #b91c1c;
}

.btn-nonaktif:hover {
    background: #fee2e2;
}

.nonaktif-label {
    display: inline-block;
    padding: 7px 10px;
    border-radius: 8px;
    background: #f3f4f6;
    color: #9ca3af;
    font-size: 12px;
    font-weight: 600;
}


/* ========================================
   EMPTY
======================================== */

.empty-state {
    padding: 55px 20px;
    text-align: center;
}

.empty-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background: #f0fdf4;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
}

.empty-state h3 {
    margin: 0;
    color: #374151;
    font-size: 16px;
}

.empty-state p {
    margin: 7px 0 0;
    color: #9ca3af;
    font-size: 13px;
}


/* ========================================
   RESPONSIVE
======================================== */

@media (max-width: 700px) {

    .nasabah-page {
        margin-top: 18px;
    }

    .nasabah-header {
        padding: 20px;
    }

    .nasabah-header-inner {
        align-items: stretch;
    }

    .nasabah-actions {
        width: 100%;
    }

    .total-nasabah,
    .btn-tambah {
        flex: 1;
        justify-content: center;
    }

    .table-header {
        padding: 18px 20px;
    }

}

</style>


<main class="main-content">


    <!-- ========================================
         TOPBAR
    ======================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Nasabah
                </h1>

                <p>
                    Kelola dan pantau data nasabah Bank Sampah.
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
                            $_SESSION['nama'] ?? 'Admin'
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

    <div class="nasabah-page">


        <!-- ========================================
             HEADER CARD
        ======================================== -->

        <div class="nasabah-header">

            <div class="nasabah-header-inner">


                <div class="nasabah-title">

                    <h2>
                        Data Nasabah
                    </h2>

                    <p>
                        Daftar seluruh nasabah yang terdaftar
                        dalam sistem.
                    </p>

                </div>


                <div class="nasabah-actions">


                    <div class="total-nasabah">

                        <div class="total-nasabah-icon">
                            👥
                        </div>

                        <span>
                            <?= number_format(
                                count($data_nasabah),
                                0,
                                ',',
                                '.'
                            ) ?>
                            Nasabah
                        </span>

                    </div>


                    <a
                        href="tambah.php"
                        class="btn-tambah"
                    >
                        <span>＋</span>
                        Tambah Nasabah
                    </a>

                </div>


            </div>

        </div>


        <!-- ========================================
             ERROR
        ======================================== -->

        <?php if ($error !== ''): ?>

            <div class="nasabah-error">

                ⚠️
                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <!-- ========================================
             TABLE
        ======================================== -->

        <div class="nasabah-table-card">


            <div class="table-header">

                <h3>
                    Daftar Nasabah
                </h3>

                <p>
                    Data nasabah yang terdaftar pada sistem.
                </p>

            </div>


            <?php if (!empty($data_nasabah)): ?>


                <div class="table-wrapper">

                    <table class="nasabah-table">


                        <thead>

                            <tr>

                                <th>
                                    No
                                </th>

                                <th>
                                    Nasabah
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Role
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Terdaftar
                                </th>

                                <th>
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php $no = 1; ?>


                            <?php foreach (
                                $data_nasabah
                                as $nasabah
                            ): ?>


                                <tr>


                                    <!-- NO -->

                                    <td>

                                        <?= $no++ ?>

                                    </td>


                                    <!-- NASABAH -->

                                    <td>

                                        <div class="user-cell">

                                            <div class="nasabah-avatar">

                                                <?= strtoupper(
                                                    substr(
                                                        $nasabah['nama'],
                                                        0,
                                                        1
                                                    )
                                                ) ?>

                                            </div>


                                            <div>

                                                <div class="user-name-cell">

                                                    <?= htmlspecialchars(
                                                        $nasabah['nama']
                                                    ) ?>

                                                </div>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- EMAIL -->

                                    <td>

                                        <span class="email-cell">

                                            <?= htmlspecialchars(
                                                $nasabah['email']
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- ROLE -->

                                    <td>

                                        <span class="role-badge">

                                            <?= ucfirst(
                                                htmlspecialchars(
                                                    $nasabah['role']
                                                )
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- STATUS -->

                                    <td>


                                        <?php if (
                                            $nasabah['status'] === 'aktif'
                                        ): ?>


                                            <span
                                                class="
                                                    status-badge
                                                    status-aktif
                                                "
                                            >

                                                <span
                                                    class="status-dot"
                                                ></span>

                                                Aktif

                                            </span>


                                        <?php else: ?>


                                            <span
                                                class="
                                                    status-badge
                                                    status-nonaktif
                                                "
                                            >

                                                <span
                                                    class="status-dot"
                                                ></span>

                                                Nonaktif

                                            </span>


                                        <?php endif; ?>


                                    </td>


                                    <!-- TANGGAL -->

                                    <td>

                                        <span class="date-cell">

                                            <?= date(
                                                'd-m-Y',
                                                strtotime(
                                                    $nasabah['created_at']
                                                )
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- AKSI -->

                                    <td>


                                        <div class="action-buttons">


                                            <!-- EDIT -->

                                            <a
                                                href="edit.php?id=<?= (int) $nasabah['id'] ?>"
                                                class="
                                                    btn-action
                                                    btn-edit
                                                "
                                            >
                                                ✏️ Edit
                                            </a>


                                            <!-- DETAIL -->

                                            <a
                                                href="detail.php?id=<?= (int) $nasabah['id'] ?>"
                                                class="
                                                    btn-action
                                                    btn-detail
                                                "
                                            >
                                                👁️ Detail
                                            </a>


                                            <!-- NONAKTIF -->

                                            <?php if (
                                                $nasabah['status'] === 'aktif'
                                            ): ?>


                                                <a
                                                    href="nonaktif.php?id=<?= (int) $nasabah['id'] ?>"
                                                    class="
                                                        btn-action
                                                        btn-nonaktif
                                                    "
                                                    onclick="
                                                        return confirm(
                                                            'Yakin ingin menonaktifkan nasabah ini?'
                                                        );
                                                    "
                                                >
                                                    🚫 Nonaktifkan
                                                </a>


                                            <?php else: ?>


                                                <span
                                                    class="nonaktif-label"
                                                >
                                                    Nonaktif
                                                </span>


                                            <?php endif; ?>


                                        </div>


                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>


                    </table>

                </div>


            <?php else: ?>


                <!-- EMPTY -->

                <div class="empty-state">

                    <div class="empty-icon">
                        👥
                    </div>

                    <h3>
                        Belum Ada Nasabah
                    </h3>

                    <p>
                        Belum ada data nasabah yang terdaftar.
                    </p>

                </div>


            <?php endif; ?>


        </div>


    </div>


</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>