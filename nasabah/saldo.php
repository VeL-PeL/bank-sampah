<?php

require_once __DIR__ . '/../includes/auth.php';

require_role('nasabah');

$page_title = 'Saldo';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

?>

<main class="main-content">

    <div class="topbar">

        <div class="page-title">
            <h1>Saldo</h1>
            <p>Informasi saldo tabungan sampah kamu.</p>
        </div>

    </div>

    <div class="content-card">
        <h2>Saldo Nasabah</h2>

        <p>
            Informasi saldo akan dibuat pada tahap berikutnya.
        </p>
    </div>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>    