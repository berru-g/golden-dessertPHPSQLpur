<?php
session_start();
require_once 'figue.php';

$pdo = getDbConnection();

// Créer la table si elle n'existe pas
$pdo->exec("CREATE TABLE IF NOT EXISTS galerie_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Initialiser images par défaut
$count = $pdo->query("SELECT COUNT(*) FROM galerie_images")->fetchColumn();
if ($count == 0) {
    $defaultImages = [
        ['../img/citron-yuzu.jpg', 'Entremet citron Yuzu'],
        ['../img/framboise-intense.jpg', 'Entremet framboise intense'],
        ['../img/Macarons en création.jpg', 'Macarons en création'],
        ['../img/Phare 2 _ La Mangue.jpg', 'Phare 2 - La Mangue'],
        ['../img/Fondant au chocolat.jpg', 'Fondant au chocolat'],
        ['../img/Mon cheesecake caramel.jpg', 'Mon cheesecake caramel'],
        ['../img/Crumble pommes.jpg', 'Crumble pommes'],
        ['../img/Passion chocolat lait.jpg', 'Passion chocolat lait'],
        ['../img/Coques macarons.jpg', 'Coques macarons'],
        ['../img/noixcoco.jpg', 'Noix de coco en trompe l\'oeil'],
        ['../img/citrontrompeloeil.jpg', 'Mini citron en trompe l\'oeil'],
        ['../img/Chou craquelin vanille.jpg', 'Chou craquelin vanille'],
        ['../img/Mon Bounty.jpg', 'Mon Bounty'],
        ['../img/Entremet rose litchi framboise.jpg', 'Entremet rose litchi framboise'],
        ['../img/Ma noix de coco.jpg', 'Ma noix de coco'],
        ['../img/Phare 3 _ Le Gâteau Nantais.jpg', 'Phare 3 - Le Gâteau Nantais'],
        ['../img/Citron yuzu.jpg', 'Citron yuzu'],
        ['../img/Royal.jpg', 'Royal'],
        ['../img/Entremet Mojito.jpg', 'Entremet Mojito'],
        ['../img/Crumble poire chocolat.jpg', 'Crumble poire chocolat'],
        ['../img/Entremet façon Bueno.jpg', 'Entremet façon Bueno'],
        ['../img/Phare 1 _ L_Entremet façon Raffaello.jpg', 'Phare 1 - L\'Entremet façon Raffaello'],
        ['../img/Coco exotique mangue.jpg', 'Coco exotique mangue']
    ];

    $stmt = $pdo->prepare("INSERT INTO galerie_images (image_path, title, display_order) VALUES (?, ?, ?)");
    foreach ($defaultImages as $index => $image) {
        $stmt->execute([$image[0], $image[1], $index]);
    }
}

// Traitement login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (verifyCsrfToken($csrf)) {
        if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
            $_SESSION['galerie_admin'] = true;
            $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['admin_login_time'] = time();
            header('Location: index.php');
            exit;
        } else {
            $loginError = "Identifiants incorrects";
        }
    } else {
        $loginError = "Token CSRF invalide";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$isAdmin = isAdmin();

// Récupérer les images
$stmt = $pdo->query("SELECT * FROM galerie_images ORDER BY display_order, created_at DESC");
$images = $stmt->fetchAll();

$csrfToken = generateCsrfToken();
?>
<doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Golden Dessert créateur de desserts haut de gamme pour restaurateurs</title>
    <meta name="description"
        content="Golden Dessert propose aux restaurateurs des desserts premium prêts à servir, faits avec passion et exigence par des artisans pâtissiers." />
    <meta name="keywords"
        content="desserts pour restaurant, pâtisserie artisanale, entremets haut de gamme, fournisseur desserts, Golden Dessert, pâtissier professionnel, livraison desserts restaurant" />
    <meta name="author" content="Golden Dessert" />
    <meta name="robots" content="index, follow" />
    <!-- FAVICON -->
    <link rel="shortcut icon" href="./golden-dessert-logo.png" />
    <link rel="apple-touch-icon" href="./golden-dessert-logo.png" />
    <!-- OPEN GRAPH (réseaux sociaux) -->
    <meta property="og:title" content="Golden Dessert | Desserts premium pour restaurateurs" />
    <meta property="og:description"
        content="Découvrez Golden Dessert, laboratoire artisanal spécialisé dans la création de desserts haut de gamme prêts à servir pour la restauration." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://goldendessert.fr/" />
    <meta property="og:image" content="https://goldendessert.fr/img/golden-dessert-logo.png" />
    <meta property="og:site_name" content="Golden Dessert" />
    <link rel="stylesheet" href="../style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/three@0.155.0/examples/js/loaders/GLTFLoader.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600;800&family=Playfair+Display:ital,wght@1,600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="../script.js"></script>
</head>
<body>
    <div class="loader">
        <img src="../golden-dessert-logo.png" alt="Golden Dessert" />
    </div>

    <nav>
        <div class="logo-container">
            <img src="../golden-dessert-logo.png" alt="LogoGD" class="logo-img" />
        </div>
        <div class="hamburger">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>
        <ul class="nav-links">
            <li><a href="../index.html">HOME</a></li>
            <li><a href="../histoire.html">HISTOIRE</a></li>
            <li><a href="../#">GALERIE</a></li>
            <li><a href="../formulaire/index.html">CONTACT</a></li>
            <!--<li><a href="./admin/login.php"><i class="fa-solid fa-utensils"></i></a></li>-->
        </ul>
    </nav>


    <div class="admin-bar">
        <?php if (!$isAdmin): ?>
            <button class="btn" onclick="showLogin()" style="background: transparent; color: transparent; border: none;">
                <i class="fas fa-cog"></i> Mode Admin
            </button>
        <?php else: ?>
            <a href="?logout=1" class="btn" style="background: #e74c3c; border: none;">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
            <button class="btn" onclick="showAddImage()" style="background: #27ae60; border: none; margin-left: 10px;">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        <?php endif; ?>
    </div>

    <section class="gallery">
        <h2 class="section-title">Quelques créations phares</h2>

        <div class="gallery-grid" id="galleryGrid">
            <?php foreach ($images as $image): ?>
                <div class="gallery-item" data-id="<?= htmlspecialchars($image['id']) ?>">
                    <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="<?= htmlspecialchars($image['title']) ?>" />
                    <?php if ($isAdmin): ?>
                        <div class="admin-controls">
                            <button class="edit-btn" onclick="changeImage(<?= htmlspecialchars($image['id']) ?>)">
                                <i class="fas fa-image"></i>
                            </button>
                            <button class="delete-btn" onclick="deleteImage(<?= htmlspecialchars($image['id']) ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    <div class="gallery-item-caption">
                        <?php if ($isAdmin): ?>
                            <input type="text" class="edit-title-input" 
                                   value="<?= htmlspecialchars($image['title']) ?>"
                                   data-id="<?= htmlspecialchars($image['id']) ?>"
                                   onchange="updateTitle(<?= htmlspecialchars($image['id']) ?>, this.value)">
                        <?php else: ?>
                            <?= htmlspecialchars($image['title']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="../formulaire/" class="btn">Recevoir le catalogue complet</a>
    </section>

    <!-- POPUP LOGIN -->
    <div id="loginPopup" class="popup">
        <div class="popup-content">
            <h3>Connexion Admin</h3>
            <?php if (isset($loginError)): ?>
                <div class="message error"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="text" name="username" placeholder="Nom d'utilisateur" required autocomplete="username">
                <input type="password" name="password" placeholder="Mot de passe" required autocomplete="current-password">
                <button type="submit" name="login_submit">Se connecter</button>
            </form>
            <button onclick="hideLogin()" style="background: #666;">Annuler</button>
        </div>
    </div>

    <!-- POPUP UPLOAD IMAGE -->
    <div id="uploadPopup" class="popup">
        <div class="popup-content">
            <h3>Changer l'image</h3>
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                <input type="hidden" name="id" id="uploadImageId">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit">Uploader</button>
            </form>
            <div id="uploadMessage"></div>
            <button onclick="hideUpload()" style="background: #666;">Annuler</button>
        </div>
    </div>

    <!-- POPUP AJOUT IMAGE -->
    <div id="addImagePopup" class="popup">
        <div class="popup-content">
            <h3>Ajouter une image</h3>
            <form id="addImageForm" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Titre de l'image" required maxlength="255">
                <input type="file" name="new_image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit">Ajouter</button>
            </form>
            <div id="addImageMessage"></div>
            <button onclick="hideAddImage()" style="background: #666;">Annuler</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <?php require_once '../includes/footer.php' ?>
    
    <script>
        const CSRF_TOKEN = '<?= htmlspecialchars($csrfToken) ?>';
        let currentImageId = null;

        function showLogin() { document.getElementById('loginPopup').style.display = 'block'; }
        function hideLogin() { document.getElementById('loginPopup').style.display = 'none'; }
        function showUpload() { document.getElementById('uploadPopup').style.display = 'block'; }
        function hideUpload() { document.getElementById('uploadPopup').style.display = 'none'; }
        function showAddImage() { document.getElementById('addImagePopup').style.display = 'block'; }
        function hideAddImage() { document.getElementById('addImagePopup').style.display = 'none'; }

        function changeImage(id) {
            currentImageId = id;
            document.getElementById('uploadImageId').value = id;
            showUpload();
        }

        async function updateTitle(id, newTitle) {
            const formData = new FormData();
            formData.append('action', 'update_title');
            formData.append('id', id);
            formData.append('title', newTitle);
            formData.append('csrf_token', CSRF_TOKEN);

            try {
                const response = await fetch('galerie-actions.php', { 
                    method: 'POST', 
                    body: formData 
                });
                const result = await response.json();
                if (!result.success) {
                    alert('Erreur: ' + (result.error || 'Échec de la mise à jour'));
                    location.reload();
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur de connexion');
            }
        }

        document.getElementById('uploadForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'upload_image');

            try {
                const response = await fetch('galerie-actions.php', { 
                    method: 'POST', 
                    body: formData 
                });
                const result = await response.json();

                if (result.success) {
                    const img = document.querySelector(`[data-id="${currentImageId}"] img`);
                    img.src = result.new_path + '?t=' + new Date().getTime();
                    hideUpload();
                    showMessage('uploadMessage', 'Image mise à jour avec succès', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showMessage('uploadMessage', result.error, 'error');
                }
            } catch (error) {
                showMessage('uploadMessage', 'Erreur de connexion', 'error');
            }
        });

        document.getElementById('addImageForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_image');

            try {
                const response = await fetch('galerie-actions.php', { 
                    method: 'POST', 
                    body: formData 
                });
                const result = await response.json();

                if (result.success) {
                    showMessage('addImageMessage', 'Image ajoutée avec succès', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showMessage('addImageMessage', result.error, 'error');
                }
            } catch (error) {
                showMessage('addImageMessage', 'Erreur de connexion', 'error');
            }
        });

        async function deleteImage(id) {
            if (!confirm('Supprimer cette image ?')) return;

            const formData = new FormData();
            formData.append('action', 'delete_image');
            formData.append('id', id);
            formData.append('csrf_token', CSRF_TOKEN);

            try {
                const response = await fetch('galerie-actions.php', { 
                    method: 'POST', 
                    body: formData 
                });
                const result = await response.json();

                if (result.success) {
                    document.querySelector(`[data-id="${id}"]`).remove();
                } else {
                    alert('Erreur: ' + (result.error || 'Échec de la suppression'));
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur de connexion');
            }
        }

        function showMessage(elementId, message, type) {
            const element = document.getElementById(elementId);
            element.innerHTML = `<div class="message ${type}">${message}</div>`;
            setTimeout(() => element.innerHTML = '', 3000);
        }

        window.onclick = function (event) {
            if (event.target.classList.contains('popup')) {
                event.target.style.display = 'none';
            }
        }

        <?php if (isset($loginError)): ?>
        showLogin();
        <?php endif; ?>
    </script>
</body>
</html>