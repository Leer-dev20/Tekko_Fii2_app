<?php
// config/database.php

// Paramètres de connexion
define('DB_HOST', 'localhost');         // Serveur 
define('DB_NAME', 'tekko_fii_a');        // Nom de la base
define('DB_USER', 'root');             // Utilisateur MySQL
define('DB_PASS', '');                
define('DB_CHARSET', 'utf8mb4');      

/**
 * Retourne une instance PDO connectée à la base de données.
 * @throws PDOException si la connexion échoue
 */
function getPDO(): PDO
{
    static $pdo = null;               // Singleton : une seule connexion par requête

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lancer des exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Tableaux associatifs
            PDO::ATTR_EMULATE_PREPARES   => false,                 // Requêtes préparées réelles
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En production, loggez l'erreur et affichez un message générique
            error_log('Erreur de connexion PDO : ' . $e->getMessage());
            die('Erreur de connexion à la base de données. Veuillez réessayer plus tard.');
        }
    }

    return $pdo;
}