<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Seuls les développeurs connectés peuvent accéder
if (!isLoggedIn() || $_SESSION['user_type'] !== 'developer') {
    header('Location: connexion.php?redirect=parcourir.php');
    exit;
}

$pdo = getPDO();
$userId = getUserId();

// Récupérer l'ID du développeur (table developers)
$stmt = $pdo->prepare("SELECT id FROM developers WHERE user_id = ?");
$stmt->execute([$userId]);
$developer = $stmt->fetch();
if (!$developer) {
    // Normalement, un développeur a toujours une entrée, mais sécurité
    die("Erreur : profil développeur introuvable.");
}
$developerId = $developer['id'];

// Initialisation des variables
$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['projectName'] ?? '');
    $description = trim($_POST['features'] ?? '');
    $technologiesInput = trim($_POST['technologies'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    
    // Gestion des fichiers
    $zipFile = $_FILES['projectFile'] ?? null;
    $imageFile = $_FILES['imageFile'] ?? null; // optionnel, on peut l'ajouter dans le formulaire
    
    // Validations
    if (empty($title) || empty($description) || empty($technologiesInput) || $price <= 0) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif ($zipFile && $zipFile['error'] !== UPLOAD_ERR_OK) {
        $error = 'Erreur lors de l\'upload du fichier ZIP.';
    } elseif ($zipFile && !in_array($zipFile['type'], ['application/zip', 'application/x-zip-compressed'])) {
        $error = 'Le fichier doit être au format ZIP.';
    } elseif ($imageFile && $imageFile['error'] !== UPLOAD_ERR_OK && $imageFile['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = 'Erreur lors de l\'upload de l\'image.';
    } elseif ($imageFile && $imageFile['size'] > 0 && !in_array($imageFile['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
        $error = 'L\'image doit être au format JPG, PNG ou GIF.';
    } else {
        // Tout est OK, on procède à l'insertion
        try {
            $pdo->beginTransaction();
            
            // 1. Upload du fichier ZIP
            $zipPath = null;
            if ($zipFile && $zipFile['tmp_name']) {
                $uploadDir = 'uploads/projects/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $zipExtension = pathinfo($zipFile['name'], PATHINFO_EXTENSION);
                $zipFilename = uniqid('project_') . '.' . $zipExtension;
                $zipPath = $uploadDir . $zipFilename;
                move_uploaded_file($zipFile['tmp_name'], $zipPath);
            }
            
            // 2. Upload de l'image (si fournie)
            $imagePath = null;
            if ($imageFile && $imageFile['tmp_name'] && $imageFile['size'] > 0) {
                $uploadDir = 'uploads/projects/';
                $imageExtension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
                $imageFilename = uniqid('img_') . '.' . $imageExtension;
                $imagePath = $uploadDir . $imageFilename;
                move_uploaded_file($imageFile['tmp_name'], $imagePath);
            }
            
            // 3. Insérer le projet dans la table projects
            $stmt = $pdo->prepare("
                INSERT INTO projects (developer_id, title, description, price, image_path, zip_file_path, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'published', NOW())
            ");
            $stmt->execute([$developerId, $title, $description, $price, $imagePath, $zipPath]);
            $projectId = $pdo->lastInsertId();
            
            // 4. Traiter les technologies
            // On suppose que l'utilisateur a entré une liste de noms séparés par des virgules
            $techNames = array_map('trim', explode(',', $technologiesInput));
            foreach ($techNames as $techName) {
                if (empty($techName)) continue;
                // Vérifier si la compétence existe déjà
                $stmt = $pdo->prepare("SELECT id FROM skills WHERE name = ?");
                $stmt->execute([$techName]);
                $skillId = $stmt->fetchColumn();
                if (!$skillId) {
                    // Créer la compétence
                    $stmt = $pdo->prepare("INSERT INTO skills (name) VALUES (?)");
                    $stmt->execute([$techName]);
                    $skillId = $pdo->lastInsertId();
                }
                // Lier au projet
                $stmt = $pdo->prepare("INSERT INTO project_technologies (project_id, skill_id) VALUES (?, ?)");
                $stmt->execute([$projectId, $skillId]);
            }
            
            $pdo->commit();
            $success = 'Projet déposé avec succès !';
            // Optionnel : rediriger vers la page du projet
            // header('Location: projet.php?id=' . $projectId);
            // exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Une erreur est survenue : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déposer un projet - Tekko-Fii</title>
    <link rel="shortcut icon" href="img/Blue_Purple_Modern_Gradient_Computer_Service_and_Repair_Logo__1_-removebg-preview.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4285F4',
                        secondary: '#34A853'
                    },
                    borderRadius: {
                        button: '8px'
                    }
                }
            }
        }
    </script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body { font-family: 'Inter', sans-serif; }
        input:focus, textarea:focus { outline: none; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">

    <!-- Header dynamique -->
    <header class="w-full py-4 px-6 flex justify-between items-center border-b border-gray-100 bg-white">
        <a href="index.php" class="text-2xl font-bold font-['Pacifico'] text-gray-900">Tekko-Fii</a>
        <nav class="hidden md:flex space-x-8">
            <a href="projet.php" class="text-gray-800 hover:text-primary font-medium">Projets</a>
            <a href="liste_dev.php" class="text-gray-800 hover:text-primary font-medium">Développeurs</a>
            <a href="entreprise.php" class="text-gray-800 hover:text-primary font-medium">Entreprise</a>
        </nav>
        <div class="flex items-center space-x-4">
            <?php if (isLoggedIn()): 
                $user = getUser();
            ?>
                <div class="flex items-center space-x-2">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                    <?php else: ?>
                        <i class="ri-account-circle-line text-2xl text-gray-600"></i>
                    <?php endif; ?>
                    <span class="text-gray-800 font-medium hidden sm:inline">
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?>
                    </span>
                </div>
                <a href="deconnexion.php" 
                   class="bg-red-500 text-white px-4 py-2 rounded-button font-medium hover:bg-red-600 transition-colors whitespace-nowrap">
                    Déconnexion
                </a>
            <?php else: ?>
                <a href="inscrire.php" class="text-gray-800 hover:text-primary font-medium whitespace-nowrap">S'inscrire</a>
                <a href="connexion.php" class="bg-primary text-white px-5 py-2 rounded-button font-medium hover:bg-blue-600 transition-colors whitespace-nowrap">Se connecter</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-2xl p-8 md:p-10">
            <h1 class="text-3xl font-bold text-center mb-8 text-primary">Déposer un projet</h1>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form action="parcourir.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Nom du projet -->
                <div>
                    <label for="projectName" class="block text-lg font-medium mb-2">Nom du projet <span class="text-red-500">*</span></label>
                    <input type="text" name="projectName" id="projectName" required
                           value="<?= htmlspecialchars($_POST['projectName'] ?? '') ?>"
                           placeholder="Ex : MonApp"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Description des fonctionnalités -->
                <div>
                    <label for="features" class="block text-lg font-medium mb-2">Description des fonctionnalités <span class="text-red-500">*</span></label>
                    <textarea name="features" id="features" rows="5" required
                              placeholder="Décrivez les fonctionnalités principales…"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"><?= htmlspecialchars($_POST['features'] ?? '') ?></textarea>
                </div>

                <!-- Technologies utilisées (sous forme de liste) -->
                <div>
                    <label for="technologies" class="block text-lg font-medium mb-2">Technologies utilisées <span class="text-red-500">*</span></label>
                    <input type="text" name="technologies" id="technologies" required
                           value="<?= htmlspecialchars($_POST['technologies'] ?? '') ?>"
                           placeholder="React, Laravel, MySQL… (séparées par des virgules)"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-sm text-gray-500 mt-1">Exemple : React, Node.js, MongoDB</p>
                </div>

                <!-- Prix -->
                <div>
                    <label for="price" class="block text-lg font-medium mb-2">Prix (en MAD) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="price" min="0" step="0.01" required
                           value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                           placeholder="Ex : 49.99"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <!-- Image du projet (optionnelle) -->
                <div>
                    <label for="imageFile" class="block text-lg font-medium mb-2">Image du projet (optionnelle)</label>
                    <input type="file" name="imageFile" id="imageFile" accept="image/jpeg,image/png,image/gif"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-blue-600">
                    <p class="text-sm text-gray-500 mt-1">Format JPG, PNG ou GIF. Taille max : 2 Mo (non implémenté).</p>
                </div>

                <!-- Fichier ZIP du projet -->
                <div>
                    <label for="projectFile" class="block text-lg font-medium mb-2">Fichier ZIP du projet <span class="text-red-500">*</span></label>
                    <input type="file" name="projectFile" id="projectFile" accept=".zip,application/zip" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-blue-600">
                </div>

                <!-- Bouton de soumission -->
                <div class="text-center pt-4">
                    <button type="submit" 
                            class="bg-primary text-white px-10 py-4 rounded-xl font-semibold text-lg hover:bg-blue-700 transition shadow-md">
                        Déposer le projet
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 px-6">
        <div class="container mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div>
                    <h3 class="text-xl font-bold mb-4 font-['Pacifico']">Tekko-Fii</h3>
                    <p class="text-gray-400 mb-6">La marketplace qui connecte développeurs et entreprises.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="ri-twitter-x-line ri-lg"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="ri-linkedin-line ri-lg"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="ri-github-line ri-lg"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Liens rapides</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Projets populaires</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Nouvelles arrivées</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Développeurs vérifiés</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Ressources</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Documentation</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Guide du vendeur</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">FAQ</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Support</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Légal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Conditions d'utilisation</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Politique de confidentialité</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Licences</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Cookies</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 mb-4 md:mb-0">© 2025 Tekko-Fii. Tous droits réservés.</p>
                <div class="flex items-center space-x-4 text-gray-400">
                    <i class="ri-visa-line ri-lg"></i>
                    <i class="ri-mastercard-line ri-lg"></i>
                    <i class="ri-paypal-line ri-lg"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Petit script pour confirmer la taille max des fichiers (optionnel) -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('projectFile');
            if (fileInput.files.length > 0 && fileInput.files[0].size > 10 * 1024 * 1024) { // 10 Mo
                e.preventDefault();
                alert('Le fichier ZIP ne doit pas dépasser 10 Mo.');
            }
        });
    </script>
</body>
</html>