<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';
$success = '';
$fullName = $email = '';
$userType = 'developer'; // valeur par défaut

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $userType = $_POST['accountType'] ?? 'developer';
    $terms = isset($_POST['terms']);

    // Validations
    if (empty($fullName) || empty($email) || empty($password)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (!$terms) {
        $error = 'Vous devez accepter les conditions d\'utilisation.';
    } else {
        $pdo = getPDO();
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            // Hash du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insertion dans la table users
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password, full_name, user_type, avatar)
                VALUES (?, ?, ?, ?, NULL)
            ");
            if ($stmt->execute([$email, $hashedPassword, $fullName, $userType])) {
                $userId = $pdo->lastInsertId();

                // Si c'est un développeur, créer une entrée dans la table developers
                if ($userType === 'developer') {
                    $stmtDev = $pdo->prepare("
                        INSERT INTO developers (user_id, hourly_rate, availability_status)
                        VALUES (?, 0, 'available')
                    ");
                    $stmtDev->execute([$userId]);
                }

                // Connecter automatiquement l'utilisateur
                loginUser($userId, $email, $fullName, $userType);

                $success = 'Inscription réussie ! Vous allez être redirigé.';
                header('Refresh: 2; URL=index.php');
            } else {
                $error = 'Erreur lors de l\'inscription. Veuillez réessayer.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tekko-Fii - Inscription</title>
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
        body {
            font-family: 'Inter', sans-serif;
        }
        input:focus {
            outline: none;
        }
    </style>
</head>
<body class="bg-white min-h-screen flex flex-col">

    <!-- Header -->
    <header class="w-full py-4 px-6 flex justify-between items-center border-b border-gray-100">
        <a href="index.php" class="text-2xl font-bold font-['Pacifico'] text-gray-900">Tekko-Fii</a>
        <nav class="hidden md:flex space-x-8">
            <a href="projet.php" class="text-gray-800 hover:text-primary font-medium">Projets</a>
            <a href="liste_dev.php" class="text-gray-800 hover:text-primary font-medium">Développeurs</a>
            <a href="entreprise.php" class="text-gray-800 hover:text-primary font-medium">Entreprise</a>
        </nav>
        <div class="flex items-center space-x-4">
            <?php if (isLoggedIn()): ?>
                <span class="text-gray-800">Bonjour, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="deconnexion.php" class="bg-red-500 text-white px-5 py-2 rounded-button font-medium hover:bg-red-600 transition">Déconnexion</a>
            <?php else: ?>
                <a href="inscrire.php" class="text-gray-800 hover:text-primary font-medium">S'inscrire</a>
                <a href="connexion.php" class="bg-primary text-white px-5 py-2 rounded-button font-medium hover:bg-blue-600 transition">Se connecter</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main -->
    <main class="flex-grow flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md bg-gray-50 p-8 rounded-xl shadow-lg">
            <h1 class="text-2xl font-semibold text-center text-gray-800 mb-6">Créer un compte</h1>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="inscrire.php">
                <div class="mb-4">
                    <label for="fullName" class="block mb-1 font-medium">Nom complet</label>
                    <input type="text" id="fullName" name="fullName" required
                           value="<?= htmlspecialchars($fullName) ?>"
                           placeholder="Votre nom complet"
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-primary">
                </div>

                <div class="mb-4">
                    <label for="email" class="block mb-1 font-medium">Adresse email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($email) ?>"
                           placeholder="leer@domaine.com"
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-primary">
                </div>

                <div class="mb-4">
                    <label for="password" class="block mb-1 font-medium">Mot de passe</label>
                    <input type="password" id="password" name="password" required
                           placeholder="********"
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-primary"
                           oninput="checkPasswordStrength()">
                    <div id="passwordStrength" class="text-sm mt-1"></div>
                </div>

                <div class="mb-4">
                    <label for="confirmPassword" class="block mb-1 font-medium">Confirmer le mot de passe</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required
                           placeholder="********"
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-primary">
                    <div id="confirmPasswordError" class="text-sm text-red-500"></div>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Type de compte</label>
                    <label class="inline-flex items-center mr-4">
                        <input type="radio" name="accountType" value="developer"
                               <?= $userType == 'developer' ? 'checked' : '' ?> class="mr-2"> Développeur
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="accountType" value="client"
                               <?= $userType == 'client' ? 'checked' : '' ?> class="mr-2"> Client
                    </label>
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="terms" id="terms" class="mr-2">
                        J'accepte les <a href="terms.php" class="text-primary underline"> conditions d'utilisation</a>
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-primary text-white py-3 rounded-button font-medium hover:bg-blue-600 transition">
                    S'inscrire
                </button>
            </form>

            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Déjà inscrit ?
                    <a href="connexion.php" class="text-primary underline">Connectez-vous</a>
                </p>
            </div>
        </div>
    </main>

    <!-- Footer (identique à votre footer) -->
    <footer class="bg-gray-900 text-white py-12 px-6 mt-12">
        <div class="container mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div>
                    <h3 class="text-xl font-bold mb-4 font-['Pacifico']">Tekko-Fii</h3>
                    <p class="text-gray-400 mb-6">La marketplace qui connecte développeurs et entreprises.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Liens rapides</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Projets populaires</a></li>
                        <li><a href="#" class="hover:text-white">Nouvelles arrivées</a></li>
                        <li><a href="#" class="hover:text-white">Développeurs vérifiés</a></li>
                        <li><a href="#" class="hover:text-white">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Ressources</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Documentation</a></li>
                        <li><a href="#" class="hover:text-white">Guide du vendeur</a></li>
                        <li><a href="#" class="hover:text-white">FAQ</a></li>
                        <li><a href="#" class="hover:text-white">Support</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Légal</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Conditions d'utilisation</a></li>
                        <li><a href="#" class="hover:text-white">Politique de confidentialité</a></li>
                        <li><a href="#" class="hover:text-white">Licences</a></li>
                        <li><a href="#" class="hover:text-white">Cookies</a></li>
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

    <script>
        // Fonction de vérification de la force du mot de passe
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthDiv = document.getElementById('passwordStrength');
            let strength = 'Faible';
            if (password.length >= 8) {
                strength = 'Moyenne';
                if (/[A-Z]/.test(password) && /\d/.test(password)) {
                    strength = 'Forte';
                }
            }
            strengthDiv.textContent = 'Force du mot de passe : ' + strength;
        }

        // Vérification de la correspondance des mots de passe
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirm = document.getElementById('confirmPassword');
            const errorDiv = document.getElementById('confirmPasswordError');

            confirm.addEventListener('input', function() {
                if (password.value !== confirm.value) {
                    errorDiv.textContent = 'Les mots de passe ne correspondent pas.';
                } else {
                    errorDiv.textContent = '';
                }
            });
        });
    </script>

</body>
</html>