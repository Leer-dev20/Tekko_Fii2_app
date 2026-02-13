<?php
require_once 'includes/auth.php';
require_once 'includes/messages.php';
requireAuth();

$userId = getUserId();
$conversations = getConversations($userId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma messagerie - Tekko-Fii</title>
    <!-- Mêmes CSS que les autres pages -->
    <?php include 'includes/head.php'; // vous pouvez factoriser le head ?>
</head>
<body>
    <?php include 'includes/header.php'; // header dynamique ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Ma messagerie</h1>

        <?php if (empty($conversations)): ?>
            <p class="text-gray-500">Vous n'avez aucune conversation pour le moment.</p>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <?php foreach ($conversations as $conv): ?>
                    <a href="conversation.php?user=<?= $conv['user_id'] ?>" 
                       class="block border-b border-gray-200 hover:bg-gray-50 transition <?= $conv['unread_count'] > 0 ? 'bg-blue-50' : '' ?>">
                        <div class="flex items-center p-4">
                            <div class="flex-shrink-0 mr-4">
                                <?php if ($conv['avatar']): ?>
                                    <img src="<?= htmlspecialchars($conv['avatar']) ?>" alt="" class="w-12 h-12 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center">
                                        <i class="ri-user-line text-gray-600"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline">
                                    <h3 class="text-lg font-semibold truncate"><?= htmlspecialchars($conv['name']) ?></h3>
                                    <span class="text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($conv['last_time'])) ?></span>
                                </div>
                                <p class="text-sm text-gray-600 truncate">
                                    <?= $conv['last_sender_id'] == $userId ? 'Vous : ' : '' ?>
                                    <?= htmlspecialchars($conv['last_message']) ?>
                                </p>
                            </div>
                            <?php if ($conv['unread_count'] > 0): ?>
                                <div class="ml-4 bg-primary text-white rounded-full w-6 h-6 flex items-center justify-center text-sm">
                                    <?= $conv['unread_count'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>