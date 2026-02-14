<?php
require_once 'includes/auth.php';
require_once 'includes/cart.php';

session_start(); // déjà fait dans auth.php normalement
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Veuillez vous connecter']);
    exit;
}

$projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
if ($projectId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Projet invalide']);
    exit;
}

// Récupérer les infos du projet
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id, title, price FROM projects WHERE id = ? AND status = 'published'");
$stmt->execute([$projectId]);
$project = $stmt->fetch();
if (!$project) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Projet introuvable']);
    exit;
}

addToCart($project['id'], $project['price'], $project['title']);
echo json_encode(['success' => true]);