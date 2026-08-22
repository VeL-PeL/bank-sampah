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

<style>

/* ==================================================
   PENGATURAN
================================================== */

.settings-page {
    margin-top: 28px;
    max-width: 1000px;
}


/* ==================================================
   ERROR
================================================== */

.settings-alert {
    display: flex;
    align-items: center;
    gap: 12px;

    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;

    padding: 15px 18px;
    border-radius: 12px;

    margin-bottom: 22px;

    font-size: 14px;
    font-weight: 600;
}


/* ==================================================
   PROFILE HERO
================================================== */

.profile-card {
    position: relative;

    background: white;

    border-radius: 18px;

    padding: 28px;

    margin-bottom: 22px;

    box-shadow:
        0 8px 30px rgba(15, 23, 42, 0.06);

    overflow: hidden;
}


/* garis hijau atas */

.profile-card::before {
    content: "";

    position: absolute;

    top: 0;
    left: 0;
    right: 0;

    height: 5px;

    background: #166534;
}


/* profile header */

.profile-header {
    display: flex;
    align-items: center;

    gap: 20px;

    padding-bottom: 25px;

    border-bottom: 1px solid #eef2f7;
}


/* avatar */

.profile-avatar {
    width: 72px;
    height: 72px;

    flex-shrink: 0;

    border-radius: 18px;

    background:
        linear-gradient(
            135deg,
            #166534,
            #22c55e
        );

    color: white;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 28px;
    font-weight: 800;

    box-shadow:
        0 8px 20px rgba(22, 101, 52, 0.20);
}


.profile-title h2 {
    margin: 0;

    color: #111827;

    font-size: 20px;
    font-weight: 750;
}


.profile-title p {
    margin: 6px 0 0;

    color: #6b7280;

    font-size: 14px;
}


/* ==================================================
   PROFILE INFO
================================================== */

.profile-info {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

    margin-top: 22px;
}


.info-item {
    background: #f8fafc;

    border: 1px solid #eef2f7;

    border-radius: 12px;

    padding: 16px;
}


.info-label {
    color: #64748b;

    font-size: 12px;

    font-weight: 600;

    text-transform: uppercase;

    letter-spacing: .4px;

    margin-bottom: 7px;
}


.info-value {
    color: #111827;

    font-size: 14px;

    font-weight: 700;

    word-break: break-word;
}


.role-badge {
    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 10px;

    border-radius: 20px;

    background: #dcfce7;

    color: #166534;

    font-size: 12px;

    font-weight: 700;
}


/* ==================================================
   SETTINGS CARD
================================================== */

.settings-card {
    background: white;

    border-radius: 18px;

    padding: 28px;

    box-shadow:
        0 8px 30px rgba(15, 23, 42, 0.06);
}


.settings-heading {
    margin-bottom: 20px;
}


.settings-heading h2 {
    margin: 0;

    color: #111827;

    font-size: 20px;
}


.settings-heading p {
    margin: 6px 0 0;

    color: #6b7280;

    font-size: 14px;
}


/* ==================================================
   SETTINGS ITEM
================================================== */

.setting-link {
    display: flex;

    align-items: center;

    gap: 16px;

    padding: 17px;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    text-decoration: none;

    color: #111827;

    margin-bottom: 12px;

    transition:
        .2s ease;

    background: white;
}


.setting-link:last-child {
    margin-bottom: 0;
}


.setting-link:hover {
    border-color: #bbf7d0;

    background: #f0fdf4;

    transform: translateY(-2px);

    box-shadow:
        0 6px 18px rgba(22, 101, 52, 0.08);
}


/* icon */

.setting-icon {
    width: 48px;
    height: 48px;

    flex-shrink: 0;

    border-radius: 12px;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 21px;
}


/* edit */

.setting-icon.profile {
    background: #dcfce7;
}


/* password */

.setting-icon.password {
    background: #fef3c7;
}


/* text */

.setting-content {
    flex: 1;
}


.setting-title {
    color: #111827;

    font-size: 15px;

    font-weight: 700;

    margin-bottom: 4px;
}


