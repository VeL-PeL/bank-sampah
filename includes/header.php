<?php

$page_title = $page_title ?? 'Bank Sampah';

require_once __DIR__ . '/auth.php';

require_login();

$nama_user = $_SESSION['nama'] ?? 'User';
$role_user = $_SESSION['role'] ?? 'user';

$initial = strtoupper(
    substr(trim($nama_user), 0, 1)
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($page_title) ?> - Bank Sampah
    </title>

    <link rel="stylesheet" href="/bank-sampah/assets/css/style.css">

</head>

<body>

<div class="app">

    <!-- MOBILE BUTTON -->
    <button
        type="button"
        class="mobile-menu-btn"
        id="mobileMenuBtn"
        aria-label="Buka menu"
    >
        ☰
    </button>

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>