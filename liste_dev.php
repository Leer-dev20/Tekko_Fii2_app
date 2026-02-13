<?php
require_once 'config/database.php';

$pdo = getPDO();

// ------------------------------------------------------------
// 1. Récupération et validation des paramètres GET
// ------------------------------------------------------------
$search       = isset($_GET['search']) ? trim($_GET['search']) : '';
$skills       = isset($_GET['skills']) ? (array)$_GET['skills'] : [];
$available    = isset($_GET['available']) ? true : false;
$rate_max     = isset($_GET['rate_max']) ? (int)$_GET['rate_max'] : 0;
$location     = isset($_GET['location']) ? $_GET['location'] : '';
$sort         = isset($_GET['sort']) ? $_GET['sort'] : 'rating';
$page         = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage      = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 6;
$allowedPerPage = [6, 12, 24, 48];
if (!in_array($perPage, $allowedPerPage)) $perPage = 6;
$offset       = ($page - 1) * $perPage;

// ------------------------------------------------------------
// 2. Récupération de toutes les compétences pour le dropdown
// ------------------------------------------------------------
$allSkills = $pdo->query("SELECT id, name FROM skills ORDER BY name")->fetchAll();

// ------------------------------------------------------------
// 3. Valeur maximale du tarif pour le slider
// ------------------------------------------------------------
$maxRate = $pdo->query("SELECT MAX(hourly_rate) FROM developers")->fetchColumn();
if (!$maxRate) $maxRate = 1500;
if ($rate_max == 0) $rate_max = $maxRate;
$rate_max = min($rate_max, $maxRate);

// ------------------------------------------------------------
// 4. Construction de la requête de comptage (avec gestion des compétences)
// ------------------------------------------------------------
$countSql = "SELECT COUNT(DISTINCT d.id) FROM developers d
             JOIN users u ON d.user_id = u.id";
$countParams = [];

// Jointure sur les compétences si besoin
if (!empty($skills)) {
    $placeholders = implode(',', array_fill(0, count($skills), '?'));
    $countSql .= " JOIN developer_skills ds ON d.id = ds.developer_id
                   WHERE ds.skill_id IN ($placeholders)";
    $countParams = array_merge($countParams, $skills);
} else {
    $countSql .= " WHERE 1=1";
}

// Recherche
if (!empty($search)) {
    $countSql .= " AND (u.full_name LIKE ? OR d.title LIKE ? OR d.bio LIKE ?)";
    $like = '%' . $search . '%';
    $countParams = array_merge($countParams, [$like, $like, $like]);
}

// Disponibilité
if ($available) {
    $countSql .= " AND d.availability_status = 'available'";
}

// Tarif maximum
if ($rate_max < $maxRate) {
    $countSql .= " AND d.hourly_rate <= ?";
    $countParams[] = $rate_max;
}

// Localisation
if (!empty($location)) {
    $countSql .= " AND d.location = ?";
    $countParams[] = $location;
}

$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($countParams);
$totalDevelopers = $stmtCount->fetchColumn();
$totalPages = ceil($totalDevelopers / $perPage);
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

// ------------------------------------------------------------
// 5. Requête pour récupérer les développeurs de la page courante
// ------------------------------------------------------------
$sql = "SELECT 
            u.id,
            u.full_name,
            u.avatar,
            d.id as developer_id,
            d.hourly_rate,
            d.availability_status,
            d.location,
            d.years_experience,
            d.title,
            d.bio,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT r.id) as review_count
        FROM developers d
        JOIN users u ON d.user_id = u.id
        LEFT JOIN reviews r ON r.item_type = 'developer' AND r.item_id = d.id";

$dataParams = [];

// Jointure compétences si nécessaire
if (!empty($skills)) {
    $placeholders = implode(',', array_fill(0, count($skills), '?'));
    $sql .= " JOIN developer_skills ds ON d.id = ds.developer_id
              WHERE ds.skill_id IN ($placeholders)";
    $dataParams = array_merge($dataParams, $skills);
} else {
    $sql .= " WHERE 1=1";
}

