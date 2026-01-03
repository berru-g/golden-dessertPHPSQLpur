<?php
// ============================================
// SYSTEME ANTI-BOT GOLDEN DESSERT v2.0
// ============================================

// Démarrer la session pour le CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================== CONFIGURATION ====================
$HONEYPOT_URL = "https://www.google.com/search?q=comment+arrêter+de+spammer+les+sites+web";
$BLOCK_DURATION = 3600; // 1 heure
$MAX_ATTEMPTS = 3;

// ==================== FONCTIONS DE LOG ====================
function log_attack($type, $details) {
    $log = date('[Y-m-d H:i:s]') . " | TYPE: $type | IP: " . $_SERVER['REMOTE_ADDR'];
    $log .= " | UA: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu');
    $log .= " | DETAILS: " . json_encode($details) . "\n";
    file_put_contents('security.log', $log, FILE_APPEND);
}

// ==================== DÉTECTION BOT ====================
$is_bot = false;
$bot_evidence = [];

// 1. Vérification honeypot 1 (champ fax_number)
if (!empty($_POST['fax_number'])) {
    $is_bot = true;
    $bot_evidence[] = "HONEYPOT_1_triggered";
}

// 2. Vérification honeypot 2 (checkbox human_check)
if (isset($_POST['human_check']) && $_POST['human_check'] === '1') {
    $is_bot = true;
    $bot_evidence[] = "HONEYPOT_2_triggered";
}

// 3. Vérification CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $is_bot = true;
    $bot_evidence[] = "CSRF_failed";
}

// 4. Vérification temps de remplissage
if (isset($_POST['form_load_time'])) {
    $load_time = intval($_POST['form_load_time']);
    $submit_time = time();
    $time_spent = $submit_time - $load_time;
    
    if ($time_spent < 2) { // Moins de 2 secondes = bot
        $is_bot = true;
        $bot_evidence[] = "too_fast_" . $time_spent . "s";
    }
}

// 5. Vérification user-agent des bots connus
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$bot_patterns = ['curl', 'python', 'wget', 'libwww', 'perl', 'java', 
                 'sqlmap', 'nikto', 'scanner', 'bot', 'crawler', 'spider'];
foreach ($bot_patterns as $pattern) {
    if (stripos($user_agent, $pattern) !== false) {
        $is_bot = true;
        $bot_evidence[] = "UA_pattern_" . $pattern;
        break;
    }
}

// 6. Vérification des champs vides (ton problème original)
$required_fields = ['email', 'fullname', 'message'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        $is_bot = true;
        $bot_evidence[] = "empty_field_" . $field;
    }
}

// ==================== ACTION SI BOT DÉTECTÉ ====================
if ($is_bot) {
    // Log l'attaque
    log_attack("BOT_DETECTED", [
        'evidence' => $bot_evidence,
        'post_data' => $_POST,
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    // Envoie le bot dans le piège
    header("Location: " . $HONEYPOT_URL);
    exit();
}

// ==================== TRAITEMENT HUMAIN ====================
// Si on arrive ici, c'est probablement un humain

// Vérification du taux limite (rate limiting)
$ip = $_SERVER['REMOTE_ADDR'];
$ip_hash = md5($ip);
$ip_file = "ip_data/$ip_hash.json";

if (!is_dir('ip_data')) {
    mkdir('ip_data', 0755, true);
}

if (file_exists($ip_file)) {
    $ip_data = json_decode(file_get_contents($ip_file), true);
    $last_time = $ip_data['last_submit'] ?? 0;
    
    if ((time() - $last_time) < 30) { // 30 secondes entre chaque soumission
        log_attack("RATE_LIMIT", ['ip' => $ip]);
        die("Veuillez patienter 30 secondes entre chaque demande.");
    }
}

// ==================== VALIDATION DES DONNÉES ====================
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
if (!$email) {
    die("Veuillez fournir une adresse email valide.");
}

$fullname = htmlspecialchars(trim($_POST['fullname']), ENT_QUOTES, 'UTF-8');
if (strlen($fullname) < 2 || strlen($fullname) > 100) {
    die("Le nom doit contenir entre 2 et 100 caractères.");
}

$telephone = isset($_POST['telephone']) ? preg_replace('/[^0-9+]/', '', $_POST['telephone']) : '';
$siteweb = isset($_POST['siteweb']) ? filter_var(trim($_POST['siteweb']), FILTER_VALIDATE_URL) : '';

$message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');
if (strlen($message) < 10) {
    die("Le message doit contenir au moins 10 caractères.");
}

// ==================== TRAITEMENT NORMAL (TON CODE ORIGINAL) ====================
$env = parse_ini_file(__DIR__.'/.env');

try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset={$env['DB_CHARSET']}",
        $env['DB_USER'],
        $env['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Enregistre la soumission
    $stmt = $pdo->prepare("INSERT INTO contacts (email, fullname, telephone, siteweb, message, ip_address, user_agent, is_human, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $email, 
        $fullname, 
        $telephone, 
        $siteweb, 
        $message,
        $ip,
        $user_agent,
        1 // is_human = true
    ]);

    // Met à jour le fichier IP
    $ip_data = [
        'last_submit' => time(),
        'attempts' => 0,
        'last_success' => date('Y-m-d H:i:s')
    ];
    file_put_contents($ip_file, json_encode($ip_data));

    // ==================== EMAIL (ton code original) ====================
    $subject = "Nouveau message depuis le site Golden Dessert";
    $message_content = "
Vous avez reçu un nouveau message :

Nom complet : $fullname
Email : $email
Téléphone : $telephone
Site Internet : $siteweb

Message :
$message

---

Ce message a été enregistré dans votre base de données.
";

    $headers = "From: {$env['MAIL_FROM']}\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

    mail($env['MAIL_TO'], $subject, $message_content, $headers);

    // Redirection vers la page de confirmation
    header("Location: merci.html");
    exit();

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: erreur.html");
    exit();
}

// Régénère le token CSRF pour la prochaine fois
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>