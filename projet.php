<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$pdo = getPDO();

// ------------------------------------------------------------
// 1. Récupération des paramètres GET
// ------------------------------------------------------------
$search       = isset($_GET['search']) ? trim($_GET['search']) : '';
$category     = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$technologies = isset($_GET['technologies']) ? (array)$_GET['technologies'] : [];
$price_max    = isset($_GET['price_max']) ? (int)$_GET['price_max'] : 0;
$sort         = isset($_GET['sort']) ? $_GET['sort'] : 'popular';
$page         = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage      = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 8; // 8 par défaut comme dans l'HTML
$allowedPerPage = [8, 12, 24, 48];
if (!in_array($perPage, $allowedPerPage)) $perPage = 8;
$offset       = ($page - 1) * $perPage;

// ------------------------------------------------------------
// 2. Récupération des catégories pour le filtre
// ------------------------------------------------------------
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// ------------------------------------------------------------
// 3. Récupération de toutes les technologies pour le dropdown
// ------------------------------------------------------------
$allTechnologies = $pdo->query("SELECT id, name FROM skills ORDER BY name")->fetchAll();

// ------------------------------------------------------------
// 4. Prix maximum pour le slider (basé sur les projets)
// ------------------------------------------------------------
$maxPrice = $pdo->query("SELECT MAX(price) FROM projects WHERE status = 'published'")->fetchColumn();
if (!$maxPrice) $maxPrice = 10000;
if ($price_max == 0) $price_max = $maxPrice;
$price_max = min($price_max, $maxPrice);

// ------------------------------------------------------------
// 5. Construction de la requête de comptage (avec filtres)
// ------------------------------------------------------------
$countSql = "SELECT COUNT(DISTINCT p.id) 
             FROM projects p
             JOIN developers d ON p.developer_id = d.id
             JOIN users u ON d.user_id = u.id";
$countParams = [];

// Catégorie
if ($category > 0) {
    $countSql .= " WHERE p.category_id = ?";
    $countParams[] = $category;
} else {
    $countSql .= " WHERE 1=1";
}

// Statut publié uniquement
$countSql .= " AND p.status = 'published'";

// Recherche
if (!empty($search)) {
    $countSql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $like = '%' . $search . '%';
    $countParams[] = $like;
    $countParams[] = $like;
}

// Technologies (jointure)
if (!empty($technologies)) {
    $placeholders = implode(',', array_fill(0, count($technologies), '?'));
    $countSql .= " AND p.id IN (
                    SELECT pt.project_id 
                    FROM project_technologies pt 
                    WHERE pt.skill_id IN ($placeholders)
                  )";
    $countParams = array_merge($countParams, $technologies);
}

// Prix max
if ($price_max < $maxPrice) {
    $countSql .= " AND p.price <= ?";
    $countParams[] = $price_max;
}

$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($countParams);
$totalProjects = $stmtCount->fetchColumn();
$totalPages = ceil($totalProjects / $perPage);
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

// ------------------------------------------------------------
// 6. Requête pour récupérer les projets de la page courante
// ------------------------------------------------------------
$sql = "SELECT 
            p.id,
            p.title,
            p.description,
            p.price,
            p.image_path,
            p.github_url,
            p.created_at,
            u.full_name as author_name,
            u.avatar as author_avatar,
            c.name as category_name,
            d.id as developer_id
        FROM projects p
        JOIN developers d ON p.developer_id = d.id
        JOIN users u ON d.user_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id";
$dataParams = [];

// Catégorie
if ($category > 0) {
    $sql .= " WHERE p.category_id = ?";
    $dataParams[] = $category;
} else {
    $sql .= " WHERE 1=1";
}

// Statut publié
$sql .= " AND p.status = 'published'";

// Recherche
if (!empty($search)) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $like = '%' . $search . '%';
    $dataParams[] = $like;
    $dataParams[] = $like;
}

// Technologies (sous-requête)
if (!empty($technologies)) {
    $placeholders = implode(',', array_fill(0, count($technologies), '?'));
    $sql .= " AND p.id IN (
                SELECT pt.project_id 
                FROM project_technologies pt 
                WHERE pt.skill_id IN ($placeholders)
              )";
    $dataParams = array_merge($dataParams, $technologies);
}

// Prix max
if ($price_max < $maxPrice) {
    $sql .= " AND p.price <= ?";
    $dataParams[] = $price_max;
}

$sql .= " GROUP BY p.id";

// Tri
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY p.created_at DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY p.created_at ASC";
        break;
    case 'popular':
    default:
        // On peut ajouter un compteur de ventes ou de vues plus tard
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

