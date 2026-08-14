<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Detail Penarikan';

$id = (int) ($_GET['id'] ?? 0);

$penarikan = null;
$error = '';


// ========================================
// VALIDASI ID
// ========================================

if ($id <= 0) {

    $error = 'ID penarikan tidak valid.';

} else {

    try {

        // ========================================
        // AMBIL DETAIL PENARIKAN
        // ========================================

        $stmt = $pdo->prepare("
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
            WHERE p.id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        $penarikan = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$penarikan) {

            $error = 'Data penarikan tidak ditemukan.';

        }

    } catch (PDOException $e) {

        $error = 'Gagal mengambil detail penarikan.';

    }

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
                    Detail Penarikan
                </h1>

                <p>
                    Lihat detail pengajuan penarikan nasabah.
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
            max-width: 750px;
            margin-top: 30px;
        "
    >


        <!-- BACK -->

        <a
            href="index.php"
            style="
                display: inline-block;
                margin-bottom: 20px;
                color: #166534;
                text-decoration: none;
                font-weight: 600;
            "
        >
            ← Kembali ke Penarikan
        </a>


        <?php if ($error !== ''): ?>

            <!-- ERROR -->

            <div
                style="
                    background: #fee2e2;
                    color: #991b1b;
                    padding: 18px;
                    border-radius: 10px;
                    background: #fee2e2;
                "
            >

                <?= htmlspecialchars($error) ?>

            </div>


        <?php elseif ($penarikan): ?>


            <!-- DETAIL CARD -->

            <div
                style="
                    background: white;
                    padding: 30px;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,.08);
                "
            >


                <h2
                    style="
                        margin-top: 0;
                        color: #166534;
                        margin-bottom: 25px;
                    "
                >
                    Detail Pengajuan
                </h2>


                <!-- KODE -->

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding: 15px 0;
                        border-bottom: 1px solid #e5e7eb;
                    "
                >

                    <span
                        style="
                            color: #6b7280;
                        "
                    >
                        Kode Penarikan
                    </span>


                    <strong>
                        <?= htmlspecialchars(
                            $penarikan['kode_penarikan']
                        ) ?>
                    </strong>

                </div>


                <!-- NASABAH -->

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding: 15px 0;
                        border-bottom: 1px solid #e5e7eb;
                    "
                >

                    <span
                        style="
                            color: #6b7280;
                        "
                    >
                        Nasabah
                    </span>


                    <strong>
                        <?= htmlspecialchars(
                            $penarikan['nama']
                        ) ?>
                    </strong>

                </div>


                <!-- JUMLAH -->

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding: 15px 0;
                        border-bottom: 1px solid #e5e7eb;
                    "
                >

                    <span
                        style="
                            color: #6b7280;
                        "
                    >
                        Jumlah Penarikan
                    </span>


                    <strong
                        style="
                            color: #166534;
                            font-size: 20px;
                        "
                    >
                        Rp
                        <?= number_format(
                            $penarikan['jumlah'],
                            0,
                            ',',
                            '.'
                        ) ?>
                    </strong>

                </div>


                <!-- METODE -->

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding: 15px 0;
                        border-bottom: 1px solid #e5e7eb;
                    "
                >

                    <span
                        style="
                            color: #6b7280;
                        "
                    >
                        Metode
                    </span>


                    <strong>
                        <?= ucfirst(
                            htmlspecialchars(
                                $penarikan['metode']
                            )
                        ) ?>
                    </strong>

                </div>


                <!-- NOMOR TUJUAN -->

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding: 15px 0;
                        border-bottom: 1px solid #e5e7eb;
                    "
                >

                    <span
                        style="
                            color: #6b7280;
                        "
                    >
                        Nomor Tujuan
                    </span>


                    <strong>
                        <?= htmlspecialchars(
                            $penarikan['nomor_tujuan']
                        ) ?>
                    </strong>

                </div>


                <!-- STATUS -->

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 20px;
                        padding: 15px 0;
                        border-bottom: 1px solid #e5e7eb;
                    "
                >

                    <span
                        style="
                            color: #6b7280;
                        "
                    >
                        Status
                    </span>


                    <?php if ($penarikan['status'] === 'pending'): ?>

                        <span
                            style="
                                display: inline-block;
                                padding: 7px 14px;
                                border-radius: 20px;
                                background: #fef3c7;
                                color: #92400e;
                                font-size: 13px;
                                font-weight: bold;
                            "
                        >
                            Pending
                        </span>


                    <?php elseif ($penarikan['status'] === 'diterima'): ?>

                        <span
                            style="
                                display: inline-block;
                                padding: 7px 14px;
                                border-radius: 20px;
                                background: #dcfce7;
                                color: #166534;
                                font-size: 13px;
                                font-weight: bold;
                            "
                        >
                            Diterima
                        </span>


                    <?php elseif ($penarikan['status'] === 'ditolak'): ?>

                        <span
                            style="
                                display: inline-block;
                                padding: 7px 14px;
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
                                padding: 7px 14px;
                                border-radius: 20px;
                                background: #e5e7eb;
                                color: #374151;
                                font-size: 13px;
                                font-weight: bold;
                            "
                        >
                            <?= htmlspecialchars(
                                $penarikan['status']
                            ) ?>
                        </span>

                    <?php endif; ?>

                </div>


                <!-- TANGGAL -->

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding: 15px 0;
                    "
                >

                    <span
                        style="
                            color: #6b7280;
                        "
                    >
                        Tanggal Pengajuan
                    </span>


                    <strong>
                        <?= date(
                            'd-m-Y H:i',
                            strtotime(
                                $penarikan['created_at']
                            )
                        ) ?>
                    </strong>

                </div>


                
<!-- AKSI ADMIN -->

<?php if ($penarikan['status'] === 'pending'): ?>

    <div
        style="
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e5e7eb;
        "
    >

        <h3
            style="
                margin-top: 0;
                color: #374151;
            "
        >
            Aksi Admin
        </h3>

        <p
            style="
                color: #6b7280;
                margin-bottom: 20px;
            "
        >
            Pilih tindakan untuk pengajuan penarikan ini.
        </p>


        <div
            style="
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            "
        >

            <!-- TERIMA -->

            <form
                action="proses.php"
                method="POST"
                onsubmit="
                    return confirm(
                        'Apakah kamu yakin ingin menerima penarikan ini? Saldo nasabah akan dikurangi.'
                    );
                "
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $penarikan['id'] ?>"
                >

                <input
                    type="hidden"
                    name="aksi"
                    value="terima"
                >

                <button
                    type="submit"
                    style="
                        border: none;
                        padding: 12px 20px;
                        border-radius: 8px;
                        background: #16a34a;
                        color: white;
                        font-weight: bold;
                        cursor: pointer;
                    "
                >
                    ✓ Terima
                </button>

            </form>


            <!-- TOLAK -->

            <form
                action="proses.php"
                method="POST"
                onsubmit="
                    return confirm(
                        'Apakah kamu yakin ingin menolak penarikan ini?'
                    );
                "
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $penarikan['id'] ?>"
                >

                <input
                    type="hidden"
                    name="aksi"
                    value="tolak"
                >

                <button
                    type="submit"
                    style="
                        border: none;
                        padding: 12px 20px;
                        border-radius: 8px;
                        background: #dc2626;
                        color: white;
                        font-weight: bold;
                        cursor: pointer;
                    "
                >
                    ✕ Tolak
                </button>

            </form>

        </div>

    </div>

<?php endif; ?>



            </div>

        <?php endif; ?>

    </div>

</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>
```
