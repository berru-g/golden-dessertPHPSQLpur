<?php
/**
 * Configuration sécurisée - À placer HORS du webroot si possible
 * Permissions recommandées : chmod 600 (lecture seule pour le propriétaire)
 */

// Configuration BDD
define('DB_HOST', 'localhost');
define('DB_NAME', 'golden_dessert'); //u667977963_
define('DB_USER', 'root');
define('DB_PASS', 'root'); // À CHANGER EN PRODUCTION

// Credentials admin (utilise bcrypt)
// Pour générer : password_hash('ton_password', PASSWORD_BCRYPT)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2y$10$8dcDLkJIx8P.2Bm7LqvXrusTKWmChgGbzXJ/GUf5l4il6GxnrZaVu'); // = 'golden2'

// Configuration sécurité uploads
define('UPLOAD_DIR', __DIR__ . '/../img/');
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png', 
    'image/gif',
    'image/webp'
]);

// Clé secrète pour CSRF (à générer aléatoirement)
define('CSRF_SECRET', 'change_cette_cle_en_production_' . bin2hex(random_bytes(16)));

// Connexion PDO sécurisée
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Vraies prepared statements
            ];
            
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                $options
            );
        } catch (PDOException $e) {
            error_log("Erreur BDD : " . $e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }
    
    return $pdo;
}

// Génération token CSRF
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Vérification token CSRF
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Vérification admin
function isAdmin() {
    return isset($_SESSION['galerie_admin']) && 
           $_SESSION['galerie_admin'] === true &&
           isset($_SESSION['admin_ip']) &&
           $_SESSION['admin_ip'] === $_SERVER['REMOTE_ADDR'];
}

// Validation sécurisée des uploads
function validateUpload($file) {
    $errors = [];
    
    // Vérifier erreurs upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Erreur lors de l'upload (code: {$file['error']})";
        return $errors;
    }
    
    // Vérifier taille
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        $errors[] = "Fichier trop volumineux (max 5MB)";
    }
    
    // Vérifier extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        $errors[] = "Extension non autorisée";
    }
    
    // Vérifier MIME type réel
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        $errors[] = "Type de fichier non autorisé";
    }
    
    // Vérifier que c'est bien une image
    if (@getimagesize($file['tmp_name']) === false) {
        $errors[] = "Le fichier n'est pas une image valide";
    }
    
    return $errors;
}

// Sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}