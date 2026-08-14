<?php

require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$page_title = 'Dashboard Admin';

require_once __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../includes/sidebar.php';

?>

<main class="main-content">

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Dashboard
                </h1>

                <p>
                    Selamat datang kembali,
                    <?= htmlspecialchars($_SESSION['nama']) ?>!
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
                        <?= htmlspecialchars($_SESSION['nama']) ?>
                    </div>

                    <div class="user-role">
                        Administrator
                    </div>

                </div>

            </div>

        </div>

    </div>


    <div>

        <h2>
            Dashboard Admin
        </h2>

        <p style="margin-top: 10px;">
            Selamat datang di panel administrasi Bank Sampah.
        </p>

    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>