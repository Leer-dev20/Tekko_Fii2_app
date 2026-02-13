<?php
// includes/auth.php
require_once __DIR__ . '/../config/database.php';

/**
 * Démarre la session si elle n'est pas déjà active
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Vérifie si un utilisateur est connecté
 */
function isLoggedIn(): bool {
    initSession();
    return isset($_SESSION['user_id']);
}

/**
 * Retourne l'ID de l'utilisateur connecté (ou null)
 */
function getUserId(): ?int {
    initSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Retourne les infos de l'utilisateur connecté
 */
function getUser() {
    initSession();
    if (!isLoggedIn()) return null;
    
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT id, email, full_name, user_type, avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Connecte un utilisateur
 */
function loginUser(int $userId, string $email, string $fullName, string $userType) {
    initSession();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_type'] = $userType;
    session_regenerate_id(true);
}

/**
 * Déconnecte l'utilisateur
 */
function logoutUser() {
    initSession();
    $_SESSION = [];
    session_destroy();
}

/**
 * Redirige si non connecté
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: connexion.php');
        exit;
    }
}