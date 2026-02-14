<?php
// Inclusion des fichiers nécessaires
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/favorites.php';

// Vérifier la connexion
if (!isLoggedIn()) {
    header('Location: connexion.php?redirect=dashboard.php');
    exit;
}

$pdo = getPDO();
$userId = $_SESSION['user_id'];
$user = getUser(); // Informations de base de l'utilisateur
$userType = $user['user_type']; // 'developer' ou 'client'

// --- TRAITEMENT DU FORMULAIRE DE MISE À JOUR DU PROFIL ---
$updateSuccess = '';
$updateError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hourlyRate = isset($_POST['hourly_rate']) ? floatval($_POST['hourly_rate']) : 0;
    $location = trim($_POST['location'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Validation simple
    if (empty($fullName) || empty($email)) {
        $updateError = 'Le nom et l\'email sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $updateError = 'Email invalide.';
    } else {
        // Mise à jour de la table users
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->execute([$fullName, $email, $userId]);

        // Si l'utilisateur est développeur, mettre à jour la table developers
        if ($userType == 'developer') {
            $stmt = $pdo->prepare("UPDATE developers SET hourly_rate = ?, location = ?, title = ?, bio = ? WHERE user_id = ?");
            $stmt->execute([$hourlyRate, $location, $title, $bio, $userId]);
        }

        $updateSuccess = 'Profil mis à jour avec succès.';
        // Recharger les infos de l'utilisateur
        $user = getUser();
    }
}

// --- RÉCUPÉRATION DES DONNÉES SPÉCIFIQUES ---
// Données développeur (si c'est un développeur)
$developerData = null;
if ($userType == 'developer') {
    $stmt = $pdo->prepare("SELECT * FROM developers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $developerData = $stmt->fetch();
}

// Projets déposés (pour développeur)
$projects = [];
if ($userType == 'developer') {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM projects p LEFT JOIN categories c ON p.category_id = c.id WHERE p.developer_id = (SELECT id FROM developers WHERE user_id = ?) ORDER BY p.created_at DESC");
    $stmt->execute([$userId]);
    $projects = $stmt->fetchAll();
}

// Favoris de l'utilisateur (développeurs et projets)
$favorites = getUserFavorites($userId);
$favDevelopers = [];
$favProjects = [];
foreach ($favorites as $fav) {
    if ($fav['item_type'] == 'developer') {
        $stmt = $pdo->prepare("SELECT u.full_name, u.avatar, d.title FROM developers d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([$fav['item_id']]);
        $dev = $stmt->fetch();
        if ($dev) $favDevelopers[] = $dev;
    } else {
        $stmt = $pdo->prepare("SELECT title, price, image_path FROM projects WHERE id = ?");
        $stmt->execute([$fav['item_id']]);
        $proj = $stmt->fetch();
        if ($proj) $favProjects[] = $proj;
    }
}

// Messages récents reçus
$stmt = $pdo->prepare("SELECT m.*, u.full_name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = ? ORDER BY m.created_at DESC LIMIT 5");
$stmt->execute([$userId]);
$recentMessages = $stmt->fetchAll();

// Nombre de messages non lus (pour le badge)
$unread = getUnreadMessageCount($userId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon tableau de bord - Tekko-Fii</title>
    <link rel="shortcut icon" href="img/Blue_Purple_Modern_Gradient_Computer_Service_and_Repair_Logo__1_-removebg-preview.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4285F4',secondary:'#34A853'},borderRadius:{button:'8px'}}}}</script>
    <style>:where([class^="ri-"])::before { content: "\f3c2"; } body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-white min-h-screen flex flex-col">
    <!-- Header identique -->
    <header class="w-full py-4 px-6 flex justify-between items-center border-b border-gray-100">
        <a href="index.php" class="text-2xl font-bold font-['Pacifico'] text-gray-900">Tekko-Fii</a>
        <nav class="hidden md:flex space-x-8">
            <a href="projet.php" class="text-gray-800 hover:text-primary font-medium">Projets</a>
            <a href="liste_dev.php" class="text-gray-800 hover:text-primary font-medium">Développeurs</a>
            <a href="entreprise.php" class="text-gray-800 hover:text-primary font-medium">Entreprise</a>
        </nav>
        <div class="flex items-center space-x-4">
            <a href="messagerie.php" class="relative text-gray-700 hover:text-primary">
                <i class="ri-message-3-line text-xl"></i>
                <?php if ($unread > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?= $unread > 9 ? '9+' : $unread ?></span>
                <?php endif; ?>
            </a>
            <a href="dashboard.php" class="flex items-center space-x-2 hover:text-primary">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                <?php else: ?>
                    <i class="ri-account-circle-line text-2xl text-gray-600"></i>
                <?php endif; ?>
                <span class="text-gray-800 font-medium hidden sm:inline"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            </a>
            <a href="deconnexion.php" class="bg-red-500 text-white px-4 py-2 rounded-button font-medium hover:bg-red-600">Déconnexion</a>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-6 py-8">
        <h1 class="text-2xl font-bold mb-6">Mon tableau de bord</h1>

        <!-- Messages de succès / erreur -->
        <?php if ($updateSuccess): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6"><?= $updateSuccess ?></div>
        <?php endif; ?>
        <?php if ($updateError): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6"><?= $updateError ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- COLONNE GAUCHE : Formulaire de modification du profil -->
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4">Mon profil</h2>
                    <form method="post" class="space-y-4">
                        <input type="hidden" name="update_profile" value="1">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>

                        <?php if ($userType == 'developer' && $developerData): ?>
                            <!-- Champs supplémentaires pour développeur -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tarif horaire (MAD)</label>
                                <input type="number" name="hourly_rate" value="<?= $developerData['hourly_rate'] ?>" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Localisation</label>
                                <input type="text" name="location" value="<?= htmlspecialchars($developerData['location'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Titre / spécialité</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($developerData['title'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                                <textarea name="bio" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($developerData['bio'] ?? '') ?></textarea>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-blue-600 w-full">Mettre à jour</button>
                    </form>
                </div>
            </div>

            <!-- COLONNE DROITE : Statistiques, projets, favoris, messages -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Mini statistiques -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                        <i class="ri-star-line text-3xl text-yellow-400 mb-2"></i>
                        <div class="text-2xl font-bold"><?= count($favorites) ?></div>
                        <div class="text-sm text-gray-500">Favoris</div>
                    </div>
                    <?php if ($userType == 'developer'): ?>
                        <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                            <i class="ri-code-box-line text-3xl text-primary mb-2"></i>
                            <div class="text-2xl font-bold"><?= count($projects) ?></div>
                            <div class="text-sm text-gray-500">Projets déposés</div>
                        </div>
                    <?php endif; ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                        <i class="ri-message-3-line text-3xl text-green-500 mb-2"></i>
                        <div class="text-2xl font-bold"><?= $unread ?></div>
                        <div class="text-sm text-gray-500">Messages non lus</div>
                    </div>
                </div>

                <!-- Si développeur : liste des projets déposés -->
                <?php if ($userType == 'developer' && !empty($projects)): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4">Mes projets déposés</h2>
                    <div class="space-y-3">
                        <?php foreach ($projects as $proj): ?>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <div>
                                    <h3 class="font-medium"><?= htmlspecialchars($proj['title']) ?></h3>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($proj['category_name'] ?? 'Non catégorisé') ?> - <?= number_format($proj['price'], 0) ?> MAD</p>
                                </div>
                                <div>
                                    <a href="#" class="text-sm text-primary hover:underline">Modifier</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Favoris -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4">Mes favoris</h2>
                    <?php if (empty($favDevelopers) && empty($favProjects)): ?>
                        <p class="text-gray-500">Vous n'avez aucun favori pour le moment.</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php if (!empty($favDevelopers)): ?>
                                <h3 class="font-medium text-gray-700">Développeurs</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <?php foreach ($favDevelopers as $dev): ?>
                                        <div class="flex items-center space-x-2 border border-gray-100 rounded p-2">
                                            <?php if (!empty($dev['avatar'])): ?>
                                                <img src="<?= htmlspecialchars($dev['avatar']) ?>" alt="" class="w-10 h-10 rounded-full object-cover">
                                            <?php else: ?>
                                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center"><i class="ri-user-line"></i></div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="font-medium"><?= htmlspecialchars($dev['full_name']) ?></p>
                                                <p class="text-xs text-gray-500"><?= htmlspecialchars($dev['title'] ?? '') ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($favProjects)): ?>
                                <h3 class="font-medium text-gray-700">Projets</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <?php foreach ($favProjects as $proj): ?>
                                        <div class="flex items-center space-x-2 border border-gray-100 rounded p-2">
                                            <img src="<?= htmlspecialchars($proj['image_path'] ?? 'img/placeholder-project.jpg') ?>" alt="" class="w-10 h-10 object-cover rounded">
                                            <div>
                                                <p class="font-medium"><?= htmlspecialchars($proj['title']) ?></p>
                                                <p class="text-xs text-gray-500"><?= number_format($proj['price'], 0) ?> MAD</p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Messages récents -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold">Messages récents</h2>
                        <a href="messagerie.php" class="text-sm text-primary hover:underline">Voir tout</a>
                    </div>
                    <?php if (empty($recentMessages)): ?>
                        <p class="text-gray-500">Aucun message récent.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($recentMessages as $msg): ?>
                                <div class="border-b border-gray-100 pb-2">
                                    <p class="text-sm"><span class="font-medium"><?= htmlspecialchars($msg['sender_name']) ?></span> : <?= htmlspecialchars(mb_substr($msg['content'], 0, 60)) ?>...</p>
                                    <p class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white py-12 px-6">...</footer>
</body>
</html>