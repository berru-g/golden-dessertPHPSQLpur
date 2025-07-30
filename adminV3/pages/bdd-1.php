<?php 
$title = "Base de données 1";
require __DIR__.'/../includes/header.php';

// Récupérer les données spécifiques à cette base
$messages = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();
$unread_count = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
$total_messages = count($messages);
?>

<div class="content-body">
    <div class="table-container">
        <table id="messagesTable">
            <!-- Table content same as before -->
            <thead>
                <tr>
                    <th data-column="0">Date <i class="fas fa-sort"></i></th>
                    <th data-column="1">Nom <i class="fas fa-sort"></i></th>
                    <th data-column="2">Mail</th>
                    <th data-column="4">Tél</th>
                    <th data-column="5">URL <i class="fas fa-sort"></i></th>
                    <th data-column="6">Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                <tr class="message-row <?= $msg['is_read'] ? '' : 'unread' ?>" 
                    data-id="<?= $msg['id'] ?>"
                    data-read="<?= $msg['is_read'] ?>"
                    data-fullmessage="<?= htmlspecialchars($msg['message']) ?>">
                    <!-- Rest of the table rows same as before -->
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__.'/../includes/footer.php'; ?>