// Pagination
$sql .= " LIMIT ? OFFSET ?";
$dataParams[] = $perPage;
$dataParams[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($dataParams);
$projects = $stmt->fetchAll();

// ------------------------------------------------------------
// 7. Récupération des technologies pour chaque projet
// ------------------------------------------------------------
$technologies_by_project = [];
if (!empty($projects)) {
    $projectIds = array_column($projects, 'id');
    $placeholders = implode(',', array_fill(0, count($projectIds), '?'));

    $stmtTech = $pdo->prepare("
        SELECT pt.project_id, s.name
        FROM project_technologies pt
        JOIN skills s ON pt.skill_id = s.id
        WHERE pt.project_id IN ($placeholders)
    ");
    $stmtTech->execute($projectIds);
    foreach ($stmtTech as $row) {
        $technologies_by_project[$row['project_id']][] = $row['name'];
    }
}

// ------------------------------------------------------------
// 8. Fonction utilitaire pour construire les URL de filtres
// ------------------------------------------------------------
function buildUrl($params = []) {
    $query = $_GET;
    foreach ($params as $key => $value) {
        if ($value === null) {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }
    return 'projet.php?' . http_build_query($query);
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

<!-- Header dynamique -->
<header class="w-full py-4 px-6 flex justify-between items-center border-b border-gray-100">
    <a href="index.php" class="text-2xl font-bold font-['Pacifico'] text-gray-900">Tekko-Fii</a>
    <nav class="hidden md:flex space-x-8">
        <a href="projet.php" class="text-primary font-medium border-b-2 border-primary">Projets</a>
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
    <a href="panier.php" class="relative ml-4">
    <i class="ri-shopping-cart-line text-2xl text-gray-600"></i>
    <?php 
    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    if ($cartCount > 0): ?>
        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
            <?= $cartCount ?>
        </span>
    <?php endif; ?>
</a>
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
        <form method="get" action="projet.php" id="filterForm">
            <!-- Search Bar -->
            <div class="flex mb-8">
                <div class="relative flex-1">
                    <input type="text" name="search" id="searchInput" 
                           placeholder="Rechercher un projet..." 
                           value="<?= htmlspecialchars($search) ?>"
                           class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-button focus:border-primary text-gray-800">
                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <div class="w-6 h-6 flex items-center justify-center">
                            <i class="ri-search-line"></i>
                        </div>
                    </div>
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-3 ml-4 rounded-button font-medium hover:bg-blue-600 transition-colors whitespace-nowrap">Rechercher</button>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                    <div class="relative">
                        <select name="category" id="categorySelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-button appearance-none bg-white text-gray-800 pr-8">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
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
                        <button type="button" id="techFilterBtn" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-button bg-white text-gray-800 text-left flex justify-between items-center">
                            <span id="techLabel">
                                <?php
                                $selectedTechCount = count($technologies);
                                if ($selectedTechCount === 0) echo 'Sélectionner';
                                elseif ($selectedTechCount === 1) {
                                    // Trouver le nom de la technologie sélectionnée
                                    $techId = $technologies[0];
                                    $techName = '';
                                    foreach ($allTechnologies as $t) {
                                        if ($t['id'] == $techId) $techName = $t['name'];
                                    }
                                    echo htmlspecialchars($techName);
                                } else {
                                    echo $selectedTechCount . ' sélectionnées';
                                }
                                ?>
                            </span>
                            <div class="w-4 h-4 flex items-center justify-center">
                                <i class="ri-arrow-down-s-line"></i>
                            </div>
                        </button>
                        <div id="techDropdown" class="hidden absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-button shadow-lg z-10 max-h-60 overflow-y-auto">
                            <div class="p-3">
                                <?php foreach ($allTechnologies as $tech): ?>
                                <label class="custom-checkbox block mb-2">
                                    <input type="checkbox" name="technologies[]" value="<?= $tech['id'] ?>" 
                                           <?= in_array($tech['id'], $technologies) ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    <?= htmlspecialchars($tech['name']) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price Range Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fourchette de prix</label>
                    <div class="px-2">
                        <input type="range" name="price_max" id="priceRange" 
                               min="0" max="<?= $maxPrice ?>" value="<?= $price_max ?>" 
                               class="range-slider w-full h-2 bg-gray-200 rounded-full appearance-none cursor-pointer">
                        <div class="flex justify-between mt-2 text-sm text-gray-600">
                            <span>0 MAD</span>
                            <span id="priceValue"><?= number_format($price_max, 0) ?> MAD</span>
                            <span><?= number_format($maxPrice, 0) ?> MAD</span>
                        </div>
                    </div>
                </div>

                <!-- Sort By -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                    <div class="relative">
                        <select name="sort" id="sortSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-button appearance-none bg-white text-gray-800 pr-8">
                            <option value="popular" <?= $sort == 'popular' ? 'selected' : '' ?>>Popularité</option>
                            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Date (récent)</option>
                            <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Date (ancien)</option>
                            <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Prix (croissant)</option>
                            <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Prix (décroissant)</option>
                        </select>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none">
                            <div class="w-4 h-4 flex items-center justify-center">
                                <i class="ri-arrow-down-s-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Hidden inputs pour pagination et per_page -->
            <input type="hidden" name="per_page" id="perPageInput" value="<?= $perPage ?>">
        </form>
    </div>
</section>

<!-- Projects Grid -->
<section class="py-10 px-6">
    <div class="container mx-auto">
        <!-- En-tête des résultats -->
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600">
                <?php if ($totalProjects > 0): ?>
                    <?php $start = ($page - 1) * $perPage + 1; ?>
                    <?php $end = min($page * $perPage, $totalProjects); ?>
                    <span class="font-medium">Affichage de <?= $start ?>-<?= $end ?> sur <?= $totalProjects ?></span> projets
                <?php else: ?>
                    <span class="font-medium">0 projet trouvé</span>
                <?php endif; ?>
            </p>
            <div class="flex space-x-2">
                <button class="p-2 bg-gray-100 rounded-button text-gray-600 hover:bg-gray-200">
                    <div class="w-5 h-5 flex items-center justify-center">
                        <i class="ri-layout-grid-line"></i>
                    </div>
                </button>
                <button class="p-2 bg-white rounded-button text-gray-400 hover:bg-gray-100 border border-gray-200">
                    <div class="w-5 h-5 flex items-center justify-center">
                        <i class="ri-list-check"></i>
                    </div>
                </button>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($projects as $index => $project): 
                $projectId = $project['id'];
                $techs = $technologies_by_project[$projectId] ?? [];
                $image = $project['image_path'] ?: 'img/placeholder-project.jpg'; // fallback
            ?>
            <div class="project-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition relative">
                <img loading="lazy" class="w-full h-40 object-cover" src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                <div class="p-4">
                    <h2 class="text-lg font-bold"><?= htmlspecialchars($project['title']) ?></h2>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($project['author_name']) ?></p>
                    <div class="mt-2 text-sm flex flex-wrap gap-2">
                        <?php foreach (array_slice($techs, 0, 2) as $tech): ?>
                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs"><?= htmlspecialchars($tech) ?></span>
                        <?php endforeach; ?>
                        <?php if (count($techs) > 2): ?>
                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">+<?= count($techs)-2 ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="font-bold text-indigo"><?= number_format($project['price'], 0) ?> MAD</span>
                        <button onclick='openModal(<?= json_encode($project) ?>, <?= json_encode($techs) ?>)' class="text-primary hover:text-blue-700 font-medium text-sm">Voir plus</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="mt-12 flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <span class="text-sm text-gray-600">Page <?= $page ?> sur <?= $totalPages ?></span>
            </div>
            <div class="flex items-center">
                <div class="mr-4">
                    <select id="perPageSelect" class="px-3 py-2 border border-gray-300 rounded-button text-sm text-gray-800 pr-8 appearance-none">
                        <option value="8" <?= $perPage == 8 ? 'selected' : '' ?>>8 par page</option>
                        <option value="12" <?= $perPage == 12 ? 'selected' : '' ?>>12 par page</option>
                        <option value="24" <?= $perPage == 24 ? 'selected' : '' ?>>24 par page</option>
                        <option value="48" <?= $perPage == 48 ? 'selected' : '' ?>>48 par page</option>
                    </select>
                </div>
                <div class="flex">
                    <!-- Previous -->
                    <?php if ($page > 1): ?>
                    <a href="<?= buildUrl(['page' => $page - 1]) ?>" 
                       class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button mr-2 text-gray-500 hover:bg-gray-50">
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-arrow-left-s-line"></i>
                        </div>
                    </a>
                    <?php else: ?>
                    <span class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button mr-2 text-gray-300 bg-gray-50 cursor-not-allowed">
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-arrow-left-s-line"></i>
                        </div>
                    </span>
                    <?php endif; ?>

                    <!-- Page numbers -->
                    <?php
                    $range = 2;
                    $startPage = max(1, $page - $range);
                    $endPage = min($totalPages, $page + $range);
                    
                    if ($startPage > 1): ?>
                        <a href="<?= buildUrl(['page' => 1]) ?>" class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button mr-2 text-gray-700 hover:bg-gray-50">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="w-10 h-10 flex items-center justify-center text-gray-500">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button mr-2 bg-primary text-white"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= buildUrl(['page' => $i]) ?>" class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button mr-2 text-gray-700 hover:bg-gray-50"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="w-10 h-10 flex items-center justify-center text-gray-500">...</span>
                        <?php endif; ?>
                        <a href="<?= buildUrl(['page' => $totalPages]) ?>" class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button mr-2 text-gray-700 hover:bg-gray-50"><?= $totalPages ?></a>
                    <?php endif; ?>

                    <!-- Next -->
                    <?php if ($page < $totalPages): ?>
                    <a href="<?= buildUrl(['page' => $page + 1]) ?>" 
                       class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button text-gray-500 hover:bg-gray-50">
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-arrow-right-s-line"></i>
                        </div>
                    </a>
                    <?php else: ?>
                    <span class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button text-gray-300 bg-gray-50 cursor-not-allowed">
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-arrow-right-s-line"></i>
                        </div>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Message si aucun résultat -->
        <?php if (empty($projects)): ?>
        <div class="text-center py-16">
            <i class="ri-folder-search-line text-6xl text-gray-300"></i>
            <p class="text-gray-500 mt-4 text-lg">Aucun projet ne correspond à vos critères.</p>
            <a href="projet.php" class="inline-block mt-4 text-primary hover:underline">Réinitialiser les filtres</a>
        </div>
        <?php endif; ?>
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
    
    techFilterBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        techDropdown.classList.toggle('hidden');
    });
    
    document.addEventListener('click', function(e) {
        if (!techFilterBtn.contains(e.target) && !techDropdown.contains(e.target)) {
            techDropdown.classList.add('hidden');
        }
    });
    
    // Price range slider
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    
    priceRange.addEventListener('input', function() {
        priceValue.textContent = this.value + ' MAD';
    });
    
    // Technology checkboxes label update
    const techCheckboxes = document.querySelectorAll('#techDropdown input[type="checkbox"]');
    techCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateTechLabel();
        });
    });
    
    function updateTechLabel() {
        const checked = Array.from(techCheckboxes).filter(cb => cb.checked);
        const labelSpan = document.getElementById('techLabel');
        if (checked.length === 0) {
            labelSpan.textContent = 'Sélectionner';
        } else if (checked.length === 1) {
            const parent = checked[0].closest('.custom-checkbox');
            labelSpan.textContent = parent.textContent.trim();
        } else {
            labelSpan.textContent = checked.length + ' sélectionnées';
        }
    }

    // Soumission automatique pour le select catégorie, tri, et per_page
    const categorySelect = document.getElementById('categorySelect');
    const sortSelect = document.getElementById('sortSelect');
    const perPageSelect = document.getElementById('perPageSelect');

    categorySelect.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    sortSelect.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    perPageSelect.addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', this.value);
        url.searchParams.delete('page'); // revient à la page 1
        window.location.href = url.toString();
    });

    // Pour le slider, on ne soumet pas automatiquement (l'utilisateur peut cliquer sur Rechercher)
});

