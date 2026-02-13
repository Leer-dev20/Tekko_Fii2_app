<?php
require_once 'includes/auth.php';
require_once 'includes/messages.php';
requireAuth();

$userId = getUserId();
$otherId = isset($_GET['user']) ? (int)$_GET['user'] : 0;

if ($otherId <= 0) {
    header('Location: messagerie.php');
    exit;
}

// Récupérer les infos de l'autre utilisateur
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id, full_name, avatar FROM users WHERE id = ?");
$stmt->execute([$otherId]);
$otherUser = $stmt->fetch();
if (!$otherUser) {
    header('Location: messagerie.php');
    exit;
}

// Marquer les messages comme lus
markMessagesAsRead($userId, $otherId);

// Récupérer tous les messages
$messages = getMessages($userId, $otherId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Conversation avec <?= htmlspecialchars($otherUser['full_name']) ?> - Tekko-Fii</title>
    <!-- CSS -->
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8 max-w-3xl">
        <div class="mb-4">
            <a href="messagerie.php" class="text-primary hover:underline">&larr; Retour à la messagerie</a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gray-50 p-4 border-b flex items-center">
                <div class="mr-3">
                    <?php if ($otherUser['avatar']): ?>
                        <img src="<?= htmlspecialchars($otherUser['avatar']) ?>" alt="" class="w-10 h-10 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                            <i class="ri-user-line text-gray-600"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h2 class="text-xl font-semibold"><?= htmlspecialchars($otherUser['full_name']) ?></h2>
            </div>

            <div class="p-4 h-96 overflow-y-auto flex flex-col space-y-3" id="messageContainer">
                <?php foreach ($messages as $msg): ?>
                    <div class="flex <?= $msg['sender_id'] == $userId ? 'justify-end' : 'justify-start' ?>">
                        <div class="max-w-xs md:max-w-md rounded-lg p-3 <?= $msg['sender_id'] == $userId ? 'bg-primary text-white' : 'bg-gray-200 text-gray-800' ?>">
                            <p><?= nl2br(htmlspecialchars($msg['content'])) ?></p>
                            <p class="text-xs mt-1 <?= $msg['sender_id'] == $userId ? 'text-blue-100' : 'text-gray-500' ?>">
                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="border-t p-4">
                <form id="messageForm" class="flex space-x-2">
                    <input type="hidden" name="receiver_id" value="<?= $otherId ?>">
                    <input type="text" name="content" placeholder="Votre message..." 
                           class="flex-1 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary" required>
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:bg-blue-600 transition">Envoyer</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Envoi du message en AJAX
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('envoyer_message.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Ajouter le message dans la conversation
                    const container = document.getElementById('messageContainer');
                    const input = this.querySelector('input[name="content"]');
                    const newMsg = document.createElement('div');
                    newMsg.className = 'flex justify-end';
                    newMsg.innerHTML = `
                        <div class="max-w-xs md:max-w-md rounded-lg p-3 bg-primary text-white">
                            <p>${input.value.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p>
                            <p class="text-xs mt-1 text-blue-100">${new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'})}</p>
                        </div>
                    `;
                    container.appendChild(newMsg);
                    container.scrollTop = container.scrollHeight;
                    input.value = '';
                } else {
                    alert('Erreur lors de l\'envoi');
                }
            });
        });

        // Scroll en bas au chargement
        const container = document.getElementById('messageContainer');
        container.scrollTop = container.scrollHeight;
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>