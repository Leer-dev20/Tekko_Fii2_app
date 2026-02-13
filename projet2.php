<?php
// en haut du fichier, avant tout code HTML
require_once 'config/database.php';

try {
    $pdo = getPDO();
    // Ici vous pouvez exécuter vos requêtes
    $stmt = $pdo->query('SELECT * FROM users LIMIT 5');
    $users = $stmt->fetchAll();
    // ...
} catch (Exception $e) {
    // Gestion des erreurs
    echo 'Une erreur est survenue.';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tekko-Fii - Projets</title>    
<link rel="shortcut icon" href="img/Blue_Purple_Modern_Gradient_Computer_Service_and_Repair_Logo__1_-removebg-preview.png" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:'#4285F4',secondary:'#34A853'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
<style>
body {
    font-family: 'Inter', sans-serif;
}
input:focus, select:focus {
    outline: none;
}
.range-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #4285F4;
    cursor: pointer;
}
.range-slider::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #4285F4;
    cursor: pointer;
    border: none;
}
.custom-checkbox {
    position: relative;
    padding-left: 28px;
    cursor: pointer;
    user-select: none;
}
.custom-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}
.checkmark {
    position: absolute;
    top: 0;
    left: 0;
    height: 20px;
    width: 20px;
    background-color: #fff;
    border: 1px solid #d1d5db;
    border-radius: 4px;
}
.custom-checkbox:hover input ~ .checkmark {
    background-color: #f3f4f6;
}
.custom-checkbox input:checked ~ .checkmark {
    background-color: #4285F4;
    border-color: #4285F4;
}
.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}
.custom-checkbox input:checked ~ .checkmark:after {
    display: block;
}
.custom-checkbox .checkmark:after {
    left: 7px;
    top: 3px;
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
.project-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}
</style>
</head>
<body class="bg-white min-h-screen">

<!-- Header -->
<header class="w-full py-4 px-6 flex justify-between items-center border-b border-gray-100">
    <div class="flex items-center">
        <a href="index.php" class="text-2xl font-bold font-['Pacifico'] text-gray-900">Tekko-Fii</a>
    </div>
    <div class="flex items-center space-x-8">
        <nav class="hidden md:flex space-x-8">
            <a href="projet.php" class="text-primary font-medium border-b-2 border-primary">Projets</a>
            <a href="developper.php" class="text-gray-800 hover:text-primary font-medium">Développeurs</a>
            <a href="entreprise.php" class="text-gray-800 hover:text-primary font-medium">Entreprise</a>
        </nav>
        <div class="flex items-center space-x-4">
            <a href="inscrire.php" class="text-gray-800 hover:text-primary font-medium whitespace-nowrap">S'inscrire</a>
            <a href="connexion.php" class="bg-primary text-white px-5 py-2 !rounded-button font-medium hover:bg-blue-600 transition-colors whitespace-nowrap">Se connecter</a>
        </div>
    </div>
</header>

<!-- Page Title -->
<div class="bg-gray-50 py-8">
    <div class="container mx-auto px-6">
        <h1 class="text-3xl font-bold text-gray-900">Explorez nos projets</h1>
        <p class="text-gray-600 mt-2">Découvrez des milliers de projets de développeurs talentueux</p>
    </div>
</div>

