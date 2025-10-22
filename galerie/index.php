<?php
session_start();
require_once '../admin/figue.php';
require_once '../includes/header.php';

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

<style>
    .admin-bar {
        position: relative;
        width: 100%;
        padding: 0.5rem 2%;
        display: flex;
        justify-content: space-evenly;
        align-items: center;
        margin-top: 0rem;
    }

    .admin-controls {
        position: absolute;
        top: 10px;
        right: 10px;
        display: flex;
        gap: 5px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .gallery-item:hover .admin-controls {
        opacity: 1;
    }

    .edit-btn, .delete-btn {
        border: none;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .edit-btn {
        background: #c9a769;
    }

    .delete-btn {
        background: #e74c3c;
    }

    .edit-title-input {
        width: 100%;
        padding: 8px;
        border: 1px solid #444;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.9);
        text-align: center;
        font-size: 14px;
    }

    .popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 1000;
    }

    .popup-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #1a1a1a;
        padding: 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 400px;
        color: white;
    }

    .popup h3 {
        color: #c9a769;
        margin-bottom: 20px;
        text-align: center;
    }

    .popup input,
    .popup button {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: 1px solid #444;
        border-radius: 6px;
        background: #2a2a2a;
        color: white;
    }

    .popup button {
        background: #c9a769;
        border: none;
        cursor: pointer;
        font-weight: 600;
    }

    .message {
        padding: 10px;
        margin: 10px 0;
        border-radius: 4px;
        text-align: center;
    }

    .success {
        background: #27ae60;
        color: white;
    }

    .error {
        background: #e74c3c;
        color: white;
    }
</style>

<body>

    <div class="admin-bar">
        <?php if (!$isAdmin): ?>
            <button class="btn" onclick="showLogin()">
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