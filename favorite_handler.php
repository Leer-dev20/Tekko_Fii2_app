<?php
require_once 'includes/auth.php';
require_once 'includes/favorites.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$userId = getUserId();
$type = $_POST['type'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!in_array($type, ['developer', 'project']) || $id <= 0 || !in_array($action, ['add', 'remove'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
    exit;
}

$success = false;
if ($action === 'add') {
    $success = addFavorite($userId, $type, $id);
} else {
    $success = removeFavorite($userId, $type, $id);
}

echo json_encode(['success' => $success]);