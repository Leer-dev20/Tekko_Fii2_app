<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tekko-Fii - Marketplace de projets de développeurs</title>
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
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    }
                }
            }
        }
    </script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body {
            font-family: 'Inter', sans-serif;
        }
        .hero-bg {
            background-image: url('img/7401d6ed8df947c91eb40ae9801a54eb.jpg');
            background-size: cover;
            background-position: right center;
        }
        .hero-overlay {
            background: linear-gradient(90deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 50%, rgba(255,255,255,0.5) 100%);
        }
        input:focus {
            outline: none;
        }
    </style>
</head>
<body class="bg-white min-h-screen">

    <!-- Header dynamique -->
    <header class="w-full py-4 px-6 flex justify-between items-center border-b border-gray-100">
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

    <!-- Hero Section -->
    <section class="hero-bg w-full relative">
        <div class="hero-overlay w-full">
            <div class="container mx-auto px-6 py-20 md:py-28">
                <div class="max-w-3xl">
                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">Dépose le ici</h1>
                    <p class="text-xl text-gray-700 mb-8 leading-relaxed">
                        Marketplace de projets de développeurs. Achetez et vendez des applications, des sites web, des morceaux de code, et plus encore.
                    </p>
                    <a href="parcourir.php" class="inline-block bg-primary text-white px-8 py-4 rounded-button font-medium text-lg hover:bg-blue-600 transition-colors whitespace-nowrap">Parcourir</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Projects -->
    <section class="py-16 px-6">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-gray-900 mb-10">Projets en vedette</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Project Card 1 -->
                <div class="bg-white rounded shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gray-100 overflow-hidden">
                        <img src="img/projetcard1.jpg" alt="E-commerce App" class="w-full h-full object-cover object-top">
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg mb-2">E-commerce App</h3>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full">React</span>
                            <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full">Node.js</span>
                        </div>
                        <div class="flex justify-end">
                            <span class="font-bold text-xl">1.625 MAD</span>
                        </div>
                    </div>
                </div>

                <!-- Project Card 2 -->
                <div class="bg-white rounded shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gray-100 overflow-hidden">
                        <img src="img/projetcard2.jpg" alt="Outil de Gestion de Projet" class="w-full h-full object-cover object-top">
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg mb-2">Outil de Gestion de Projet</h3>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full">Vue</span>
                            <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full">Django</span>
                        </div>
                        <div class="flex justify-end">
                            <span class="font-bold text-xl">2.166 MAD</span>
                        </div>
                    </div>
                </div>

                <!-- Project Card 3 -->
                <div class="bg-white rounded shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gray-100 overflow-hidden">
                        <img src="img/projetcard3.jpg" alt="Plateforme de Réseau Social" class="w-full h-full object-cover object-top">
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg mb-2">Plateforme de Réseau Social</h3>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full">Angular</span>
                            <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full">Firebase</span>
                        </div>
                        <div class="flex justify-end">
                            <span class="font-bold text-xl">3.250 MAD</span>
                        </div>
                    </div>
                </div>

                <!-- Project Card 4 -->
                <div class="bg-white rounded shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gray-100 overflow-hidden">
                        <img src="img/projetcard4.jpg" alt="Site Portfolio" class="w-full h-full object-cover object-top">
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg mb-2">Site Portfolio</h3>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full">HTML</span>
                            <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full">CSS</span>
                            <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full">JS</span>
                        </div>
                        <div class="flex justify-end">
                            <span class="font-bold text-xl">540 MAD</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-16 px-6 bg-gray-50">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-gray-900 mb-10">Catégories populaires</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <a href="#" class="bg-white p-6 rounded shadow-sm hover:shadow-md transition-shadow flex flex-col items-center text-center">
                    <div class="w-16 h-16 flex items-center justify-center mb-4 text-primary">
                        <i class="ri-store-2-line ri-2x"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">E-commerce</h3>
                    <p class="text-gray-600">Solutions complètes pour boutiques en ligne</p>
                </a>
                
                <a href="#" class="bg-white p-6 rounded shadow-sm hover:shadow-md transition-shadow flex flex-col items-center text-center">
                    <div class="w-16 h-16 flex items-center justify-center mb-4 text-primary">
                        <i class="ri-smartphone-line ri-2x"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Applications Mobiles</h3>
                    <p class="text-gray-600">Apps iOS et Android prêtes à l'emploi</p>
                </a>
                
                <a href="#" class="bg-white p-6 rounded shadow-sm hover:shadow-md transition-shadow flex flex-col items-center text-center">
                    <div class="w-16 h-16 flex items-center justify-center mb-4 text-primary">
                        <i class="ri-code-box-line ri-2x"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Plugins & Extensions</h3>
                    <p class="text-gray-600">Modules complémentaires pour CMS et frameworks</p>
                </a>
                
                <a href="#" class="bg-white p-6 rounded shadow-sm hover:shadow-md transition-shadow flex flex-col items-center text-center">
                    <div class="w-16 h-16 flex items-center justify-center mb-4 text-primary">
                        <i class="ri-dashboard-line ri-2x"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Tableaux de Bord</h3>
                    <p class="text-gray-600">Interfaces d'administration et analytics</p>
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-16 px-6">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-gray-900 mb-10 text-center">Comment ça marche</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="flex flex-col items-center text-center">
                    <div class="w-20 h-20 flex items-center justify-center bg-blue-100 rounded-full mb-6 text-primary">
                        <i class="ri-user-search-line ri-2x"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-3">Parcourez</h3>
                    <p class="text-gray-600">Explorez notre catalogue de projets numériques classés par catégorie et technologie.</p>
                </div>
                
                <div class="flex flex-col items-center text-center">
                    <div class="w-20 h-20 flex items-center justify-center bg-blue-100 rounded-full mb-6 text-primary">
                        <i class="ri-shopping-cart-line ri-2x"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-3">Achetez</h3>
                    <p class="text-gray-600">Achetez des projets complets avec documentation et support du développeur.</p>
                </div>
                
                <div class="flex flex-col items-center text-center">
                    <div class="w-20 h-20 flex items-center justify-center bg-blue-100 rounded-full mb-6 text-primary">
                        <i class="ri-upload-cloud-line ri-2x"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-3">Vendez</h3>
                    <p class="text-gray-600">Publiez vos propres projets et générez des revenus passifs de votre travail.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-16 px-6 bg-gray-50">
        <div class="container mx-auto max-w-3xl text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Restez informé</h2>
            <p class="text-gray-600 mb-8">Recevez nos dernières mises à jour et les nouveaux projets directement dans votre boîte mail.</p>
            
            <form class="flex flex-col md:flex-row gap-4 max-w-lg mx-auto">
                <input type="email" placeholder="Votre adresse email" class="flex-1 px-4 py-3 border border-gray-300 rounded focus:border-primary text-gray-800">
                <button type="submit" class="bg-primary text-white px-6 py-3 rounded-button font-medium hover:bg-blue-600 transition-colors whitespace-nowrap">S'abonner</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 px-6">
        <div class="container mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div>
                    <h3 class="text-xl font-bold mb-4 font-['Pacifico']">Tekko-Fii</h3>
                    <p class="text-gray-400 mb-6">La marketplace qui connecte développeurs et entreprises.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <div class="w-10 h-10 flex items-center justify-center">
                                <i class="ri-twitter-x-line ri-lg"></i>
                            </div>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <div class="w-10 h-10 flex items-center justify-center">
                                <i class="ri-linkedin-line ri-lg"></i>
                            </div>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <div class="w-10 h-10 flex items-center justify-center">
                                <i class="ri-github-line ri-lg"></i>
                            </div>
                        </a>
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
                <div class="flex items-center space-x-4">
                    <div class="text-gray-400 flex items-center">
                        <i class="ri-visa-line ri-lg mr-2"></i>
                        <i class="ri-mastercard-line ri-lg mr-2"></i>
                        <i class="ri-paypal-line ri-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>