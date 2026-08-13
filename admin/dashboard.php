<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/admin-auth.php';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard Admin - Bank Sampah</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f0fdf4;
            color: #1f2937;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            color: #166534;
        }

        .header p {
            color: #6b7280;
            margin-bottom: 0;
        }

        .welcome {
            margin-top: 20px;
            padding: 20px;
            background: #dcfce7;
            border-radius: 10px;
            color: #166534;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .menu-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-decoration: none;
            color: #1f2937;
            box-shadow: 0 10px 25px rgba(0,0,0,.06);
            transition: .2s;
        }

        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0,0,0,.10);
        }

        .icon {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .menu-card h3 {
            margin: 0 0 8px;
            color: #166534;
        }

        .menu-card p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .logout {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 20px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .logout:hover {
            background: #b91c1c;
        }

        @media (max-width: 700px) {

            .container {
                margin: 20px auto;
                padding: 15px;
            }

            .menu {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>
            Dashboard Admin
        </h1>

        <p>
            Selamat datang di Sistem Bank Sampah.
        </p>

        <div class="welcome">

            Halo,
            <strong>
                <?= htmlspecialchars($_SESSION['nama']) ?>
            </strong>

            👋

            <br>

            Kamu login sebagai
            <strong>Administrator</strong>.

        </div>

    </div>


    <div class="menu">

        <!-- NASABAH -->

        <a
            href="nasabah/index.php"
            class="menu-card"
        >

            <div class="icon">
                👥
            </div>

            <h3>
                Nasabah
            </h3>

            <p>
                Kelola data nasabah Bank Sampah.
            </p>

        </a>


        <!-- JENIS SAMPAH -->

        <a
            href="sampah/index.php"
            class="menu-card"
        >

            <div class="icon">
                ♻️
            </div>

            <h3>
                Jenis Sampah
            </h3>

            <p>
                Kelola jenis dan harga sampah.
            </p>

        </a>


        <!-- TRANSAKSI -->

        <a
            href="transaksi/index.php"
            class="menu-card"
        >

            <div class="icon">
                📦
            </div>

            <h3>
                Transaksi Setor
            </h3>

            <p>
                Periksa dan proses setoran nasabah.
            </p>

        </a>


        <!-- PENARIKAN -->

        <a
            href="penarikan/index.php"
            class="menu-card"
        >

            <div class="icon">
                💰
            </div>

            <h3>
                Penarikan
            </h3>

            <p>
                Kelola pengajuan penarikan saldo.
            </p>

        </a>


        <!-- LAPORAN -->

        <a
            href="laporan/index.php"
            class="menu-card"
        >

            <div class="icon">
                📊
            </div>

            <h3>
                Laporan
            </h3>

            <p>
                Lihat laporan aktivitas Bank Sampah.
            </p>

        </a>

    </div>


    <a
        href="../auth/logout.php"
        class="logout"
    >
        Logout
    </a>

</div>

</body>

</html>