<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$host = 'localhost';
$db = 'u667977963_golden_dessert';
$user = 'u667977963_berru_nico';
$pass = 'm@bddSQL25'; // 
$charset = 'utf8mb4';
//voir secur.md pour finir la config 
//require __DIR__.'./db_config.php'; 
//$dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";
//$pdo = new PDO($dsn, $config['user'], $config['pass']);


try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);// récupérer l'état 'is_read'
    $messages = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();
    $unread_count = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
    $total_messages = count($messages);
    $messages = $pdo->query("SELECT * FROM contacts ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
    <!-- 
    ============================================
       Developed by : https://github.com/berru-g/
       Project : Interface Admin
       Version : 1.0.2  | 10/06/2025
       Licence : The MIT License (MIT)
       Copyright (c) 2025 Berru
    ============================================
-->
<head>
    <meta charset="UTF-8">
    <title>GDbdd</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../logobdd.png" />
    <link rel="apple-touch-icon" href="../logobdd.png" />
    <meta name="description"
        content="Interface admin pour gérer la base de données client de son site.">
    <meta name="keywords"
        content="interface admin, outils de gestion de base de donnée, formulaire et interface administrateur,">
    <meta name="author" content="Gael Berru.">
    <meta name="robots" content="noai">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h2>
        <button id="inboxMenu" class="inbox-icon">
             <!--<i class="fas fa-inbox"></i>-->
             <i class="fas fa-database"></i>
        </button>
        SQL Admin
        <button id="importJsonBtn" class="download-btn" title="importer une bdd">
            <i class="fa-solid fa-file-import"></i>
        </button>
        <button id="exportJsonBtn" class="download-btn" title="exorter la bdd">
            <i class="fa-solid fa-file-export"></i> 
        </button>
        <span class="notification-badge" id="unreadBadge" title="Messages non lue">
            <i class="fas fa-envelope"></i>
        <span id="unreadCount"><?= $unread_count ?></span>
        </span>
        <span><a class="logout-btn" href="logout.php" title="Se déconnecter">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a></span>
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
                <th data-column="3">Statut</th>
                <th data-column="4">Téléphone</th>
                <th data-column="5">budget</th>
                <th data-column="6">Message</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($messages as $msg): ?>
                
            <tr class="message-row <?= $msg['is_read'] ? '' : 'unread' ?>" 
                data-id="<?= $msg['id'] ?>"
                data-read="<?= $msg['is_read'] ?>"
                data-fullmessage="<?= htmlspecialchars($msg['message']) ?>">
                <td><?= htmlspecialchars($msg['created_at'] ?? '') ?></td>
                <td><?= htmlspecialchars($msg['fullname']) ?></td>
                <td>
                   <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="email-link">
                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($msg['email']) ?>
                   </a>
                </td>
                <td><?= htmlspecialchars($msg['statut']) ?></td>
                <td><?= htmlspecialchars($msg['telephone']) ?></td>
                <td><?= htmlspecialchars($msg['budget']) ?></td>
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
        <a href="https://gael-berru.netlify.app#formulaire" rel="noopener" target="_blank">
            <span>v.1.2 développée par berru-g</span>
            <i class="fas fa-headset"></i>
        </a>
    </div>
    
    <!--<nav class="bottom-nav">
        <a href="#"><i class="fas fa-home"></i><span>Accueil</span></a>
        <a href="#"><i class="fas fa-cog"></i><span>Configuration</span></a>
        <a href="index.php"><i class="fas fa-arrow-left"></i><span>Retour</span></a>
    </nav>-->
    <script>
       const hamburgerMenu = document.querySelector('.inbox-icon');

hamburgerMenu.addEventListener('click', () => {
  Swal.fire({
    title: '<h3 style="color:#ab9ff2; font-weight:600; margin-bottom: 1rem;">⚙️</h3>',
    html: `
      <div style="display: flex; flex-direction: column; gap: 12px; font-size: 1rem;">
        <a href="./facture.html" style="padding: 10px 16px; background: #ab9ff2; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 500; transition: background 0.3s;">
          📑 Facture
        </a>
        <a href="./php-generate-hash.php" style="padding: 10px 16px; background: #ab9ff2; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 500; transition: background 0.3s;">
          ✅ Generate Hash
        </a>
        <a href="#" style="padding: 10px 16px; background: #ab9ff2; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 500; transition: background 0.3s;">
          <i class="fas fa-headset"></i> Assistance
        </a>
      </div>
    `,
    showCloseButton: true,
    showConfirmButton: false,
    background: '#f4f3fc',
    customClass: {
      popup: 'custom-swal-popup',
      closeButton: 'custom-swal-close-button',
      content: 'custom-swal-content',
    }
  });
});

</script>
    <script src="script.js"></script>
</body>
</html>
