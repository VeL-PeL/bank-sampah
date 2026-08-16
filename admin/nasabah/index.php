
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

    $data_nasabah =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error =
        'Gagal mengambil data nasabah.';

}


// ========================================
// HEADER & SIDEBAR
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
                    Nasabah
                </h1>

                <p>
                    Kelola data nasabah Bank Sampah.
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


    <!-- ========================================
         KONTEN
    ======================================== -->

    <div
        style="
            margin-top: 25px;
        "
    >

        <!-- HEADER CARD -->

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
                        Data Nasabah
                    </h2>

                    <p
                        style="
                            margin: 0;
                            color: #6b7280;
                        "
                    >
                        Daftar seluruh nasabah yang
                        terdaftar.
                    </p>

                </div>


                <!-- JUMLAH NASABAH -->

<div
    style="
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    "
>

    <div
        style="
            padding: 12px 18px;
            background: #f0fdf4;
            border-radius: 10px;
            color: #166534;
            font-weight: bold;
        "
    >
        👥
        <?= number_format(
            count($data_nasabah),
            0,
            ',',
            '.'
        ) ?>
        Nasabah
    </div>


    <a
        href="tambah.php"
        style="
            display: inline-block;
            padding: 12px 18px;
            background: #166534;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
        "
    >
        + Tambah Nasabah
    </a>

</div>


            </div>

        </div>


        <!-- ERROR -->

        <?php if ($error !== ''): ?>

            <div
                style="
                    padding: 15px;
                    margin-bottom: 20px;
                    border-radius: 10px;
                    background: #fee2e2;
                    color: #b91c1c;
                "
            >
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <!-- ========================================
             TABEL NASABAH
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

        <th style="
            padding: 13px;
            text-align: left;
            background: #f0fdf4;
            color: #166534;
        ">
            No
        </th>

        <th style="
            padding: 13px;
            text-align: left;
            background: #f0fdf4;
            color: #166534;
        ">
            Nama
        </th>

        <th style="
            padding: 13px;
            text-align: left;
            background: #f0fdf4;
            color: #166534;
        ">
            Email
        </th>

        <th style="
            padding: 13px;
            text-align: left;
            background: #f0fdf4;
            color: #166534;
        ">
            Role
        </th>

        <th style="
            padding: 13px;
            text-align: left;
            background: #f0fdf4;
            color: #166534;
        ">
            Status
        </th>

        <th style="
            padding: 13px;
            text-align: left;
            background: #f0fdf4;
            color: #166534;
        ">
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

                    <?php if (!empty($data_nasabah)): ?>

                        <?php $no = 1; ?>

                        <?php foreach (
                            $data_nasabah
                            as $nasabah
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

<td style="
    padding: 13px;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
">
    <?= htmlspecialchars(
        $nasabah['nama']
    ) ?>
</td>


<!-- EMAIL -->

<td style="
    padding: 13px;
    border-bottom: 1px solid #e5e7eb;
">
    <?= htmlspecialchars(
        $nasabah['email']
    ) ?>
</td>


<!-- ROLE -->

<td style="
    padding: 13px;
    border-bottom: 1px solid #e5e7eb;
">
    <?= ucfirst(
        htmlspecialchars(
            $nasabah['role']
        )
    ) ?>
</td>


<!-- STATUS -->

<td style="
    padding: 13px;
    border-bottom: 1px solid #e5e7eb;
">

    <?php if (
        $nasabah['status'] === 'aktif'
    ): ?>

        <span style="
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-size: 13px;
            font-weight: bold;
        ">
            Aktif
        </span>

    <?php else: ?>

        <span style="
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 13px;
            font-weight: bold;
        ">
            Nonaktif
        </span>

    <?php endif; ?>

</td>


<!-- TANGGAL -->

<td style="
    padding: 13px;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
">
    <?= date(
        'd-m-Y',
        strtotime(
            $nasabah['created_at']
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
        href="edit.php?id=<?= (int) $nasabah['id'] ?>"
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
    href="detail.php?id=<?= (int) $nasabah['id'] ?>"
    style="
        display: inline-block;
        padding: 7px 12px;
        border-radius: 8px;
        background: #dcfce7;
        color: #166534;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        margin-left: 5px;
    "
>
    👁️ Detail
</a>

<?php if ($nasabah['status'] === 'aktif'): ?>

    <a
        href="nonaktif.php?id=<?= (int) $nasabah['id'] ?>"
        onclick="return confirm(
            'Yakin ingin menonaktifkan nasabah ini?'
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
            margin-left: 5px;
        "
    >
        🚫 Nonaktifkan
    </a>

<?php else: ?>

    <span
        style="
            display: inline-block;
            padding: 7px 12px;
            border-radius: 8px;
            background: #f3f4f6;
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            margin-left: 5px;
        "
    >
        Nonaktif
    </span>

<?php endif; ?>



</td>



                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                style="
                                    padding: 35px;
                                    text-align: center;
                                    color: #6b7280;
                                "
                            >
                                Belum ada data nasabah.
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