<!-- Search and Filter Section -->
<section class="py-8 px-6 border-b border-gray-200">
    <div class="container mx-auto">
        <!-- Search Bar -->
        <div class="flex mb-8">
            <div class="relative flex-1">
                <input type="text" placeholder="Rechercher un projet..." class="w-full px-4 py-3 pr-10 border border-gray-300 !rounded-button focus:border-primary text-gray-800">
                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                    <div class="w-6 h-6 flex items-center justify-center">
                        <i class="ri-search-line"></i>
                    </div>
                </div>
            </div>
            <button class="bg-primary text-white px-6 py-3 ml-4 !rounded-button font-medium hover:bg-blue-600 transition-colors whitespace-nowrap">Rechercher</button>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                <div class="relative">
                    <select class="w-full px-4 py-2.5 border border-gray-300 !rounded-button appearance-none bg-white text-gray-800 pr-8">
                        <option value="">Toutes les catégories</option>
                        <option value="ecommerce">E-commerce</option>
                        <option value="mobile">Applications Mobiles</option>
                        <option value="plugins">Plugins & Extensions</option>
                        <option value="dashboard">Tableaux de Bord</option>
                        <option value="portfolio">Sites Portfolio</option>
                        <option value="blog">Blogs & CMS</option>
                        <option value="game">Jeux</option>
                        <option value="api">API & Services</option>
                    </select>
                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none">
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-arrow-down-s-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technology Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Technologies</label>
                <div class="relative">
                    <button id="techFilterBtn" class="w-full px-4 py-2.5 border border-gray-300 !rounded-button bg-white text-gray-800 text-left flex justify-between items-center">
                        <span>Sélectionner</span>
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-arrow-down-s-line"></i>
                        </div>
                    </button>
                    <div id="techDropdown" class="hidden absolute left-0 right-0 mt-1 bg-white border border-gray-200 !rounded-button shadow-lg z-10 max-h-60 overflow-y-auto">
                        <div class="p-3">
                            <label class="custom-checkbox block mb-2">
                                <input type="checkbox" value="react">
                                <span class="checkmark"></span>
                                React
                            </label>
                            <label class="custom-checkbox block mb-2">
                                <input type="checkbox" value="vue">
                                <span class="checkmark"></span>
                                Vue.js
                            </label>
                            <label class="custom-checkbox block mb-2">
                                <input type="checkbox" value="angular">
                                <span class="checkmark"></span>
                                Angular
                            </label>
                            <label class="custom-checkbox block mb-2">
                                <input type="checkbox" value="node">
                                <span class="checkmark"></span>
                                Node.js
                            </label>
                            <label class="custom-checkbox block mb-2">
                                <input type="checkbox" value="django">
                                <span class="checkmark"></span>
                                Django
                            </label>
                            <label class="custom-checkbox block mb-2">
                                <input type="checkbox" value="laravel">
                                <span class="checkmark"></span>
                                Laravel
                            </label>
                            <label class="custom-checkbox block">
                                <input type="checkbox" value="wordpress">
                                <span class="checkmark"></span>
                                WordPress
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Range Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fourchette de prix</label>
                <div class="px-2">
                    <input type="range" min="0" max="10000" value="5000" class="range-slider w-full h-2 bg-gray-200 rounded-full appearance-none cursor-pointer" id="priceRange">
                    <div class="flex justify-between mt-2 text-sm text-gray-600">
                        <span>0 MAD</span>
                        <span id="priceValue">5.000 MAD</span>
                        <span>10.000 MAD</span>
                    </div>
                </div>
            </div>

            <!-- Sort By -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                <div class="relative">
                    <select class="w-full px-4 py-2.5 border border-gray-300 !rounded-button appearance-none bg-white text-gray-800 pr-8">
                        <option value="popular">Popularité</option>
                        <option value="newest">Date (récent)</option>
                        <option value="oldest">Date (ancien)</option>
                        <option value="price_low">Prix (croissant)</option>
                        <option value="price_high">Prix (décroissant)</option>
                        <option value="rating">Évaluation</option>
                    </select>
                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none">
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-arrow-down-s-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Projects Grid -->
<section class="py-10 px-6">
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600"><span class="font-medium">238</span> projets trouvés</p>
            <div class="flex space-x-2">
                <button class="p-2 bg-gray-100 !rounded-button text-gray-600 hover:bg-gray-200">
                    <div class="w-5 h-5 flex items-center justify-center">
                        <i class="ri-layout-grid-line"></i>
                    </div>
                </button>
                <button class="p-2 bg-white !rounded-button text-gray-400 hover:bg-gray-100 border border-gray-200">
                    <div class="w-5 h-5 flex items-center justify-center">
                        <i class="ri-list-check"></i>
                    </div>
                </button>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Project Card 1 -->
            <div class="project-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition relative">
                <img loading="lazy" class="w-full h-40 object-cover" src="img/agrilinkimg.png" alt="AgriLink">
                <div class="p-4">
                    <h2 class="text-lg font-bold">AgriLink</h2>
                    <p class="text-sm text-gray-500">Mor Sene</p>
                    <div class="mt-2 text-sm flex flex-wrap gap-2">
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">HTML</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">CSS</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">JS</span>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="font-bold text-indigo">2.125 MAD</span>
                        <button onclick="openModal(0)" class="text-primary hover:text-blue-700 font-medium text-sm">Voir plus</button>
                    </div>
                </div>
            </div>

            <!-- Project Card 2 -->
            <div class="project-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition relative">
                <img loading="lazy" class="w-full h-40 object-cover" src="img/projetcard2.jpg" alt="Outil de Gestion de Projet">
                <div class="p-4">
                    <h2 class="text-lg font-bold">Outil de Gestion de Projet</h2>
                    <p class="text-sm text-gray-500">Amina Diouf</p>
                    <div class="mt-2 text-sm flex flex-wrap gap-2">
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Vue</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Django</span>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="font-bold text-indigo">2.166 MAD</span>
                        <button onclick="openModal(1)" class="text-primary hover:text-blue-700 font-medium text-sm">Voir plus</button>
                    </div>
                </div>
            </div>

            <!-- Project Card 3 -->
            <div class="project-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition relative">
                <img loading="lazy" class="w-full h-40 object-cover" src="img/projetcard3.jpg" alt="Plateforme de Réseau Social">
                <div class="p-4">
                    <h2 class="text-lg font-bold">Plateforme de Réseau Social</h2>
                    <p class="text-sm text-gray-500">Ibrahima LO</p>
                    <div class="mt-2 text-sm flex flex-wrap gap-2">
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Angular</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Firebase</span>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="font-bold text-indigo">3.250 MAD</span>
                        <button onclick="openModal(2)" class="text-primary hover:text-blue-700 font-medium text-sm">Voir plus</button>
                    </div>
                </div>
            </div>

            <!-- Project Card 4 -->
            <div class="project-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition relative">
                <img loading="lazy" class="w-full h-40 object-cover" src="img/projetcard4.jpg" alt="Site Portfolio">
                <div class="p-4">
                    <h2 class="text-lg font-bold">Site Portfolio</h2>
                    <p class="text-sm text-gray-500">Khadija Cissé</p>
                    <div class="mt-2 text-sm flex flex-wrap gap-2">
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">HTML</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">CSS</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">JS</span>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="font-bold text-indigo">541 MAD</span>
                        <button onclick="openModal(3)" class="text-primary hover:text-blue-700 font-medium text-sm">Voir plus</button>
                    </div>
                </div>
            </div>

            <!-- Project Card 5 -->
            <div class="project-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition relative">
                <img loading="lazy" class="w-full h-40 object-cover" src="img/projetcard5.jpg" alt="Système de Blog CMS">
                <div class="p-4">
                    <h2 class="text-lg font-bold">Système de Blog CMS</h2>
                    <p class="text-sm text-gray-500">Dame Seck</p>
                    <div class="mt-2 text-sm flex flex-wrap gap-2">
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">PHP</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">MySQL</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">jQuery</span>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="font-bold text-indigo">1.300 MAD</span>
                        <button onclick="openModal(4)" class="text-primary hover:text-blue-700 font-medium text-sm">Voir plus</button>
                    </div>
                </div>
            </div>

            <!-- Project Card 6 -->
            <div class="project-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition relative">
                <img loading="lazy" class="w-full h-40 object-cover" src="img/projetcard6.jpg" alt="Kit UI Mobile">
                <div class="p-4">
                    <h2 class="text-lg font-bold">Kit UI Mobile</h2>
                    <p class="text-sm text-gray-500">Bruno A. Diallo</p>
                    <div class="mt-2 text-sm flex flex-wrap gap-2">
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Sketch</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Figma</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">XD</span>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="font-bold text-indigo">920 MAD</span>
                        <button onclick="openModal(5)" class="text-primary hover:text-blue-700 font-medium text-sm">Voir plus</button>
                    </div>
                </div>
            </div>

            <!-- Project Card 7 -->
            <div class="project-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition relative">
                <img loading="lazy" class="w-full h-40 object-cover" src="img/projetcard7.jpg" alt="Tableau de Bord Analytics">
                <div class="p-4">
                    <h2 class="text-lg font-bold">Tableau de Bord Analytics</h2>
                    <p class="text-sm text-gray-500">Ibrahima Diouf</p>
                    <div class="mt-2 text-sm flex flex-wrap gap-2">
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">React</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">D3.js</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">API</span>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="font-bold text-indigo">2.383 MAD</span>
                        <button onclick="openModal(6)" class="text-primary hover:text-blue-700 font-medium text-sm">Voir plus</button>
                    </div>
                </div>
            </div>

            <!-- Project Card 8 -->
            <div class="project-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition relative">
                <img loading="lazy" class="w-full h-40 object-cover" src="img/projetcard8.jpg" alt="Plateforme d'Apprentissage">
                <div class="p-4">
                    <h2 class="text-lg font-bold">Plateforme d'Apprentissage</h2>
                    <p class="text-sm text-gray-500">Aicha Rassoul</p>
                    <div class="mt-2 text-sm flex flex-wrap gap-2">
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Laravel</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Vue</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">AWS</span>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="font-bold text-indigo">2.500 MAD</span>
                        <button onclick="openModal(7)" class="text-primary hover:text-blue-700 font-medium text-sm">Voir plus</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-12 flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <span class="text-sm text-gray-600">Affichage de 1-8 sur 238 projets</span>
            </div>
            <div class="flex items-center">
                <div class="mr-4">
                    <select class="px-3 py-2 border border-gray-300 !rounded-button text-sm text-gray-800 pr-8 appearance-none">
                        <option value="12">12 par page</option>
                        <option value="24">24 par page</option>
                        <option value="48">48 par page</option>
                    </select>
                </div>
                <div class="flex">
                    <a href="#" class="w-10 h-10 flex items-center justify-center border border-gray-300 !rounded-button mr-2 text-gray-500 hover:bg-gray-50">
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-arrow-left-s-line"></i>
                        </div>
                    </a>
                    <a href="projet.php" class="w-10 h-10 flex items-center justify-center border border-gray-300 !rounded-button mr-2 text-gray-700 hover:bg-gray-50">1</a>
                    <a href="projet2.php" class="w-10 h-10 flex items-center justify-center border border-gray-300 !rounded-button mr-2 bg-primary text-white hover:bg-blue-600">2</a>
                    <a href="#" class="w-10 h-10 flex items-center justify-center border border-gray-300 !rounded-button mr-2 text-gray-700 hover:bg-gray-50">3</a>
                    <span class="w-10 h-10 flex items-center justify-center text-gray-500">...</span>
                    <a href="#" class="w-10 h-10 flex items-center justify-center border border-gray-300 !rounded-button mr-2 text-gray-700 hover:bg-gray-50">30</a>
                    <a href="#" class="w-10 h-10 flex items-center justify-center border border-gray-300 !rounded-button text-gray-500 hover:bg-gray-50">
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-arrow-right-s-line"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Modal -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg w-11/12 md:w-2/3 lg:w-1/2 xl:w-1/3 p-6 relative max-h-[90vh] overflow-y-auto">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-600 hover:text-gray-900 text-xl">&times;</button>
        <img id="modalImage" src="" alt="Projet" class="w-full h-48 object-cover rounded-md mb-4">
        <h2 id="modalTitle" class="text-xl font-bold mb-2"></h2>
        <p id="modalAuthor" class="text-sm text-gray-600 mb-1"></p>
        <p id="modalCategory" class="text-sm font-medium text-primary mb-2"></p>
        <p id="modalPrice" class="text-lg font-bold text-indigo-600 mb-4"></p>
        <p id="modalDescription" class="text-gray-700 mb-4"></p>
        <div class="flex flex-wrap gap-2 mb-4" id="modalTechnologies"></div>
        <div class="flex justify-between items-center">
            <a id="modalGithub" href="#" target="_blank" class="inline-block text-blue-600 hover:underline flex items-center">
                <i class="ri-github-fill mr-2"> </i> Dépôt GitHub
            </a>
            <button class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">Acheter ce projet</button>
        </div>
    </div>
