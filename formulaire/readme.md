## LE HACK de l'envoie vide du formulaire résolut !

Un fucking bot envoie des formulaire vide depuis 2 mois et je n'avais encore pas capté la bonne faille. Bref la voici...

### Il désactive le js dans la console :

    document.querySelector('form').removeAttribute('onsubmit');
    // Ou dans Settings → Désactiver JavaScript

### Supprime les required :

    document.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));

### Soumet le formulaire vide :

    document.getElementById('contactForm').submit();

**Résultat : Formulaire vide accepté !**

## Leçons ...
J'utilises prepare() et execute() contre les injections SQL mais rien contre le js et c'est lui qui gère l'envoie . aie.

### La faille principale :
    if (empty($_POST['email'])) → Un espace " " n'est pas empty() en PHP !

### Solution immédiate :

    if (trim($_POST['email']) === '') {
        die("Email requis !");
    }

# Patch des scripts concernés :
    - index.html
    - script.js
    - submit.php

## Ce que le Patch change 

### Pour les vrais utilisateurs :

    Rien ne change ! Même formulaire, même expérience
    Validation légèrement amélioré (messages d'erreur plus clairs)

### Pour les bots :

    Champ "fax_number" invisible : Un bot le remplit → REDIRECTION vers ... on vas s'amuser un peut
    CSRF token : Les requêtes automatisées sont bloquées
    Vérification temps : Si < 2 secondes → BOT
    User-Agent detection : curl/python/sqlmap → BLOQUÉ
    Champs vides : Plus possible !
    Rate limiting : Max 1 soumission/30 secondes par IP

### Surveillance active :

    Toutes les tentatives sont loggées dans security.log

    Les IP suspectes sont trackées dans ip_data/

    Je peux voir exactement qui m'attaque

    im waiting...