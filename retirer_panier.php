<?php
require_once 'includes/auth.php';
require_once 'includes/cart.php';
requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    removeFromCart($id);
}
header('Location: panier.php');
exit;