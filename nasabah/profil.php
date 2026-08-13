<?php

require_once __DIR__ . '/../includes/auth.php';

require_role('nasabah');

$page_title = 'Profil';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

?>

<main class="main-content">

    <div class="topbar">

        <div class="page-title">
            <h1>Profil</h1>
            <p>Informasi akun nasabah.</p>
        </div>

    </div>

    <div class="content-card">
        <h2>Profil Nasabah</h2>

        <p>
            Nama:
            <?= htmlspecialchars($_SESSION['nama']) ?>
        </p>

        <p>
            Email:
            <?= htmlspecialchars($_SESSION['email']) ?>
        </p>

    </div>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>