</div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Technology dropdown toggle
    const techFilterBtn = document.getElementById('techFilterBtn');
    const techDropdown = document.getElementById('techDropdown');
    
    techFilterBtn.addEventListener('click', function() {
        techDropdown.classList.toggle('hidden');
    });
    
    // Fermez la liste déroulante 
    document.addEventListener('click', function(event) {
        if (!techFilterBtn.contains(event.target) && !techDropdown.contains(event.target)) {
            techDropdown.classList.add('hidden');
        }
    });
    
    // Price range slider
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    
    priceRange.addEventListener('input', function() {
        // Format price with thousands separator
        const formattedValue = new Intl.NumberFormat('fr-FR').format(this.value);
        priceValue.textContent = formattedValue + ' MAD';
    });
    
    // Technology checkboxes
    const techCheckboxes = document.querySelectorAll('#techDropdown input[type="checkbox"]');
    techCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateTechFilterLabel();
        });
    });
    
    function updateTechFilterLabel() {
        const selectedTechs = Array.from(techCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
            
        if (selectedTechs.length === 0) {
            techFilterBtn.querySelector('span').textContent = 'Sélectionner';
        } else if (selectedTechs.length === 1) {
            techFilterBtn.querySelector('span').textContent = selectedTechs[0];
        } else {
            techFilterBtn.querySelector('span').textContent = `${selectedTechs.length} sélectionnés`;
        }
    }
});

