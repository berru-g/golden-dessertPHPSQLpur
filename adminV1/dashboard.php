<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$host = 'localhost';
$db = 'golden_dessert';
$user = 'root';
$pass = 'root'; // ou ton mot de passe MySQL O2Switch
$charset = 'utf8mb4';
//voir secur.md pour finir la config 
//require __DIR__.'./db_config.php'; 
//$dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";
//$pdo = new PDO($dsn, $config['user'], $config['pass']);
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $messages = $pdo->query("SELECT * FROM contacts ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Base de données</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <h2>
        📬 Dashboard Admin 
        <a class="logout-btn" href="logout.php">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
    </h2>
    
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Rechercher...">
    </div>
    
    <div class="table-container">
    <table id="messagesTable">
        <thead>
            <tr>
                <th data-column="0">Date</th>
                <th data-column="1">Nom</th>
                <th data-column="2">Email</th>
                <th data-column="3">Téléphone</th>
                <th data-column="4">Site web</th>
                <th data-column="5">Message</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($messages as $msg): ?>
            <tr>
                <td><?= htmlspecialchars($msg['created_at'] ?? '') ?></td>
                <td><?= htmlspecialchars($msg['fullname']) ?></td>
                <td>
                   <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="email-link">
                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($msg['email']) ?>
                   </a>
                </td>
                <td><?= htmlspecialchars($msg['telephone']) ?></td>
                <td>
                   <a href="<?= htmlspecialchars($msg['siteweb']) ?>" class="email-link" rel="noopener"
                target="_blank">
                    <?= htmlspecialchars($msg['siteweb']) ?>
                   </a>
                </td>
                <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <div class="pagination" id="pagination">
        <button id="prevBtn" disabled>Précédent</button>
        <div id="pageNumbers"></div>
        <button id="nextBtn">Suivant</button>
    </div>
    <br>
    <div class="footer">
        <a href="https://gael-berru.netlify.app#contact" rel="noopener" target="_blank">
            <span>Interface développée par berru-g | Contacter l'assistance</span>
            <i class="fas fa-headset"></i>
        </a>
    </div>
    <script src="script.js"></script>
</body>
</html>