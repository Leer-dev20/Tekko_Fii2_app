<?php
require_once 'includes/auth.php';
require_once 'includes/cart.php';
requireAuth();

$userId = getUserId();
$cart = getCart();
$total = getCartTotal();

// Traitement de la validation
if (isset($_POST['checkout'])) {
    if (empty($cart)) {
        $error = 'Votre panier est vide.';
    } else {
        $pdo = getPDO();
        try {
            $pdo->beginTransaction();
            foreach ($cart as $item) {
                // Vérifier que le projet existe toujours et est publié
                $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND status = 'published'");
                $stmt->execute([$item['id']]);
                if (!$stmt->fetch()) {
                    throw new Exception("Le projet {$item['title']} n'est plus disponible.");
                }
                // Créer la commande
                $stmt = $pdo->prepare("
                    INSERT INTO orders (buyer_id, project_id, amount, status)
                    VALUES (?, ?, ?, 'completed')
                ");
                $stmt->execute([$userId, $item['id'], $item['price']]);
            }
            $pdo->commit();
            clearCart();
            $success = 'Commande effectuée avec succès !';
            $cart = [];
            $total = 0;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon panier - Tekko-Fii</title>
    <!-- CSS -->
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Mon panier</h1>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (empty($cart)): ?>
            <p class="text-gray-500">Votre panier est vide.</p>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($cart as $item): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($item['title']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= number_format($item['price'], 0) ?> MAD</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="retirer_panier.php?id=<?= $item['id'] ?>" class="text-red-600 hover:text-red-900">Retirer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="1" class="px-6 py-4 text-right font-bold">Total :</td>
                            <td class="px-6 py-4 font-bold"><?= number_format($total, 0) ?> MAD</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <form method="post" class="mt-6">
                <button type="submit" name="checkout" class="bg-primary text-white px-6 py-3 rounded font-medium hover:bg-blue-700 transition">
                    Valider la commande
                </button>
            </form>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>