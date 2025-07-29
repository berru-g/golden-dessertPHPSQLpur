<?php
session_start();
$_SESSION['admin_logged_in'] = true;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test</title>
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <h1>Test CSS</h1>
    <?php var_dump($_SESSION); ?>
</body>
</html>