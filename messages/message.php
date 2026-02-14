<?php
// message.php
require_once 'includes/auth.php';
require_once 'includes/messages.php';

if (!isLoggedIn()) {
    header('Location: connexion.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$current_user_id = getUserId();
$to = isset($_GET['to']) ? (int)$_GET['to'] : 0;

// Vérifier que le destinataire existe et n'est pas l'utilisateur courant
if ($to <= 0 || $to == $current_user_id) {
    header('Location: index.php');
    exit;
}

// Récupérer les infos du destinataire
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE id = ?");
$stmt->execute([$to]);
$receiver = $stmt->fetch();
if (!$receiver) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['message'] ?? '');
    if (empty($content)) {
        $error = 'Le message ne peut pas être vide.';
    } else {
        if (sendMessage($current_user_id, $to, $content)) {
            $success = 'Message envoyé !';
            // Optionnel : rediriger vers la conversation
            header('Location: conversation.php?user=' . $to);
            exit;
        } else {
            $error = 'Erreur lors de l\'envoi.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Envoyer un message - Tekko-Fii</title>
    <!-- Inclure les CSS, header, etc. -->
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-4">Envoyer un message à <?= htmlspecialchars($receiver['full_name']) ?></h1>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded mb-4"><?= $success ?></div>
        <?php endif; ?>
        <form method="post" class="max-w-lg">
            <textarea name="message" rows="5" class="w-full border rounded p-2" placeholder="Votre message..."></textarea>
            <button type="submit" class="mt-2 bg-primary text-white px-4 py-2 rounded">Envoyer</button>
        </form>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>