<?php
session_start();

// Charge la config
$config = require __DIR__ . '/config.php';

// Initialise le compteur de tentatives si inexistant
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

// Vérifie si l'utilisateur est bloqué
$block_duration = 1800; // 30 minutes en secondes
if ($_SESSION['login_attempts'] >= 3 && (time() - $_SESSION['last_attempt_time']) < $block_duration) {
    $remaining_time = $block_duration - (time() - $_SESSION['last_attempt_time']);
    $error = "Trop de tentatives. Réessayez dans ".gmdate("i\m s\s", $remaining_time);
} 
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Réinitialise le compteur si le délai est expiré
    if ((time() - $_SESSION['last_attempt_time']) > $block_duration) {
        $_SESSION['login_attempts'] = 0;
    }

    if (password_verify($_POST['password'], $config['admin_password_hash'])) {
        // Réussite : réinitialise le compteur
        $_SESSION['login_attempts'] = 0;
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit();
    } else {
        // Échec : incrémente le compteur
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $remaining_attempts = 3 - $_SESSION['login_attempts'];
        
        $error = "Mot de passe incorrect. ";
        $error .= ($remaining_attempts > 0) 
            ? "Il vous reste $remaining_attempts tentative(s)." 
            : "Accès bloqué pour 30 minutes.";
        
        sleep(2); // Ralentit les attaques brute-force
    }
}
?>
<!--// à cacher via la lib PHP dotenv ou à la main dans O2switch... 
// Configuration > Variables d'environnement > variable "ADMIN_PASSWORD=mdp"
// $mdp_admin = getenv('ADMIN_PASSWORD');
// + hash
// sleep(2); // Ralentit les attaques brute-force. Penser à limiter les tentatives avec session ou blocage IP après X essais-->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
            flex-direction: column;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        h1 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }
        
        .logo {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 2rem;
            display: block;
        }
        
        form {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
        }
        
        h2 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            margin: 0.5rem 0 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        
        button:hover {
            background: var(--secondary);
            transform: translateY(-1px);
        }
        
        .error {
            color: var(--danger);
            margin: 1rem 0;
            font-size: 0.9rem;
        }
        
        .footer {
            margin-top: 3rem;
            text-align: right;
            width: 100%;
            max-width: 400px;
        }
        
        .footer a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .footer a:hover {
            color: var(--primary);
        }
        
        @media (max-width: 480px) {
            form {
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .logo {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Interface Admin</h1>
        <i class="fas fa-shield-alt logo"></i>
        
        <form method="POST">
            <h2>Connexion</h2>
            <input type="password" name="password" placeholder="Mot de passe admin" required />
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <button type="submit">Se connecter</button>
        </form>
    </div>
    
    <div class="footer">
        <a href="https://gael-berru.netlify.app#contact" rel="noopener" target="_blank">
            <span>Interface développée par berru-g</span>
            <i class="fas fa-headset"></i>
        </a>
    </div>
</body>
</html>
