<?php
require_once 'config/database.php';

try {
    $pdo = getPDO();
    echo '✅ Connexion réussie à la base "' . DB_NAME . '" !';
} catch (Exception $e) {
    echo '❌ Erreur : ' . $e->getMessage();
}