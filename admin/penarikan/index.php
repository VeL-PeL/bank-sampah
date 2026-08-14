<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Penarikan';

$penarikan = [];
$error = '';

try {

    $stmt = $pdo->query("
        SELECT
            penarikan.id,
            penarikan.kode_penarikan,
            penarikan.nasabah_id,
            penarikan.jumlah,
            penarikan.metode,
            penarikan.nomor_tujuan,
            penarikan.status,
            penarikan.tanggal_pengajuan,
            users.nama
        FROM penarikan
        INNER JOIN users
            ON penarikan.nasabah_id = users.id
        ORDER BY penarikan.tanggal_pengajuan DESC
    ");

    $penarikan = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = 'Gagal mengambil data penarikan.';

}

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
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        "
    >


        <h2
            style="
                margin-top: 0;
                margin-bottom: 8px;
            "
        >
            Pengajuan Penarikan
        </h2>


        <p
            style="
                color: #6b7280;
                margin-bottom: 25px;
            "
        >
            Daftar pengajuan penarikan saldo dari nasabah.
        </p>



        <!-- ERROR -->

        <?php if ($error !== ''): ?>

            <div
                style="
                    background: #fee2e2;
                    color: #991b1b;
                    padding: 15px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                "
            >

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>



        <!-- TABLE -->

        <?php if (empty($penarikan)): ?>

            <div
                style="
                    text-align: center;
                    padding: 40px;
                    color: #6b7280;
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
                                    padding: 14px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                "
                            >
                                No
                            </th>


                            <th
                                style="
                                    padding: 14px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                "
                            >
                                Kode
                            </th>


                            <th
                                style="
                                    padding: 14px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                "
                            >
                                Nasabah
                            </th>


                            <th
                                style="
                                    padding: 14px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                "
                            >
                                Jumlah
                            </th>


                            <th
                                style="
                                    padding: 14px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                "
                            >
                                Metode
                            </th>


                            <th
                                style="
                                    padding: 14px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                "
                            >
                                Status
                            </th>


                            <th
                                style="
                                    padding: 14px;
                                    text-align: left;
                                    background: #f0fdf4;
                                    color: #166534;
                                "
                            >
                                Tanggal
                            </th>


                            <th
                                style="
                                    padding: 14px;
                                    text-align: center;
                                    background: #f0fdf4;
                                    color: #166534;
                                "
                            >
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($penarikan as $index => $item): ?>

                            <?php

                            $statusClass = match ($item['status']) {

                                'pending' => '
                                    background: #fef3c7;
                                    color: #92400e;
                                ',

                                'disetujui' => '
                                    background: #dcfce7;
                                    color: #166534;
                                ',

                                'ditolak' => '
                                    background: #fee2e2;
                                    color: #991b1b;
                                ',

                                'selesai' => '
                                    background: #dbeafe;
                                    color: #1d4ed8;
                                ',

                                default => '
                                    background: #e5e7eb;
                                    color: #374151;
                                '

                            };

                            ?>


                            <tr>


                                <!-- NO -->

                                <td
                                    style="
                                        padding: 14px;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >

                                    <?= $index + 1 ?>

                                </td>



                                <!-- KODE -->

                                <td
                                    style="
                                        padding: 14px;
                                        border-bottom: 1px solid #e5e7eb;
                                        font-weight: 600;
                                    "
                                >

                                    <?= htmlspecialchars(
                                        $item['kode_penarikan']
                                    ) ?>

                                </td>



                                <!-- NASABAH -->

                                <td
                                    style="
                                        padding: 14px;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >

                                    <?= htmlspecialchars(
                                        $item['nama']
                                    ) ?>

                                </td>



                                <!-- JUMLAH -->

                                <td
                                    style="
                                        padding: 14px;
                                        border-bottom: 1px solid #e5e7eb;
                                        font-weight: 600;
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



                                <!-- METODE -->

                                <td
                                    style="
                                        padding: 14px;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >

                                    <?php if ($item['metode'] === 'bank'): ?>

                                        Bank

                                    <?php else: ?>

                                        E-Wallet

                                    <?php endif; ?>

                                </td>



                                <!-- STATUS -->

                                <td
                                    style="
                                        padding: 14px;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 6px 10px;
                                            border-radius: 20px;
                                            font-size: 13px;
                                            font-weight: bold;
                                            <?= $statusClass ?>
                                        "
                                    >

                                        <?= ucfirst(
                                            htmlspecialchars(
                                                $item['status']
                                            )
                                        ) ?>

                                    </span>

                                </td>



                                <!-- TANGGAL -->

                                <td
                                    style="
                                        padding: 14px;
                                        border-bottom: 1px solid #e5e7eb;
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
                                        border-bottom: 1px solid #e5e7eb;
                                        text-align: center;
                                    "
                                >

                                    <a
                                        href="detail.php?id=<?= (int) $item['id'] ?>"
                                        style="
                                            display: inline-block;
                                            padding: 7px 12px;
                                            background: #166534;
                                            color: white;
                                            text-decoration: none;
                                            border-radius: 7px;
                                            font-size: 13px;
                                            font-weight: bold;
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