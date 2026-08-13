<?php

require_once __DIR__ . '/../includes/auth.php';

require_role('nasabah');

$page_title = 'Penarikan';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

?>

<main class="main-content">

    <div class="topbar">

        <div class="page-title">
            <h1>Penarikan</h1>
            <p>Ajukan penarikan saldo tabungan sampah.</p>
        </div>

    </div>

    <div class="content-card">
        <h2>Penarikan Saldo</h2>

        <p>
            Fitur penarikan akan dibuat pada tahap berikutnya.
        </p>
    </div>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>