// Données  des projets popup
const projects = [
    {
        title: "E-commerce App",
        author: "Mactar Seck",
        category: "Application E-commerce",
        price: "1.625 MAD",
        image: "img/projetcard1.jpg",
        technologies: ["React", "Node.js"],
        description: `Une application E-commerce complète avec système de paiement intégré, gestion de panier et tableau de bord administrateur. Développée avec React pour le frontend et Node.js pour le backend.`,
        github: "#"
    },
    {
        title: "Outil de Gestion de Projet",
        author: "Amina Diouf",
        category: "Outil de Productivité",
        price: "2.166 MAD",
        image: "img/projetcard2.jpg",
        technologies: ["Vue.js", "Django"],
        description: `Un outil complet de gestion de projet avec tableaux Kanban, gestion des tâches et suivi du temps. Interface moderne développée avec Vue.js et API robuste avec Django.`,
        github: "#"
    },
    {
        title: "Plateforme de Réseau Social",
        author: "Ibrahima LO",
        category: "Réseau Social",
        price: "3.250 MAD",
        image: "img/projetcard3.jpg",
        technologies: ["Angular", "Firebase"],
        description: `Plateforme de réseau social avec fonctionnalités de publication, commentaires, likes et messagerie instantanée. Développée avec Angular et Firebase pour le backend.`,
        github: "#"
    },
    {
        title: "Site Portfolio",
        author: "Khadija Cissé",
        category: "Site Web Personnel",
        price: "541 MAD",
        image: "img/projetcard4.jpg",
        technologies: ["HTML", "CSS", "JavaScript"],
        description: `Site portfolio moderne et responsive avec animations fluides. Parfait pour présenter vos travaux et compétences. Code propre et bien documenté.`,
        github: "#"
    },
    {
        title: "Système de Blog CMS",
        author: "Dame Seck",
        category: "Système de Gestion de Contenu",
        price: "1.300 MAD",
        image: "img/projetcard5.jpg",
        technologies: ["PHP", "MySQL", "jQuery"],
        description: `CMS de blog complet avec éditeur WYSIWYG, gestion des utilisateurs et système de commentaires. Développé avec PHP natif et MySQL.`,
        github: "#"
    },
    {
        title: "Kit UI Mobile",
        author: "Bruno A. Diallo",
        category: "Design UI/UX",
        price: "920 MAD",
        image: "img/projetcard6.jpg",
        technologies: ["Sketch", "Figma", "Adobe XD"],
        description: `Kit d'interface utilisateur moderne pour applications mobiles. Comprend plus de 50 composants personnalisables et un système de design cohérent.`,
        github: "#"
    },
    {
        title: "Tableau de Bord Analytics",
        author: "Ibrahima Diouf",
        category: "Visualisation de Données",
        price: "2.383 MAD",
        image: "img/projetcard7.jpg",
        technologies: ["React", "D3.js", "API REST"],
        description: `Tableau de bord analytique avec visualisations interactives et temps réel. Intègre plusieurs sources de données et offre des options d'export.`,
        github: "#"
    },

       {
        title: "Plateforme d'Apprentissage",
        author: "Aicha Rassoul",
        category: "Plateforme d'Apprentissage",
        price: "2.500 MAD",
        image: "img/projetcard8.jpg",
        description: `Une application E-commerce permet aux utilisateurs d'acheter des produits en ligne...`,
        github: "#"
      },
      // Ajoutez ici les 7 autres projets avec leurs infos
    ];

    function openModal(index) {
      const project = projects[index];
      document.getElementById("modalTitle").innerText = project.title;
      document.getElementById("modalAuthor").innerText = `Développeur: ${project.author}`;
      document.getElementById("modalCategory").innerText = `Catégorie: ${project.category}`;
      document.getElementById("modalPrice").innerText = `Prix: ${project.price}`;
      document.getElementById("modalDescription").innerText = `Description: ${project.description}`;
      document.getElementById("modalImage").src = project.image;
      document.getElementById("modalGithub").href = project.github;
      document.getElementById("modal").classList.remove("hidden");
    }

    function closeModal() {
      document.getElementById("modal").classList.add("hidden");
    }
</script>
</body>
</html>