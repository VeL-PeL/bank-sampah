<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';


// ==================================================
// CEK METHOD
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}


// ==================================================
// AMBIL DATA POST
// ==================================================

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$aksi = $_POST['aksi'] ?? '';


// ==================================================
// VALIDASI ID
// ==================================================

if ($id <= 0) {
    header('Location: index.php?error=ID penarikan tidak valid');
    exit;
}


// ==================================================
// VALIDASI AKSI
// ==================================================

if ($aksi !== 'terima' && $aksi !== 'tolak') {
    header(
        'Location: detail.php?id=' . $id . '&error=Aksi tidak valid'
    );
    exit;
}


try {

    // ==================================================
    // MULAI TRANSACTION
    // ==================================================

    $pdo->beginTransaction();


    // ==================================================
    // AMBIL DATA PENARIKAN
    // ==================================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            nasabah_id,
            jumlah,
            status
        FROM penarikan
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $penarikan = $stmt->fetch(PDO::FETCH_ASSOC);


    // ==================================================
    // CEK DATA
    // ==================================================

    if (!$penarikan) {

        $pdo->rollBack();

        header(
            'Location: index.php?error=Data penarikan tidak ditemukan'
        );

        exit;
    }


    // ==================================================
    // CEK STATUS
    // ==================================================

    if ($penarikan['status'] !== 'pending') {

        $pdo->rollBack();

        header(
            'Location: detail.php?id=' .
            $id .
            '&error=Penarikan sudah diproses'
        );

        exit;
    }


    // ==================================================
    // JIKA DITOLAK
    // ==================================================

    if ($aksi === 'tolak') {

        $stmt = $pdo->prepare("
            UPDATE penarikan
            SET status = 'ditolak'
            WHERE id = ?
            AND status = 'pending'
        ");

        $stmt->execute([$id]);


        $pdo->commit();


        header(
            'Location: detail.php?id=' .
            $id .
            '&success=Penarikan berhasil ditolak'
        );

        exit;
    }


    // ==================================================
    // JIKA DITERIMA
    // ==================================================

    $nasabahId = (int) $penarikan['nasabah_id'];
    $jumlah = (float) $penarikan['jumlah'];


    // ==================================================
    // AMBIL SALDO NASABAH
    // ==================================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            saldo
        FROM saldo
        WHERE nasabah_id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$nasabahId]);

    $saldoData = $stmt->fetch(PDO::FETCH_ASSOC);


    // ==================================================
    // CEK SALDO
    // ==================================================

    if (!$saldoData) {

        $pdo->rollBack();

        header(
            'Location: detail.php?id=' .
            $id .
            '&error=Saldo nasabah tidak ditemukan'
        );

        exit;
    }


    $saldoSekarang = (float) $saldoData['saldo'];


    if ($saldoSekarang < $jumlah) {

        $pdo->rollBack();

        header(
            'Location: detail.php?id=' .
            $id .
            '&error=Saldo tidak mencukupi'
        );

        exit;
    }


    // ==================================================
    // HITUNG SALDO BARU
    // ==================================================

    $saldoBaru = $saldoSekarang - $jumlah;


    // ==================================================
    // UPDATE SALDO
    // ==================================================

    $stmt = $pdo->prepare("
        UPDATE saldo
        SET saldo = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $saldoBaru,
        $saldoData['id']
    ]);


    // ==================================================
    // UPDATE STATUS PENARIKAN
    // ==================================================

    $stmt = $pdo->prepare("
        UPDATE penarikan
        SET
            status = 'diterima',
            tanggal_diproses = NOW()
        WHERE id = ?
        AND status = 'pending'
    ");

    $stmt->execute([$id]);


    // ==================================================
    // SELESAI
    // ==================================================

    $pdo->commit();


    header(
        'Location: detail.php?id=' .
        $id .
        '&success=Penarikan berhasil diterima'
    );

    exit;


} catch (PDOException $e) {

    // ==================================================
    // JIKA ERROR
    // ==================================================

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    header(
        'Location: detail.php?id=' .
        $id .
        '&error=Terjadi kesalahan saat memproses penarikan'
    );

    exit;
}