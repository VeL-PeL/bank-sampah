
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Pengaturan';

$userId = (int) ($_SESSION['user_id'] ?? 0);

$admin = null;
$error = '';


// ========================================
// AMBIL DATA ADMIN
// ========================================

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama,
            email
        FROM users
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $userId
    ]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        $error = 'Data admin tidak ditemukan.';
    }

} catch (PDOException $e) {

    $error = 'Gagal mengambil data admin.';

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
                    Pengaturan
                </h1>

                <p>
                    Kelola pengaturan akun administrator.
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

    <div
        style="
            max-width: 850px;
            margin-top: 30px;
        "
    >


        <?php if ($error !== ''): ?>

            <div
                style="
                    background: #fee2e2;
                    color: #991b1b;
                    padding: 15px 18px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                "
            >

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <?php if ($admin): ?>


            <!-- ========================================
                 PROFIL ADMIN
            ======================================== -->

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
                        gap: 18px;
                        margin-bottom: 25px;
                    "
                >

                    <div
                        style="
                            width: 65px;
                            height: 65px;
                            border-radius: 50%;
                            background: #166534;
                            color: white;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 26px;
                            font-weight: bold;
                        "
                    >

                        <?= strtoupper(
                            substr(
                                $admin['nama'],
                                0,
                                1
                            )
                        ) ?>

                    </div>


                    <div>

                        <h2
                            style="
                                margin: 0;
                                color: #166534;
                            "
                        >
                            Profil Administrator
                        </h2>

                        <p
                            style="
                                margin: 5px 0 0;
                                color: #6b7280;
                            "
                        >
                            Informasi akun administrator yang sedang login.
                        </p>

                    </div>

                </div>


                <!-- NAMA -->

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding: 16px 0;
                        border-bottom: 1px solid #e5e7eb;
                    "
                >

                    <span
                        style="
                            color: #6b7280;
                        "
                    >
                        Nama
                    </span>

                    <strong>
                        <?= htmlspecialchars(
                            $admin['nama']
                        ) ?>
                    </strong>

                </div>


                <!-- EMAIL -->

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding: 16px 0;
                        border-bottom: 1px solid #e5e7eb;
                    "
                >

                    <span
                        style="
                            color: #6b7280;
                        "
                    >
                        Email
                    </span>

                    <strong>
                        <?= htmlspecialchars(
                            $admin['email']
                        ) ?>
                    </strong>

                </div>


                <!-- ROLE -->

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding: 16px 0;
                    "
                >

                    <span
                        style="
                            color: #6b7280;
                        "
                    >
                        Role
                    </span>

                    <span
                        style="
                            background: #dcfce7;
                            color: #166534;
                            padding: 6px 12px;
                            border-radius: 20px;
                            font-size: 13px;
                            font-weight: bold;
                        "
                    >
                        Administrator
                    </span>

                </div>

            </div>


            <!-- ========================================
                 MENU PENGATURAN
            ======================================== -->

            <div
                style="
                    background: white;
                    padding: 30px;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,.06);
                "
            >

                <h2
                    style="
                        margin-top: 0;
                        color: #166534;
                        margin-bottom: 8px;
                    "
                >
                    Pengaturan Akun
                </h2>

                <p
                    style="
                        color: #6b7280;
                        margin-bottom: 25px;
                    "
                >
                    Pilih pengaturan yang ingin kamu ubah.
                </p>


                <!-- EDIT PROFIL -->

                <a
                    href="profil.php"
                    style="
                        display: flex;
                        align-items: center;
                        gap: 15px;
                        padding: 18px;
                        border: 1px solid #e5e7eb;
                        border-radius: 12px;
                        text-decoration: none;
                        color: #111827;
                        margin-bottom: 15px;
                    "
                >

                    <div
                        style="
                            width: 45px;
                            height: 45px;
                            border-radius: 10px;
                            background: #dcfce7;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 22px;
                        "
                    >
                        👤
                    </div>

                    <div>

                        <div
                            style="
                                font-weight: bold;
                                color: #166534;
                            "
                        >
                            Edit Profil
                        </div>

                        <div
                            style="
                                color: #6b7280;
                                font-size: 13px;
                                margin-top: 3px;
                            "
                        >
                            Ubah nama dan email administrator.
                        </div>

                    </div>

                </a>


                <!-- GANTI PASSWORD -->

                <a
                    href="password.php"
                    style="
                        display: flex;
                        align-items: center;
                        gap: 15px;
                        padding: 18px;
                        border: 1px solid #e5e7eb;
                        border-radius: 12px;
                        text-decoration: none;
                        color: #111827;
                    "
                >

                    <div
                        style="
                            width: 45px;
                            height: 45px;
                            border-radius: 10px;
                            background: #fef3c7;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 22px;
                        "
                    >
                        🔐
                    </div>

                    <div>

                        <div
                            style="
                                font-weight: bold;
                                color: #166534;
                            "
                        >
                            Ganti Password
                        </div>

                        <div
                            style="
                                color: #6b7280;
                                font-size: 13px;
                                margin-top: 3px;
                            "
                        >
                            Ubah password akun administrator.
                        </div>

                    </div>

                </a>


            </div>


        <?php endif; ?>


    </div>


</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>
