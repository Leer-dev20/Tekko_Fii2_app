<?php
// envoyer_message.php
require_once 'includes/auth.php';
require_once 'includes/messages.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$senderId = getUserId();
$receiverId = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$content = trim($_POST['content'] ?? '');

if ($receiverId <= 0 || empty($content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
    exit;
}

// Vérifier que le destinataire existe
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$receiverId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Destinataire introuvable']);
    exit;
}

$success = sendMessage($senderId, $receiverId, $content);
echo json_encode(['success' => $success]);