// Recherche
if (!empty($search)) {
    $sql .= " AND (u.full_name LIKE ? OR d.title LIKE ? OR d.bio LIKE ?)";
    $like = '%' . $search . '%';
    $dataParams = array_merge($dataParams, [$like, $like, $like]);
}

// Disponibilité
if ($available) {
    $sql .= " AND d.availability_status = 'available'";
}

// Tarif maximum
if ($rate_max < $maxRate) {
    $sql .= " AND d.hourly_rate <= ?";
    $dataParams[] = $rate_max;
}

// Localisation
if (!empty($location)) {
    $sql .= " AND d.location = ?";
    $dataParams[] = $location;
}

$sql .= " GROUP BY d.id";

// Tri
switch ($sort) {
    case 'experience':
        $sql .= " ORDER BY d.years_experience DESC";
        break;
    case 'rate_low':
        $sql .= " ORDER BY d.hourly_rate ASC";
        break;
    case 'rate_high':
        $sql .= " ORDER BY d.hourly_rate DESC";
        break;
    case 'rating':
    default:
        $sql .= " ORDER BY avg_rating DESC";
        break;
}

// Pagination
$sql .= " LIMIT ? OFFSET ?";
$dataParams[] = $perPage;
$dataParams[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($dataParams);
$developers = $stmt->fetchAll();

// ------------------------------------------------------------
// 6. Récupération des compétences et portfolio pour les développeurs affichés
// ------------------------------------------------------------
$skills_by_dev = [];
$portfolio_by_dev = [];
if (!empty($developers)) {
    $devIds = array_column($developers, 'developer_id');
    $placeholders = implode(',', array_fill(0, count($devIds), '?'));

    // Compétences
    $stmtSkills = $pdo->prepare("
        SELECT ds.developer_id, s.id, s.name
        FROM developer_skills ds
        JOIN skills s ON ds.skill_id = s.id
        WHERE ds.developer_id IN ($placeholders)
    ");
    $stmtSkills->execute($devIds);
    foreach ($stmtSkills as $row) {
        $skills_by_dev[$row['developer_id']][] = ['id' => $row['id'], 'name' => $row['name']];
    }

    // Projets récents (3 max)
    $stmtPortfolio = $pdo->prepare("
        SELECT developer_id, title
        FROM portfolio_items
        WHERE developer_id IN ($placeholders)
        ORDER BY created_at DESC
    ");
    $stmtPortfolio->execute($devIds);
    foreach ($stmtPortfolio as $row) {
        $portfolio_by_dev[$row['developer_id']][] = $row['title'];
    }
    foreach ($portfolio_by_dev as $devId => $titles) {
        $portfolio_by_dev[$devId] = array_slice($titles, 0, 3);
    }
}

// ------------------------------------------------------------
// 7. Fonction utilitaire pour construire les URL de pagination/filtres
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
    return 'liste_dev.php?' . http_build_query($query);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tekko-Fii - Développeurs</title>
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
        .dev-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .dev-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .availability-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
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
        .custom-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        .custom-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e5e7eb;
            transition: .4s;
            border-radius: 34px;
        }
        .switch-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .switch-slider {
            background-color: #4285F4;
        }
        input:checked + .switch-slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body class="bg-white min-h-screen">

    <!-- Header -->
    <header class="w-full py-4 px-6 flex justify-between items-center border-b border-gray-100">
        <a href="index.php" class="text-2xl font-bold font-['Pacifico'] text-gray-900">Tekko-Fii</a>
        <nav class="hidden md:flex space-x-8">
            <a href="projet.php" class="text-gray-800 hover:text-primary font-medium">Projets</a>
            <a href="liste_dev.php" class="text-primary font-medium border-b-2 border-primary">Développeurs</a>
            <a href="entreprise.php" class="text-gray-800 hover:text-primary font-medium">Entreprise</a>
        </nav>
        <div class="flex items-center space-x-4">
            <a href="inscrire.php" class="text-gray-800 hover:text-primary font-medium">S'inscrire</a>
            <a href="connexion.php" class="bg-primary text-white px-5 py-2 rounded-button font-medium hover:bg-blue-600">Se connecter</a>
        </div>
    </header>

    <!-- Page Title -->
    <div class="bg-gray-50 py-8">
        <div class="container mx-auto px-6">
            <h1 class="text-3xl font-bold text-gray-900">Trouvez votre développeur</h1>
            <p class="text-gray-600 mt-2">Découvrez des milliers de développeurs talentueux prêts à réaliser vos projets</p>
        </div>
    </div>

    <!-- Formulaire de recherche et filtres (GET) -->
    <section class="py-8 px-6 border-b border-gray-200">
        <div class="container mx-auto">
            <form method="get" action="liste_dev.php" id="filterForm">
                <!-- Barre de recherche -->
                <div class="flex mb-8">
                    <div class="relative flex-1">
                        <input type="text" name="search" id="searchInput" 
                               placeholder="Rechercher un développeur..." 
                               value="<?= htmlspecialchars($search) ?>"
                               class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-button focus:border-primary text-gray-800">
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="ri-search-line w-6 h-6 flex items-center justify-center"></i>
                        </div>
                    </div>
                    <button type="submit" class="bg-primary text-white px-6 py-3 ml-4 rounded-button font-medium hover:bg-blue-600 transition-colors whitespace-nowrap">
                        Rechercher
                    </button>
                </div>

                <!-- Filtres -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Compétences (dropdown) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Compétences</label>
                        <div class="relative">
                            <button type="button" id="skillsFilterBtn" 
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-button bg-white text-gray-800 text-left flex justify-between items-center">
                                <span id="skillsLabel">
                                    <?php
                                    $selectedCount = count($skills);
                                    if ($selectedCount === 0) echo 'Sélectionner';
                                    elseif ($selectedCount === 1) {
                                        // Trouver le nom de la compétence sélectionnée
                                        $skillId = $skills[0];
                                        $skillName = '';
                                        foreach ($allSkills as $s) {
                                            if ($s['id'] == $skillId) $skillName = $s['name'];
                                        }
                                        echo htmlspecialchars($skillName);
                                    } else {
                                        echo $selectedCount . ' sélectionnés';
                                    }
                                    ?>
                                </span>
                                <i class="ri-arrow-down-s-line w-4 h-4"></i>
                            </button>
                            <div id="skillsDropdown" class="hidden absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-button shadow-lg z-10 max-h-60 overflow-y-auto p-3">
                                <?php foreach ($allSkills as $skill): ?>
                                <label class="custom-checkbox block mb-2">
                                    <input type="checkbox" name="skills[]" value="<?= $skill['id'] ?>" 
                                           <?= in_array($skill['id'], $skills) ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    <?= htmlspecialchars($skill['name']) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Disponibilité (switch) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Disponibilité</label>
                        <div class="flex items-center space-x-4">
                            <label class="custom-switch">
                                <input type="checkbox" name="available" id="availabilitySwitch" 
                                       <?= $available ? 'checked' : '' ?>>
                                <span class="switch-slider"></span>
                            </label>
                            <span class="text-sm text-gray-700">Disponible maintenant</span>
                        </div>
                    </div>

                    <!-- Tarif horaire (slider) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tarif horaire max.</label>
                        <div class="px-2">
                            <input type="range" name="rate_max" id="rateRange" 
                                   min="0" max="<?= $maxRate ?>" value="<?= $rate_max ?>" 
                                   class="range-slider w-full h-2 bg-gray-200 rounded-full appearance-none cursor-pointer">
                            <div class="flex justify-between mt-2 text-sm text-gray-600">
                                <span>0 MAD</span>
                                <span id="rateValue"><?= number_format($rate_max, 0) ?> MAD</span>
                                <span><?= number_format($maxRate, 0) ?> MAD</span>
                            </div>
                        </div>
                    </div>

                    <!-- Localisation -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Localisation</label>
                        <div class="relative">
                            <select name="location" id="locationSelect" 
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-button appearance-none bg-white text-gray-800 pr-8">
                                <option value="">Toutes les localisations</option>
                                <option value="France" <?= $location == 'France' ? 'selected' : '' ?>>France</option>
                                <option value="Belgique" <?= $location == 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                                <option value="Suisse" <?= $location == 'Suisse' ? 'selected' : '' ?>>Suisse</option>
                                <option value="Canada" <?= $location == 'Canada' ? 'selected' : '' ?>>Canada</option>
                                <option value="Maroc" <?= $location == 'Maroc' ? 'selected' : '' ?>>Maroc</option>
                                <option value="Sénégal" <?= $location == 'Sénégal' ? 'selected' : '' ?>>Sénégal</option>
                                <option value="remote" <?= $location == 'remote' ? 'selected' : '' ?>>Télétravail uniquement</option>
                            </select>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none">
                                <i class="ri-arrow-down-s-line w-4 h-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Champs cachés pour le tri et la pagination -->
                <input type="hidden" name="sort" id="sortInput" value="<?= htmlspecialchars($sort) ?>">
                <input type="hidden" name="per_page" id="perPageInput" value="<?= $perPage ?>">
            </form>
        </div>
    </section>

    <!-- Résultats -->
    <section class="py-10 px-6">
        <div class="container mx-auto">
            <!-- En-tête des résultats avec compteur -->
            <div class="flex justify-between items-center mb-6">
                <p class="text-gray-600">
                    <?php if ($totalDevelopers > 0): ?>
                        <?php $start = ($page - 1) * $perPage + 1; ?>
                        <?php $end = min($page * $perPage, $totalDevelopers); ?>
                        <span class="font-medium">Affichage de <?= $start ?>-<?= $end ?> sur <?= $totalDevelopers ?></span> développeur<?= $totalDevelopers > 1 ? 's' : '' ?>
                    <?php else: ?>
                        <span class="font-medium">0 développeur trouvé</span>
                    <?php endif; ?>
                </p>
                <div class="flex items-center space-x-4">
                    <label class="text-sm text-gray-600">Trier par:</label>
                    <div class="relative">
                        <select id="sortSelect" class="px-4 py-2 border border-gray-300 rounded-button appearance-none bg-white text-gray-800 pr-8">
                            <option value="rating" <?= $sort == 'rating' ? 'selected' : '' ?>>Évaluation</option>
                            <option value="experience" <?= $sort == 'experience' ? 'selected' : '' ?>>Expérience</option>
                            <option value="rate_low" <?= $sort == 'rate_low' ? 'selected' : '' ?>>Tarif (croissant)</option>
                            <option value="rate_high" <?= $sort == 'rate_high' ? 'selected' : '' ?>>Tarif (décroissant)</option>
                        </select>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none">
                            <i class="ri-arrow-down-s-line w-4 h-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grille des cartes développeurs -->
            <div id="developersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($developers as $dev): 
                    $devId = $dev['developer_id'];
                    $skillsDev = $skills_by_dev[$devId] ?? [];
                    $portfolio = $portfolio_by_dev[$devId] ?? [];
                    $avatar = $dev['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($dev['full_name']) . '&background=4285F4&color=fff&size=200';
                    
                    // Badge disponibilité
                    switch ($dev['availability_status']) {
                        case 'available':
                            $badgeClass = 'bg-green-100 text-green-800';
                            $badgeDot = 'bg-green-500';
                            $badgeText = 'Disponible';
                            break;
                        case 'partial':
                            $badgeClass = 'bg-yellow-100 text-yellow-800';
                            $badgeDot = 'bg-yellow-500';
                            $badgeText = 'Dispo. partielle';
                            break;
                        default:
                            $badgeClass = 'bg-red-100 text-red-800';
                            $badgeDot = 'bg-red-500';
                            $badgeText = 'Non disponible';
                    }
                ?>
                <div class="dev-card bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg">
                    <div class="p-5">
                        <div class="flex items-start">
                            <div class="w-20 h-20 rounded-full overflow-hidden mr-4 flex-shrink-0 bg-gray-100">
                                <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($dev['full_name']) ?>" 
                                     class="w-full h-full object-cover object-top"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($dev['full_name']) ?>&background=4285F4&color=fff&size=200'">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-lg truncate"><?= htmlspecialchars($dev['full_name']) ?></h3>
                                    <div class="flex items-center flex-shrink-0 ml-2">
                                        <i class="ri-star-fill text-yellow-400 w-4 h-4"></i>
                                        <span class="text-sm text-gray-600 ml-1"><?= number_format($dev['avg_rating'], 1) ?></span>
                                        <span class="text-xs text-gray-500 ml-1">(<?= $dev['review_count'] ?>)</span>
                                    </div>
                                </div>
                                <p class="text-gray-600 text-sm truncate"><?= htmlspecialchars($dev['title'] ?? 'Développeur') ?></p>
                                <div class="flex mt-2">
                                    <span class="<?= $badgeClass ?> availability-badge">
                                        <span class="w-2 h-2 <?= $badgeDot ?> rounded-full mr-1"></span>
                                        <?= $badgeText ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($skillsDev)): ?>
                        <div class="mt-4">
                            <div class="flex flex-wrap gap-2">
                                <?php foreach (array_slice($skillsDev, 0, 4) as $skill): ?>
                                    <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full"><?= htmlspecialchars($skill['name']) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($skillsDev) > 4): ?>
                                    <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full">+<?= count($skillsDev)-4 ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <p class="text-sm text-gray-600 mt-3 line-clamp-2">
                            <?= htmlspecialchars($dev['bio'] ?? 'Aucune description fournie.') ?>
                        </p>
                        <?php if (!empty($portfolio)): ?>
                        <div class="border-t border-gray-100 mt-3 pt-3">
                            <p class="text-xs font-medium text-gray-500 mb-2">PROJETS RÉCENTS</p>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <?php foreach ($portfolio as $project): ?>
                                    <li class="flex items-start">
                                        <i class="ri-checkbox-circle-fill text-primary w-4 h-4 mr-1 mt-0.5 flex-shrink-0"></i>
                                        <span class="truncate"><?= htmlspecialchars($project) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between items-center mt-4">
                            <div>
                                <span class="font-bold text-xl"><?= number_format($dev['hourly_rate'], 0) ?></span>
                                <span class="text-sm text-gray-500">MAD/h</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="w-10 h-10 border border-gray-200 rounded-button flex items-center justify-center hover:bg-gray-50 transition">
                                    <i class="ri-heart-line text-gray-500"></i>
                                </button>
                                <?php if ($dev['availability_status'] != 'unavailable'): ?>
                                    <a href="#" class="bg-primary text-white px-4 py-2 rounded-button font-medium hover:bg-blue-600 transition-colors whitespace-nowrap">Contacter</a>
                                <?php else: ?>
                                    <span class="bg-gray-200 text-gray-500 px-4 py-2 rounded-button font-medium cursor-not-allowed">Contacter</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="mt-12 flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <span class="text-sm text-gray-600">
                        Page <?= $page ?> sur <?= $totalPages ?>
                    </span>
                </div>
                <div class="flex items-center">
                    <div class="mr-4">
                        <select id="perPageSelect" class="px-3 py-2 border border-gray-300 rounded-button text-sm text-gray-800 pr-8 appearance-none">
                            <option value="6" <?= $perPage == 6 ? 'selected' : '' ?>>6 par page</option>
                            <option value="12" <?= $perPage == 12 ? 'selected' : '' ?>>12 par page</option>
                            <option value="24" <?= $perPage == 24 ? 'selected' : '' ?>>24 par page</option>
                            <option value="48" <?= $perPage == 48 ? 'selected' : '' ?>>48 par page</option>
                        </select>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none">
                            <i class="ri-arrow-down-s-line w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="flex">
                        <!-- Page précédente -->
                        <?php if ($page > 1): ?>
                        <a href="<?= buildUrl(['page' => $page - 1]) ?>" 
                           class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button mr-2 text-gray-500 hover:bg-gray-50">
                            <i class="ri-arrow-left-s-line w-4 h-4"></i>
                        </a>
                        <?php else: ?>
                        <span class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button mr-2 text-gray-300 bg-gray-50 cursor-not-allowed">
                            <i class="ri-arrow-left-s-line w-4 h-4"></i>
                        </span>
                        <?php endif; ?>

                        <!-- Numéros de pages -->
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

                        <!-- Page suivante -->
                        <?php if ($page < $totalPages): ?>
                        <a href="<?= buildUrl(['page' => $page + 1]) ?>" 
                           class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button text-gray-500 hover:bg-gray-50">
                            <i class="ri-arrow-right-s-line w-4 h-4"></i>
                        </a>
                        <?php else: ?>
                        <span class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-button text-gray-300 bg-gray-50 cursor-not-allowed">
                            <i class="ri-arrow-right-s-line w-4 h-4"></i>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Message si aucun résultat -->
            <?php if (empty($developers) && $totalDevelopers == 0): ?>
            <div class="text-center py-16">
                <i class="ri-user-search-line text-6xl text-gray-300"></i>
                <p class="text-gray-500 mt-4 text-lg">Aucun développeur ne correspond à vos critères.</p>
                <a href="liste_dev.php" class="inline-block mt-4 text-primary hover:underline">Réinitialiser les filtres</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 px-6 mt-12">
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
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Développeurs populaires</a></li>
                        <li><a href="#" class="hover:text-white">Nouveaux talents</a></li>
                        <li><a href="#" class="hover:text-white">Projets disponibles</a></li>
                        <li><a href="#" class="hover:text-white">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Ressources</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Documentation</a></li>
                        <li><a href="#" class="hover:text-white">Guide du développeur</a></li>
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
                    <i class="ri-visa-fill ri-lg"></i>
                    <i class="ri-mastercard-fill ri-lg"></i>
                    <i class="ri-paypal-fill ri-lg"></i>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // ========== DYNAMIQUE DES FILTRES ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Dropdown des compétences
            const skillsFilterBtn = document.getElementById('skillsFilterBtn');
            const skillsDropdown = document.getElementById('skillsDropdown');
            if (skillsFilterBtn && skillsDropdown) {
                skillsFilterBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    skillsDropdown.classList.toggle('hidden');
                });
                document.addEventListener('click', function(e) {
                    if (!skillsFilterBtn.contains(e.target) && !skillsDropdown.contains(e.target)) {
                        skillsDropdown.classList.add('hidden');
                    }
                });
            }

            // Mise à jour du label du bouton compétences
            function updateSkillsLabel() {
                const checkboxes = document.querySelectorAll('#skillsDropdown input[type="checkbox"]');
                const checked = Array.from(checkboxes).filter(cb => cb.checked);
                const labelSpan = document.getElementById('skillsLabel');
                if (checked.length === 0) {
                    labelSpan.textContent = 'Sélectionner';
                } else if (checked.length === 1) {
                    const parent = checked[0].closest('.custom-checkbox');
                    labelSpan.textContent = parent.textContent.trim();
                } else {
                    labelSpan.textContent = checked.length + ' sélectionnés';
                }
            }
            const skillCheckboxes = document.querySelectorAll('#skillsDropdown input[type="checkbox"]');
            skillCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateSkillsLabel);
            });

            // Slider tarif
            const rateRange = document.getElementById('rateRange');
            const rateValue = document.getElementById('rateValue');
            if (rateRange && rateValue) {
                rateRange.addEventListener('input', function() {
                    rateValue.textContent = this.value + ' MAD';
                });
            }

            // Tri : changement du select -> soumet le formulaire
            const sortSelect = document.getElementById('sortSelect');
            const sortInput = document.getElementById('sortInput');
            if (sortSelect && sortInput) {
                sortSelect.addEventListener('change', function() {
                    sortInput.value = this.value;
                    document.getElementById('filterForm').submit();
                });
            }

            // Switch disponibilité : soumission automatique
            const availabilitySwitch = document.getElementById('availabilitySwitch');
            if (availabilitySwitch) {
                availabilitySwitch.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            }

            // Localisation select : soumission automatique
            const locationSelect = document.getElementById('locationSelect');
            if (locationSelect) {
                locationSelect.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            }

            // Nombre par page : changement -> soumet avec per_page et page=1
            const perPageSelect = document.getElementById('perPageSelect');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('per_page', this.value);
                    url.searchParams.delete('page'); // retour page 1
                    window.location.href = url.toString();
                });
            }
        });
    </script>
</body>
</html>