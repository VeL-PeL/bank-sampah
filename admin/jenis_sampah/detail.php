
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Detail Jenis Sampah';


// ========================================
// AMBIL ID
// ========================================

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {

    header('Location: index.php');
    exit;

}


// ========================================
// AMBIL DATA
// ========================================

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama_sampah,
            harga_per_kg,
            deskripsi,
            status,
            created_at,
            updated_at
        FROM jenis_sampah
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $sampah = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die('Gagal mengambil data jenis sampah.');

}


if (!$sampah) {

    header('Location: index.php');
    exit;

}

?>

<?php

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
                    Detail Jenis Sampah
                </h1>

                <p>
                    Informasi lengkap jenis sampah.
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
         DETAIL
    ======================================== -->

    <div
        style="
            max-width: 900px;
            margin: 30px auto;
        "
    >

        <div
            style="
                background: white;
                padding: 30px;
                border-radius: 15px;
                box-shadow:
                    0 10px 30px rgba(0,0,0,.06);
            "
        >

            <!-- HEADER DETAIL -->

            <div
                style="
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    gap: 20px;
                    flex-wrap: wrap;
                    margin-bottom: 30px;
                "
            >

                <div>

                    <div
                        style="
                            font-size: 14px;
                            color: #6b7280;
                            margin-bottom: 6px;
                        "
                    >
                        Jenis Sampah
                    </div>

                    <h2
                        style="
                            margin: 0;
                            color: #166534;
                            font-size: 28px;
                        "
                    >
                        <?= htmlspecialchars(
                            $sampah['nama_sampah']
                        ) ?>
                    </h2>

                </div>


                <!-- STATUS -->

                <div>

                    <?php if (
                        $sampah['status']
                        === 'aktif'
                    ): ?>

                        <span
                            style="
                                display: inline-block;
                                padding: 8px 14px;
                                border-radius: 20px;
                                background: #dcfce7;
                                color: #166534;
                                font-size: 14px;
                                font-weight: bold;
                            "
                        >
                            ● Aktif
                        </span>

                    <?php else: ?>

                        <span
                            style="
                                display: inline-block;
                                padding: 8px 14px;
                                border-radius: 20px;
                                background: #fee2e2;
                                color: #b91c1c;
                                font-size: 14px;
                                font-weight: bold;
                            "
                        >
                            ● Nonaktif
                        </span>

                    <?php endif; ?>

                </div>

            </div>


            <!-- ========================================
                 HARGA
            ======================================== -->

            <div
                style="
                    padding: 25px;
                    border-radius: 12px;
                    background: #f0fdf4;
                    margin-bottom: 25px;
                "
            >

                <div
                    style="
                        color: #6b7280;
                        font-size: 14px;
                        margin-bottom: 6px;
                    "
                >
                    Harga per Kilogram
                </div>

                <div
                    style="
                        color: #166534;
                        font-size: 30px;
                        font-weight: bold;
                    "
                >
                    Rp
                    <?= number_format(
                        $sampah['harga_per_kg'],
                        0,
                        ',',
                        '.'
                    ) ?>
                    <span
                        style="
                            font-size: 15px;
                            font-weight: normal;
                            color: #6b7280;
                        "
                    >
                        / kg
                    </span>
                </div>

            </div>


            <!-- ========================================
                 INFORMASI
            ======================================== -->

            <div
                style="
                    display: grid;
                    grid-template-columns:
                        repeat(
                            auto-fit,
                            minmax(250px, 1fr)
                        );
                    gap: 20px;
                    margin-bottom: 25px;
                "
            >

                <!-- ID -->

                <div
                    style="
                        padding: 18px;
                        border: 1px solid #e5e7eb;
                        border-radius: 10px;
                    "
                >

                    <div
                        style="
                            color: #6b7280;
                            font-size: 13px;
                            margin-bottom: 5px;
                        "
                    >
                        ID Data
                    </div>

                    <strong>
                        #<?= (int) $sampah['id'] ?>
                    </strong>

                </div>


                <!-- TANGGAL DIBUAT -->

                <div
                    style="
                        padding: 18px;
                        border: 1px solid #e5e7eb;
                        border-radius: 10px;
                    "
                >

                    <div
                        style="
                            color: #6b7280;
                            font-size: 13px;
                            margin-bottom: 5px;
                        "
                    >
                        Tanggal Dibuat
                    </div>

                    <strong>
                        <?= date(
                            'd-m-Y H:i',
                            strtotime(
                                $sampah['created_at']
                            )
                        ) ?>
                    </strong>

                </div>


                <!-- TERAKHIR DIPERBARUI -->

                <div
                    style="
                        padding: 18px;
                        border: 1px solid #e5e7eb;
                        border-radius: 10px;
                    "
                >

                    <div
                        style="
                            color: #6b7280;
                            font-size: 13px;
                            margin-bottom: 5px;
                        "
                    >
                        Terakhir Diperbarui
                    </div>

                    <strong>
                        <?= date(
                            'd-m-Y H:i',
                            strtotime(
                                $sampah['updated_at']
                            )
                        ) ?>
                    </strong>

                </div>

            </div>


            <!-- ========================================
                 DESKRIPSI
            ======================================== -->

            <div
                style="
                    margin-bottom: 30px;
                "
            >

                <h3
                    style="
                        margin-bottom: 10px;
                        color: #374151;
                    "
                >
                    Deskripsi
                </h3>

                <div
                    style="
                        padding: 18px;
                        background: #f9fafb;
                        border-radius: 10px;
                        color: #4b5563;
                        line-height: 1.7;
                    "
                >

                    <?php if (
                        !empty(
                            $sampah['deskripsi']
                        )
                    ): ?>

                        <?= nl2br(
                            htmlspecialchars(
                                $sampah['deskripsi']
                            )
                        ) ?>

                    <?php else: ?>

                        <span
                            style="
                                color: #9ca3af;
                            "
                        >
                            Belum ada deskripsi.
                        </span>

                    <?php endif; ?>

                </div>

            </div>


            <!-- ========================================
                 TOMBOL
            ======================================== -->

            <div
                style="
                    display: flex;
                    gap: 10px;
                    flex-wrap: wrap;
                "
            >

                <a
                    href="index.php"
                    style="
                        display: inline-block;
                        padding: 12px 20px;
                        border-radius: 10px;
                        background: #f3f4f6;
                        color: #374151;
                        text-decoration: none;
                        font-weight: 600;
                    "
                >
                    ← Kembali
                </a>


                <a
                    href="edit.php?id=<?= (int) $sampah['id'] ?>"
                    style="
                        display: inline-block;
                        padding: 12px 20px;
                        border-radius: 10px;
                        background: #2563eb;
                        color: white;
                        text-decoration: none;
                        font-weight: 600;
                    "
                >
                    ✏️ Edit Data
                </a>

            </div>

        </div>

    </div>

</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>
