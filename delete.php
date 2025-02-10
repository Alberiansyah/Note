<?php
require __DIR__ . '/koneksi/koneksi.php';
require __DIR__ . '/functions/functions.php';

$functions = new Functions($pdo);

$id = $_GET['id'] ?? null;
if ($id) {
    $functions->deleteNote($id);
}

header("Location: index.php");
exit;
