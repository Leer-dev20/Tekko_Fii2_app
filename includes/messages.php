<?php
// includes/messages.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

/**
 * Envoyer un message
 */
function sendMessage(int $senderId, int $receiverId, string $content): bool {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, receiver_id, content)
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$senderId, $receiverId, $content]);
}

/**
 * Récupérer les conversations de l'utilisateur connecté
 * Retourne un tableau avec les derniers messages et les infos de l'interlocuteur
 */
function getConversations(int $userId): array {
    $pdo = getPDO();
    // Récupère tous les interlocuteurs distincts avec qui l'utilisateur a échangé
    $sql = "
        SELECT DISTINCT 
            CASE 
                WHEN sender_id = ? THEN receiver_id
                ELSE sender_id
            END as other_user_id
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $userId, $userId]);
    $otherUserIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $conversations = [];
    foreach ($otherUserIds as $otherId) {
        // Dernier message entre les deux
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   u.full_name as other_name,
                   u.avatar as other_avatar
            FROM messages m
            JOIN users u ON (u.id = ?)
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?)
            ORDER BY m.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$otherId, $userId, $otherId, $otherId, $userId]);
        $lastMsg = $stmt->fetch();

        if ($lastMsg) {
            // Compter les messages non lus de l'autre utilisateur vers moi
            $stmtUnread = $pdo->prepare("
                SELECT COUNT(*) FROM messages
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
            ");
            $stmtUnread->execute([$otherId, $userId]);
            $unreadCount = $stmtUnread->fetchColumn();

            $conversations[] = [
                'user_id' => $otherId,
                'name' => $lastMsg['other_name'],
                'avatar' => $lastMsg['other_avatar'],
                'last_message' => $lastMsg['content'],
                'last_time' => $lastMsg['created_at'],
                'unread_count' => $unreadCount,
                'last_sender_id' => $lastMsg['sender_id']
            ];
        }
    }

    // Trier par date du dernier message
    usort($conversations, fn($a, $b) => strtotime($b['last_time']) - strtotime($a['last_time']));
    return $conversations;
}

/**
 * Récupérer les messages entre deux utilisateurs
 */
function getMessages(int $user1, int $user2): array {
    $pdo = getPDO();
    $sql = "
        SELECT m.*, 
               u_sender.full_name as sender_name,
               u_sender.avatar as sender_avatar
        FROM messages m
        JOIN users u_sender ON m.sender_id = u_sender.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?)
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user1, $user2, $user2, $user1]);
    return $stmt->fetchAll();
}

/**
 * Marquer les messages d'un expéditeur comme lus
 */
function markMessagesAsRead(int $receiverId, int $senderId): void {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        UPDATE messages
        SET is_read = 1
        WHERE receiver_id = ? AND sender_id = ? AND is_read = 0
    ");
    $stmt->execute([$receiverId, $senderId]);
}