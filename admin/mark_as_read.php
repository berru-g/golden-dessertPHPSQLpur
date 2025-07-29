<?php
session_start();
require_once 'db_config.php'; // Assurez-vous que ce chemin est correct

header('Content-Type: application/json'); // Cette ligne DOIT être la première

// Debug - à enlever après vérification
error_log("Requête reçue: " . print_r($_POST, true));

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_POST['id'])) {
        throw new Exception('ID manquant');
    }

    $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
    $stmt->execute([$_POST['id']]);

    $unread = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
    
    // Réponse JSON VALIDE
    echo json_encode([
        'success' => true,
        'unread' => (int)$unread
    ]);
    exit();

} catch (Exception $e) {
    // Log l'erreur pour debug
    error_log("Erreur mark_as_read: " . $e->getMessage());
    
    // Réponse d'erreur JSON
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit();
}

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass']);
    
    // 1. Marquer le message comme lu
    $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    
    // 2. Récupérer le nouveau compte de messages non lus
    $unreadCount = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'unread' => $unreadCount
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>