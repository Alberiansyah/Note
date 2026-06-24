<?php
header('Content-Type: application/json');
require __DIR__ . '/koneksi/koneksi.php';
require __DIR__ . '/vendor/autoload.php';

$functions = new Functions($pdo);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'togglePin':
        $id = $_GET['id'] ?? 0;
        if ($id) {
            $functions->togglePin($id);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No ID']);
        }
        break;

    case 'toggleFav':
        $id = $_GET['id'] ?? 0;
        if ($id) {
            $functions->toggleFavorite($id);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No ID']);
        }
        break;

    case 'copyNote':
        $id = $_GET['id'] ?? 0;
        if ($id) {
            $functions->duplicateNote($id);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No ID']);
        }
        break;

    case 'getNote':
        $id = $_GET['id'] ?? 0;
        if ($id) {
            $note = $functions->getNoteById($id);
            echo json_encode($note);
        } else {
            echo json_encode(['success' => false, 'error' => 'No ID']);
        }
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}