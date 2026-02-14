<?php
// includes/cart.php
require_once __DIR__ . '/../config/database.php';

function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function addToCart($projectId, $price, $title) {
    initCart();
    // Éviter les doublons (on pourrait permettre la quantité, mais pour un projet unique)
    if (!isset($_SESSION['cart'][$projectId])) {
        $_SESSION['cart'][$projectId] = [
            'id' => $projectId,
            'title' => $title,
            'price' => $price
        ];
    }
}

function removeFromCart($projectId) {
    initCart();
    unset($_SESSION['cart'][$projectId]);
}

function getCart() {
    initCart();
    return $_SESSION['cart'];
}

function clearCart() {
    $_SESSION['cart'] = [];
}

function getCartTotal() {
    $total = 0;
    foreach (getCart() as $item) {
        $total += $item['price'];
    }
    return $total;
}