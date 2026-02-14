<?php
// includes/messages.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

/**
 * Envoyer un message
 */
function sendMessage($sender_id, $receiver_id, $content) {
    $pdo = getPDO();
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    return $stmt->execute([$sender_id, $receiver_id, $content]);
}

/**
 * Récupérer les conversations de l'utilisateur connecté
 * Retourne un tableau avec pour chaque autre utilisateur :
 * - user_id
 * - full_name
 * - avatar
 * - last_message
 * - last_time
 * - unread_count
 */
function getConversations($user_id) {
    $pdo = getPDO();
    // On récupère tous les messages où l'utilisateur est soit expéditeur soit destinataire,
    // groupés par l'autre utilisateur, en prenant le dernier message et le nombre de non lus.
    $sql = "SELECT 
                other_user_id,
                u.full_name,
                u.avatar,
                (SELECT content FROM messages 
                 WHERE (sender_id = ? AND receiver_id = other_user_id) 
                    OR (sender_id = other_user_id AND receiver_id = ?) 
                 ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM messages 
                 WHERE (sender_id = ? AND receiver_id = other_user_id) 
                    OR (sender_id = other_user_id AND receiver_id = ?) 
                 ORDER BY created_at DESC LIMIT 1) as last_time,
                (SELECT COUNT(*) FROM messages 
                 WHERE sender_id = other_user_id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM (
                SELECT DISTINCT 
                    CASE 
                        WHEN sender_id = ? THEN receiver_id 
                        ELSE sender_id 
                    END as other_user_id
                FROM messages
                WHERE sender_id = ? OR receiver_id = ?
            ) as others
            JOIN users u ON u.id = other_user_id
            ORDER BY last_time DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    return $stmt->fetchAll();
}

/**
 * Récupérer les messages entre deux utilisateurs
 */
function getMessages($user1, $user2) {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM messages 
                           WHERE (sender_id = ? AND receiver_id = ?) 
                              OR (sender_id = ? AND receiver_id = ?) 
                           ORDER BY created_at ASC");
    $stmt->execute([$user1, $user2, $user2, $user1]);
    return $stmt->fetchAll();
}

/**
 * Marquer les messages comme lus pour un expéditeur et un destinataire
 */
function markAsRead($receiver_id, $sender_id) {
    $pdo = getPDO();
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND is_read = 0");
    return $stmt->execute([$receiver_id, $sender_id]);
}

/**
 * Compter les messages non lus pour l'utilisateur
 */
function countUnreadMessages($user_id) {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}