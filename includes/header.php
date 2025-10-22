<?php
//session_start();
define('BASE_URL', 'http://localhost/golden-dessertPHPSQLpur/');
define('SITE_NAME', 'Golden dessert');

// Détecte les tentatives d'injection dans les URLs
if (preg_match('/[\'"]|(--)|(\/\*)|(\\\\)/i', $_SERVER['QUERY_STRING'])) {
    header("HTTP/1.1 403 Forbidden");
    error_log("Tentative d'injection détectée: ".$_SERVER['REQUEST_URI']);
    die('Requête suspecte bloquée');
}
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
    <!-- TWITTER CARD -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Golden Dessert | Desserts premium pour restaurateurs" />
    <meta name="twitter:description"
        content="Découvrez notre catalogue de desserts haut de gamme dédiés aux professionnels de la restauration." />
    <meta name="twitter:image" content="https://goldendessert.fr/img/golden-dessert-logo.png" />
    <meta name="twitter:site" content="@GoldenDessert" />
    <!-- JSON-LD SCHEMA ORG (SEO STRUCTURÉ) -->
    <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Golden Dessert",
  "image": "https://goldendessert.fr/img/golden-dessert-logo.png",
  "@id": "https://goldendessert.fr/",
  "url": "https://goldendessert.fr/",
  "telephone": "+33678442807",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "5 rue de la Petite Meilleraie",
    "addressLocality": "Les Sorinières",
    "postalCode": "44840",
    "addressCountry": "FR"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": 47.150,
    "longitude": -1.525
  },
  "openingHoursSpecification": [{
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": [
      "Tuesday",
      "Wednesday",
      "Thursday",
      "Friday",
      "Saturday"
    ],
    "opens": "10:00",
    "closes": "19:00"
  }],
  "sameAs": [
    "https://www.instagram.com/golden_dessert44/",
    "https://www.facebook.com/goldendessert"
  ]
}
</script>
    <link rel="stylesheet" href="<?= BASE_URL ?>style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/three@0.155.0/examples/js/loaders/GLTFLoader.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600;800&family=Playfair+Display:ital,wght@1,600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="<?= BASE_URL ?>script.js"></script>
</head>
<body>
    <div class="loader">
        <img src="<?= BASE_URL ?>golden-dessert-logo.png" alt="Golden Dessert" />
    </div>

    <nav>
        <div class="logo-container">
            <img src="<?= BASE_URL ?>golden-dessert-logo.png" alt="LogoGD" class="logo-img" />
        </div>
        <div class="hamburger">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>
        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>histoire.html">HISTOIRE</a></li>
            <li><a href="<?= BASE_URL ?>#">GALERIE</a></li>

            <li><a href="<?= BASE_URL ?>formulaire/index.html">CONTACT</a></li>
            <!--<li><a href="./admin/login.php"><i class="fa-solid fa-utensils"></i></a></li>-->
        </ul>
    </nav>
