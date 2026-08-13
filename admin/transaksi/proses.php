<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';

if (!verify_csrf_token($csrfToken)) {
    die('Permintaan tidak valid.');
}

$id = (int) ($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

if (!in_array($status, ['diterima', 'ditolak'], true)) {
    header('Location: detail.php?id=' . $id);
    exit;
}

try {

    $stmt = $pdo->prepare("
        UPDATE setoran
        SET status = :status
        WHERE id = :id
        AND status = 'menunggu'
    ");

    $stmt->execute([
        'status' => $status,
        'id' => $id
    ]);

    header('Location: detail.php?id=' . $id . '&success=1');
    exit;

} catch (PDOException $e) {

    header('Location: detail.php?id=' . $id . '&error=1');
    exit;
}