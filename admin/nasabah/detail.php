
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Detail Nasabah';


// ========================================
// AMBIL ID USER
// ========================================

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}


// ========================================
// AMBIL DATA NASABAH
// ========================================

try {

    $stmt = $pdo->prepare("
        SELECT
            u.id AS user_id,
            u.nama,
            u.email,
            u.role,
            u.status,
            u.created_at,
            u.updated_at,
            n.id AS nasabah_id,
            n.nomor_nasabah,
            n.nik,
            n.alamat,
            n.no_hp
        FROM users u
        LEFT JOIN nasabah n
            ON n.user_id = u.id
        WHERE u.id = ?
          AND u.role = 'nasabah'
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $nasabah = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die('Gagal mengambil data nasabah.');

}


if (!$nasabah) {

    header('Location: index.php');
    exit;

}


// ========================================
// ID NASABAH
// ========================================

$nasabahId = (int) ($nasabah['nasabah_id'] ?? 0);


// ========================================
// STATISTIK SETORAN
// ========================================

$total_setoran = 0;
$total_berat = 0;
$total_nilai_setoran = 0;

try {

    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS jumlah,
            COALESCE(SUM(berat), 0) AS total_berat,
            COALESCE(SUM(total_harga), 0) AS total_nilai
        FROM setoran
        WHERE user_id = ?
    ");

    $stmt->execute([$id]);

    $stat_setoran = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_setoran =
        (int) ($stat_setoran['jumlah'] ?? 0);

    $total_berat =
        (float) ($stat_setoran['total_berat'] ?? 0);

    $total_nilai_setoran =
        (float) ($stat_setoran['total_nilai'] ?? 0);

} catch (PDOException $e) {

    $total_setoran = 0;
    $total_berat = 0;
    $total_nilai_setoran = 0;

}


// ========================================
// STATISTIK PENARIKAN
// ========================================

$total_penarikan = 0;
$total_nilai_penarikan = 0;

try {

    /*
     * PENTING:
     * setoran menggunakan users.id
     * penarikan menggunakan nasabah.id
     *
     * Karena halaman ini menggunakan users.id,
     * kita hubungkan penarikan ke nasabah
     * melalui n.user_id.
     */

    $stmt = $pdo->prepare("
        SELECT
            COUNT(p.id) AS jumlah,
            COALESCE(SUM(p.jumlah), 0) AS total_jumlah
        FROM penarikan p
        INNER JOIN nasabah n
            ON p.nasabah_id = n.id
        WHERE n.user_id = ?
    ");

    $stmt->execute([$id]);

    $stat_penarikan = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_penarikan =
        (int) ($stat_penarikan['jumlah'] ?? 0);

    $total_nilai_penarikan =
        (float) ($stat_penarikan['total_jumlah'] ?? 0);

} catch (PDOException $e) {

    $total_penarikan = 0;
    $total_nilai_penarikan = 0;

}


// ========================================
// HEADER
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
                    Detail Nasabah
                </h1>

                <p>
                    Informasi lengkap nasabah Bank Sampah.
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


    <!-- KONTEN -->

    <div
        style="
            max-width: 1100px;
            margin: 30px auto;
        "
    >


        <!-- PROFIL NASABAH -->

        <div
            style="
                background: white;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
                margin-bottom: 25px;
            "
        >

            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 20px;
                    flex-wrap: wrap;
                "
            >

                <div
                    style="
                        width: 75px;
                        height: 75px;
                        border-radius: 50%;
                        background: #dcfce7;
                        color: #166534;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 30px;
                        font-weight: bold;
                    "
                >
                    <?= strtoupper(
                        substr(
                            $nasabah['nama'],
                            0,
                            1
                        )
                    ) ?>
                </div>


                <div style="flex: 1;">

                    <h2
                        style="
                            margin: 0 0 6px 0;
                            color: #166534;
                        "
                    >
                        <?= htmlspecialchars(
                            $nasabah['nama']
                        ) ?>
                    </h2>

                    <p
                        style="
                            margin: 0;
                            color: #6b7280;
                        "
                    >
                        <?= htmlspecialchars(
                            $nasabah['email']
                        ) ?>
                    </p>

                </div>


                <div>

                    <?php if ($nasabah['status'] === 'aktif'): ?>

                        <span
                            style="
                                display: inline-block;
                                padding: 8px 14px;
                                border-radius: 20px;
                                background: #dcfce7;
                                color: #166534;
                                font-weight: bold;
                            "
                        >
                            Aktif
                        </span>

                    <?php else: ?>

                        <span
                            style="
                                display: inline-block;
                                padding: 8px 14px;
                                border-radius: 20px;
                                background: #fee2e2;
                                color: #b91c1c;
                                font-weight: bold;
                            "
                        >
                            Nonaktif
                        </span>

                    <?php endif; ?>

                </div>

            </div>


            <!-- INFORMASI -->

            <div
                style="
                    display: grid;
                    grid-template-columns:
                        repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    margin-top: 30px;
                "
            >

                <div
                    style="
                        padding: 18px;
                        background: #f9fafb;
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
                        Role
                    </div>

                    <strong>
                        <?= ucfirst(
                            htmlspecialchars(
                                $nasabah['role']
                            )
                        ) ?>
                    </strong>

                </div>


                <div
                    style="
                        padding: 18px;
                        background: #f9fafb;
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
                        Terdaftar
                    </div>

                    <strong>
                        <?= date(
                            'd-m-Y H:i',
                            strtotime(
                                $nasabah['created_at']
                            )
                        ) ?>
                    </strong>

                </div>


                <div
                    style="
                        padding: 18px;
                        background: #f9fafb;
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
                        ID Nasabah
                    </div>

                    <strong>
                        #<?= $nasabahId ?>
                    </strong>

                </div>

            </div>

        </div>


        <!-- STATISTIK -->

        <div
            style="
                display: grid;
                grid-template-columns:
                    repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 25px;
            "
        >


            <!-- TOTAL SETORAN -->

            <div
                style="
                    background: white;
                    padding: 22px;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,.06);
                "
            >

                <div
                    style="
                        font-size: 30px;
                        margin-bottom: 10px;
                    "
                >
                    ♻️
                </div>

                <div
                    style="
                        color: #6b7280;
                        font-size: 14px;
                    "
                >
                    Total Setoran
                </div>

                <strong
                    style="
                        display: block;
                        margin-top: 5px;
                        font-size: 25px;
                        color: #166534;
                    "
                >
                    <?= number_format(
                        $total_setoran,
                        0,
                        ',',
                        '.'
                    ) ?>
                </strong>

                <div
                    style="
                        margin-top: 5px;
                        color: #6b7280;
                        font-size: 13px;
                    "
                >
                    transaksi
                </div>

            </div>


            <!-- TOTAL BERAT -->

            <div
                style="
                    background: white;
                    padding: 22px;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,.06);
                "
            >

                <div
                    style="
                        font-size: 30px;
                        margin-bottom: 10px;
                    "
                >
                    ⚖️
                </div>

                <div
                    style="
                        color: #6b7280;
                        font-size: 14px;
                    "
                >
                    Total Berat
                </div>

                <strong
                    style="
                        display: block;
                        margin-top: 5px;
                        font-size: 25px;
                        color: #166534;
                    "
                >
                    <?= number_format(
                        $total_berat,
                        2,
                        ',',
                        '.'
                    ) ?>
                </strong>

                <div
                    style="
                        margin-top: 5px;
                        color: #6b7280;
                        font-size: 13px;
                    "
                >
                    kg
                </div>

            </div>


            <!-- NILAI SETORAN -->

            <div
                style="
                    background: white;
                    padding: 22px;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,.06);
                "
            >

                <div
                    style="
                        font-size: 30px;
                        margin-bottom: 10px;
                    "
                >
                    💰
                </div>

                <div
                    style="
                        color: #6b7280;
                        font-size: 14px;
                    "
                >
                    Nilai Setoran
                </div>

                <strong
                    style="
                        display: block;
                        margin-top: 5px;
                        font-size: 22px;
                        color: #166534;
                    "
                >
                    Rp
                    <?= number_format(
                        $total_nilai_setoran,
                        0,
                        ',',
                        '.'
                    ) ?>
                </strong>

            </div>


            <!-- TOTAL PENARIKAN -->

            <div
                style="
                    background: white;
                    padding: 22px;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,.06);
                "
            >

                <div
                    style="
                        font-size: 30px;
                        margin-bottom: 10px;
                    "
                >
                    💸
                </div>

                <div
                    style="
                        color: #6b7280;
                        font-size: 14px;
                    "
                >
                    Total Penarikan
                </div>

                <strong
                    style="
                        display: block;
                        margin-top: 5px;
                        font-size: 22px;
                        color: #166534;
                    "
                >
                    Rp
                    <?= number_format(
                        $total_nilai_penarikan,
                        0,
                        ',',
                        '.'
                    ) ?>
                </strong>

                <div
                    style="
                        margin-top: 5px;
                        color: #6b7280;
                        font-size: 13px;
                    "
                >
                    <?= number_format(
                        $total_penarikan,
                        0,
                        ',',
                        '.'
                    ) ?>
                    transaksi
                </div>

            </div>

        </div>


        <!-- TOMBOL -->

        <div
            style="
                background: white;
                padding: 20px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
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
                href="edit.php?id=<?= (int) $nasabah['user_id'] ?>"
                style="
                    display: inline-block;
                    padding: 12px 20px;
                    border-radius: 10px;
                    background: #dbeafe;
                    color: #1d4ed8;
                    text-decoration: none;
                    font-weight: 600;
                "
            >
                ✏️ Edit Nasabah
            </a>

        </div>

    </div>

</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>
