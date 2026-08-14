```php
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Penarikan';

$penarikan = [];
$error = '';


// ========================================
// AMBIL DATA PENARIKAN
// ========================================

try {

    $stmt = $pdo->query("
        SELECT
            p.id,
            p.kode_penarikan,
            p.nasabah_id,
            p.jumlah,
            p.metode,
            p.nomor_tujuan,
            p.status,
            p.created_at,
            n.user_id,
            u.nama
        FROM penarikan p
        INNER JOIN nasabah n
            ON p.nasabah_id = n.id
        INNER JOIN users u
            ON n.user_id = u.id
        ORDER BY p.created_at DESC
    ");

    $penarikan = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = 'Gagal mengambil data penarikan.';

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
                    Penarikan
                </h1>

                <p>
                    Kelola pengajuan penarikan saldo nasabah.
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
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        "
    >

        <h2
            style="
                margin-top: 0;
                color: #166534;
            "
        >
            Pengajuan Penarikan
        </h2>


        <?php if ($error !== ''): ?>

            <div
                style="
                    background: #fee2e2;
                    color: #991b1b;
                    padding: 15px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                "
            >

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <?php if (empty($penarikan)): ?>

            <div
                style="
                    padding: 30px;
                    text-align: center;
                    color: #6b7280;
                    background: #f9fafb;
                    border-radius: 10px;
                "
            >

                Belum ada pengajuan penarikan.

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
                                    border-bottom: 1px solid #e5e7eb;
                                    color: #166534;
                                "
                            >
                                No
                            </th>

                            <th
                                style="
                                    padding: 12px;
                                    text-align: left;
                                    border-bottom: 1px solid #e5e7eb;
                                    color: #166534;
                                "
                            >
                                Kode
                            </th>

                            <th
                                style="
                                    padding: 12px;
                                    text-align: left;
                                    border-bottom: 1px solid #e5e7eb;
                                    color: #166534;
                                "
                            >
                                Nasabah
                            </th>

                            <th
                                style="
                                    padding: 12px;
                                    text-align: left;
                                    border-bottom: 1px solid #e5e7eb;
                                    color: #166534;
                                "
                            >
                                Jumlah
                            </th>

                            <th
                                style="
                                    padding: 12px;
                                    text-align: left;
                                    border-bottom: 1px solid #e5e7eb;
                                    color: #166534;
                                "
                            >
                                Metode
                            </th>

                            <th
                                style="
                                    padding: 12px;
                                    text-align: left;
                                    border-bottom: 1px solid #e5e7eb;
                                    color: #166534;
                                "
                            >
                                Nomor Tujuan
                            </th>

                            <th
                                style="
                                    padding: 12px;
                                    text-align: left;
                                    border-bottom: 1px solid #e5e7eb;
                                    color: #166534;
                                "
                            >
                                Status
                            </th>

                            <th
                                style="
                                    padding: 12px;
                                    text-align: left;
                                    border-bottom: 1px solid #e5e7eb;
                                    color: #166534;
                                "
                            >
                                Tanggal
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($penarikan as $index => $item): ?>

                            <tr>

                                <td
                                    style="
                                        padding: 12px;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >
                                    <?= $index + 1 ?>
                                </td>


                                <td
                                    style="
                                        padding: 12px;
                                        border-bottom: 1px solid #e5e7eb;
                                        font-weight: bold;
                                    "
                                >
                                    <?= htmlspecialchars(
                                        $item['kode_penarikan']
                                    ) ?>
                                </td>


                                <td
                                    style="
                                        padding: 12px;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >
                                    <?= htmlspecialchars(
                                        $item['nama']
                                    ) ?>
                                </td>


                                <td
                                    style="
                                        padding: 12px;
                                        border-bottom: 1px solid #e5e7eb;
                                        font-weight: bold;
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
                                </td>


                                <td
                                    style="
                                        padding: 12px;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >
                                    <?= ucfirst(
                                        htmlspecialchars(
                                            $item['metode']
                                        )
                                    ) ?>
                                </td>


                                <td
                                    style="
                                        padding: 12px;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >
                                    <?= htmlspecialchars(
                                        $item['nomor_tujuan']
                                    ) ?>
                                </td>


                                <td
                                    style="
                                        padding: 12px;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >

                                    <?php if ($item['status'] === 'pending'): ?>

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

                                    <?php elseif ($item['status'] === 'diterima'): ?>

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

                                    <?php elseif ($item['status'] === 'ditolak'): ?>

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
                                                display: inline-block;
                                                padding: 6px 10px;
                                                border-radius: 20px;
                                                background: #e5e7eb;
                                                color: #374151;
                                                font-size: 13px;
                                                font-weight: bold;
                                            "
                                        >
                                            <?= htmlspecialchars(
                                                $item['status']
                                            ) ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td
                                    style="
                                        padding: 12px;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >
                                    <?= date(
                                        'd-m-Y H:i',
                                        strtotime(
                                            $item['created_at']
                                        )
                                    ) ?>
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
```
