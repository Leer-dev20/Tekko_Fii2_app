<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']); // pour "Se souvenir de moi" (optionnel)

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id, email, password, full_name, user_type FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            loginUser($user['id'], $user['email'], $user['full_name'], $user['user_type']);

            // Gestion du "Se souvenir de moi" (créer un cookie de session longue durée)
            if ($remember) {
                // Optionnel : générer un token et le stocker en base pour "remember me"
                // Par simplicité, on peut prolonger la durée de vie de la session
                ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30); // 30 jours
            }

            // Redirection vers la page d'accueil ou la page d'origine
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tekko-Fii - Connexion</title>
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

    <!-- Formulaire de connexion -->
    <main class="flex-grow flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md bg-gray-50 p-8 rounded-xl shadow-lg">
            <h1 class="text-2xl font-semibold text-center text-gray-800 mb-6">Connexion</h1>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="connexion.php">
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
                           class="w-full px-4 py-2 border border-gray-300 rounded focus:border-primary">
                </div>

                <div class="flex justify-between items-center mb-4">
                    <label class="flex items-center text-sm text-gray-700">
                        <input type="checkbox" name="remember" class="mr-2 rounded border-gray-300 text-primary focus:ring-primary">
                        Se souvenir de moi
                    </label>
                    <a href="mot-de-passe-oublie.php" class="text-sm text-primary hover:underline">Mot de passe oublié ?</a>
                </div>

                <button type="submit"
                        class="w-full bg-primary text-white py-3 rounded-button font-medium hover:bg-blue-600 transition">
                    Se connecter
                </button>
            </form>

            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Pas encore de compte ?
                    <a href="inscrire.php" class="text-primary underline">Créez-en un</a>
                </p>
            </div>
        </div>
    </main>

    <!-- Footer -->
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

</body>
</html>