.setting-description {
    color: #6b7280;

    font-size: 13px;

    line-height: 1.5;
}


/* arrow */

.setting-arrow {
    color: #94a3b8;

    font-size: 20px;

    transition: .2s ease;
}


.setting-link:hover .setting-arrow {
    color: #166534;

    transform: translateX(3px);
}


/* ==================================================
   RESPONSIVE
================================================== */

@media (max-width: 700px) {

    .settings-page {
        margin-top: 20px;
    }


    .profile-card,
    .settings-card {
        padding: 20px;

        border-radius: 14px;
    }


    .profile-header {
        align-items: flex-start;
    }


    .profile-avatar {
        width: 58px;
        height: 58px;

        border-radius: 14px;

        font-size: 23px;
    }


    .profile-info {
        grid-template-columns: 1fr;
    }


    .setting-link {
        padding: 14px;
    }

}


@media (max-width: 480px) {

    .profile-header {
        gap: 14px;
    }


    .profile-title h2 {
        font-size: 17px;
    }


    .profile-title p {
        font-size: 12px;
    }


    .setting-icon {
        width: 42px;
        height: 42px;

        font-size: 18px;
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
                    Pengaturan
                </h1>

                <p>
                    Kelola akun dan keamanan administrator.
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
                            ?? 'Admin'
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

    <div class="settings-page">


        <?php if ($error !== ''): ?>

            <div class="settings-alert">

                ⚠️

                <span>
                    <?= htmlspecialchars($error) ?>
                </span>

            </div>

        <?php endif; ?>



        <?php if ($admin): ?>


            <!-- ==================================================
                 PROFILE
            ================================================== -->

            <div class="profile-card">


                <div class="profile-header">


                    <div class="profile-avatar">

                        <?= strtoupper(
                            substr(
                                $admin['nama'],
                                0,
                                1
                            )
                        ) ?>

                    </div>


                    <div class="profile-title">

                        <h2>
                            Profil Administrator
                        </h2>

                        <p>
                            Informasi akun administrator yang sedang login.
                        </p>

                    </div>


                </div>



                <!-- INFO -->

                <div class="profile-info">


                    <!-- NAMA -->

                    <div class="info-item">

                        <div class="info-label">
                            Nama
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $admin['nama']
                            ) ?>

                        </div>

                    </div>



                    <!-- EMAIL -->

                    <div class="info-item">

                        <div class="info-label">
                            Email
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $admin['email']
                            ) ?>

                        </div>

                    </div>



                    <!-- ROLE -->

                    <div class="info-item">

                        <div class="info-label">
                            Role
                        </div>

                        <div class="info-value">

                            <span class="role-badge">

                                🛡️

                                Administrator

                            </span>

                        </div>

                    </div>


                </div>


            </div>



            <!-- ==================================================
                 PENGATURAN AKUN
            ================================================== -->

            <div class="settings-card">


                <div class="settings-heading">

                    <h2>
                        Pengaturan Akun
                    </h2>

                    <p>
                        Kelola informasi profil dan keamanan akun kamu.
                    </p>

                </div>



                <!-- EDIT PROFIL -->

                <a
                    href="profil.php"
                    class="setting-link"
                >

                    <div class="setting-icon profile">
                        👤
                    </div>


                    <div class="setting-content">

                        <div class="setting-title">
                            Edit Profil
                        </div>

                        <div class="setting-description">
                            Ubah nama dan email administrator.
                        </div>

                    </div>


                    <div class="setting-arrow">
                        →
                    </div>

                </a>



                <!-- GANTI PASSWORD -->

                <a
                    href="password.php"
                    class="setting-link"
                >

                    <div class="setting-icon password">
                        🔐
                    </div>


                    <div class="setting-content">

                        <div class="setting-title">
                            Ganti Password
                        </div>

                        <div class="setting-description">
                            Ubah password untuk menjaga keamanan akun administrator.
                        </div>

                    </div>


                    <div class="setting-arrow">
                        →
                    </div>

                </a>


            </div>


        <?php endif; ?>


    </div>


</main>



<?php

require_once __DIR__ . '/../../includes/footer.php';

?>