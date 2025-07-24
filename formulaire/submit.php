<?php

$env = parse_ini_file(__DIR__.'/.env');

// Config
$dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset={$env['DB_CHARSET']}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], $options);

    $email = $_POST['email'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $siteweb = $_POST['siteweb'] ?? '';
    $messageContent = $_POST['message'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO contacts (email, fullname, telephone, siteweb, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$email, $fullname, $telephone, $siteweb, $messageContent]);

    // ---------- Envoi de l'email au client ----------
    $subject = "Nouveau message depuis le site Golden Dessert";
    $message = "
Vous avez reçu un nouveau message :

Nom complet : $fullname
Email : $email
Téléphone : $telephone
Site Internet : $siteweb

Message :
$messageContent

---

Ce message a été enregistré dans votre base de données.
";

    $headers = "From: {$env['MAIL_FROM']}\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

    mail($env['MAIL_TO'], $subject, $message, $headers);

    // Redirection vers la page de confirmation
    header("Location: merci.html");
    exit();

} catch (PDOException $e) {
    // En production, tu pourrais logger cette erreur plutôt que l'afficher
    error_log("Database error: " . $e->getMessage());
    header("Location: erreur.html");
    exit();
}
?>