// Modale : adaptation pour recevoir un objet projet
function openModal(project, technologies) {
    document.getElementById("modalTitle").innerText = project.title;
    document.getElementById("modalAuthor").innerText = `Développeur: ${project.author_name}`;
    document.getElementById("modalCategory").innerText = `Catégorie: ${project.category_name || 'Non catégorisé'}`;
    document.getElementById("modalPrice").innerText = `Prix: ${project.price} MAD`;
    document.getElementById("modalDescription").innerText = project.description;
    document.getElementById("modalImage").src = project.image_path || 'img/placeholder-project.jpg';
    document.getElementById("modalGithub").href = project.github_url || '#';

    
    // Afficher les technologies
    const techContainer = document.getElementById("modalTechnologies");
    techContainer.innerHTML = '';
    technologies.forEach(tech => {
        const span = document.createElement('span');
        span.className = 'bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full';
        span.innerText = tech;
        techContainer.appendChild(span);
    });
    
    document.getElementById("modal").classList.remove("hidden");
}

function closeModal() {
    document.getElementById("modal").classList.add("hidden");
}

// Dans openModal, après avoir rempli les champs, on peut ajouter un événement
document.getElementById('btnAcheter').onclick = function() {
    const projectId = project.id;
    fetch('ajouter_panier.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'project_id=' + projectId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Projet ajouté au panier !');
        } else {
            alert('Erreur : ' + data.error);
        }
    });
};
</script>
</body>
</html>