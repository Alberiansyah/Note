<?php
require __DIR__ . '/koneksi/koneksi.php';
require __DIR__ . '/vendor/autoload.php';

$functions = new Functions($pdo);

$id = $_GET['id'] ?? null;
if ($id && $functions->getNoteById($id)) {
    $functions->deleteNote($id);
}

header("Location: index.php");
exit;
