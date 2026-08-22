<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('nasabah');


// ======================================================
// CEK LOGIN
// ======================================================

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];


// ======================================================
// VARIABLE
// ======================================================

$setoran = [];
$error = '';


// ======================================================
// AMBIL RIWAYAT SETORAN
// ======================================================

try {

    $stmt = $pdo->prepare("
        SELECT
            setoran.id,
            jenis_sampah.nama_sampah,
            setoran.berat,
            setoran.harga_per_kg,
            setoran.total_harga,
            setoran.status,
            setoran.created_at
        FROM setoran

        INNER JOIN jenis_sampah
            ON setoran.jenis_sampah_id = jenis_sampah.id

        WHERE setoran.user_id = :user_id

        ORDER BY setoran.created_at DESC
    ");

    $stmt->execute([
        'user_id' => $userId
    ]);

    $setoran = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = 'Gagal mengambil riwayat setoran.';

}


// ======================================================
// HEADER & SIDEBAR
// ======================================================

$page_title = 'Riwayat Setor';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

?>


<main class="main-content">


    <!-- ==================================================
         TOPBAR
    ================================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Riwayat Setor
                </h1>

                <p>
                    Lihat seluruh riwayat setoran sampah kamu.
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
                            $_SESSION['nama'] ?? 'N',
                            0,
                            1
                        )
                    ) ?>

                </div>


                <div class="user-details">

                    <div class="user-name">

                        <?= htmlspecialchars(
                            $_SESSION['nama'] ?? 'Nasabah'
                        ) ?>

                    </div>

                    <div class="user-role">
                        Nasabah
                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- ==================================================
         CONTENT
    ================================================== -->

    <div class="riwayat-container">


        <!-- CARD -->

        <div class="riwayat-card">


            <!-- HEADER CARD -->

            <div class="card-header">

                <div>

                    <h2>
                        Riwayat Setoran
                    </h2>

                    <p>
                        Berikut adalah daftar setoran sampah yang pernah kamu ajukan.
                    </p>

                </div>

            </div>



            <!-- ERROR -->

            <?php if ($error !== ''): ?>

                <div class="alert-error">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>



            <!-- EMPTY -->

            <?php if (empty($setoran) && $error === ''): ?>

                <div class="empty-state">

                    <div class="empty-icon">
                        ♻
                    </div>

                    <h3>
                        Belum Ada Riwayat Setoran
                    </h3>

                    <p>
                        Kamu belum memiliki transaksi setoran sampah.
                    </p>

                    <a
                        href="setor.php"
                        class="btn-setor"
                    >
                        + Setor Sampah
                    </a>

                </div>


            <?php elseif (!empty($setoran)): ?>


                <!-- ==================================================
                     TABLE
                ================================================== -->

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    No
                                </th>

                                <th>
                                    Jenis Sampah
                                </th>

                                <th>
                                    Berat
                                </th>

                                <th>
                                    Harga/kg
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Tanggal
                                </th>

                                <th>
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($setoran as $index => $item): ?>

                                <?php

                                $status = strtolower(
                                    $item['status']
                                );

                                $statusText = ucfirst(
                                    $status
                                );

                                ?>


                                <tr>


                                    <!-- NO -->

                                    <td class="number">

                                        <?= $index + 1 ?>

                                    </td>



                                    <!-- JENIS SAMPAH -->

                                    <td>

                                        <div class="jenis-sampah">

                                            <div class="jenis-icon">
                                                ♻
                                            </div>

                                            <div>

                                                <div class="jenis-name">

                                                    <?= htmlspecialchars(
                                                        $item['nama_sampah']
                                                    ) ?>

                                                </div>

                                                <div class="jenis-label">
                                                    Sampah
                                                </div>

                                            </div>

                                        </div>

                                    </td>



                                    <!-- BERAT -->

                                    <td>

                                        <strong>

                                            <?= number_format(
                                                (float) $item['berat'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                            kg

                                        </strong>

                                    </td>



                                    <!-- HARGA -->

                                    <td>

                                        Rp
                                        <?= number_format(
                                            (float) $item['harga_per_kg'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </td>



                                    <!-- TOTAL -->

                                    <td>

                                        <strong class="total-harga">

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

                                    <td>

                                        <span
                                            class="
                                                status
                                                status-<?= htmlspecialchars(
                                                    $status
                                                )
                                            ?>"
                                        >

                                            <?php if ($status === 'diterima'): ?>

                                                ✓

                                            <?php elseif ($status === 'ditolak'): ?>

                                                ✕

                                            <?php else: ?>

                                                ⏳

                                            <?php endif; ?>


                                            <?= htmlspecialchars(
                                                $statusText
                                            ) ?>

                                        </span>

                                    </td>



                                    <!-- TANGGAL -->

                                    <td>

                                        <div class="tanggal">

                                            <?= date(
                                                'd-m-Y',
                                                strtotime(
                                                    $item['created_at']
                                                )
                                            ) ?>

                                            <span>

                                                <?= date(
                                                    'H:i',
                                                    strtotime(
                                                        $item['created_at']
                                                    )
                                                ) ?>

                                            </span>

                                        </div>

                                    </td>



                                    <!-- DETAIL -->

                                    <td>

                                        <a
                                            href="detail-setoran.php?id=<?= (int) $item['id'] ?>"
                                            class="btn-detail"
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


    </div>


</main>



<style>

/* ======================================================
   RIWAYAT SETOR
====================================================== */

.riwayat-container {

    margin-top: 30px;

    width: 100%;

}


.riwayat-card {

    background: #ffffff;

    padding: 28px;

    border-radius: 16px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, .06);

}


.card-header {

    margin-bottom: 25px;

}


.card-header h2 {

    margin: 0 0 6px;

    color: #166534;

    font-size: 22px;

}


.card-header p {

    margin: 0;

    color: #6b7280;

    font-size: 14px;

}



/* ======================================================
   TABLE
====================================================== */

.table-wrapper {

    width: 100%;

    overflow-x: auto;

}


table {

    width: 100%;

    min-width: 900px;

    border-collapse: collapse;

}


thead {

    background: #f0fdf4;

}


th {

    padding: 14px 12px;

    text-align: left;

    color: #166534;

    font-size: 14px;

    font-weight: 700;

    border-bottom: 1px solid #dcfce7;

}


td {

    padding: 16px 12px;

    border-bottom: 1px solid #e5e7eb;

    color: #374151;

    font-size: 14px;

    vertical-align: middle;

}


tbody tr:hover {

    background: #f9fafb;

}


.number {

    color: #6b7280;

    font-weight: 600;

}



/* ======================================================
   JENIS SAMPAH
====================================================== */

.jenis-sampah {

    display: flex;

    align-items: center;

    gap: 10px;

}


.jenis-icon {

    width: 38px;

    height: 38px;

    border-radius: 10px;

    background: #dcfce7;

    color: #166534;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

}


.jenis-name {

    font-weight: 600;

    color: #1f2937;

}


.jenis-label {

    margin-top: 2px;

    font-size: 12px;

    color: #9ca3af;

}



/* ======================================================
   TOTAL
====================================================== */

.total-harga {

    color: #166534;

}



/* ======================================================
   STATUS
====================================================== */

.status {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding: 7px 11px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;

}


.status-menunggu {

    background: #fef3c7;

    color: #92400e;

}


.status-diterima {

    background: #dcfce7;

    color: #166534;

}


.status-ditolak {

    background: #fee2e2;

    color: #b91c1c;

}



/* ======================================================
   TANGGAL
====================================================== */

.tanggal {

    display: flex;

    flex-direction: column;

    gap: 3px;

    white-space: nowrap;

}


.tanggal span {

    color: #9ca3af;

    font-size: 12px;

}



/* ======================================================
   DETAIL
====================================================== */

.btn-detail {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 8px 13px;

    background: #f0fdf4;

    color: #166534;

    border: 1px solid #bbf7d0;

    border-radius: 8px;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    transition: .2s;

}


.btn-detail:hover {

    background: #166534;

    color: #ffffff;

}



/* ======================================================
   ERROR
====================================================== */

.alert-error {

    padding: 15px 18px;

    margin-bottom: 20px;

    background: #fee2e2;

    color: #b91c1c;

    border-radius: 10px;

    font-size: 14px;

}



/* ======================================================
   EMPTY
====================================================== */

.empty-state {

    padding: 50px 20px;

    text-align: center;

    background: #f9fafb;

    border-radius: 12px;

}


.empty-icon {

    width: 60px;

    height: 60px;

    margin: 0 auto 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #dcfce7;

    color: #166534;

    font-size: 28px;

}


.empty-state h3 {

    margin: 0 0 8px;

    color: #374151;

}


.empty-state p {

    margin: 0 0 20px;

    color: #6b7280;

}


.btn-setor {

    display: inline-block;

    padding: 10px 16px;

    background: #166534;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-weight: 600;

}


.btn-setor:hover {

    background: #14532d;

}



/* ======================================================
   RESPONSIVE
====================================================== */

@media (max-width: 768px) {

    .riwayat-card {

        padding: 20px;

    }

    .card-header h2 {

        font-size: 20px;

    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>