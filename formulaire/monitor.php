<?php
// surveillance.php
$logFile = 'attaques_detaillees.log';
$ip = $_SERVER['REMOTE_ADDR'];
$data = [
    'date' => date('Y-m-d H:i:s'),
    'ip' => $ip,
    'method' => $_SERVER['REQUEST_METHOD'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu',
    'referer' => $_SERVER['HTTP_REFERER'] ?? 'Direct',
    'post_data' => $_POST,
    'get_data' => $_GET
];

$logEntry = json_encode($data, JSON_PRETTY_PRINT) . "\n---\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Trouve la géolocalisation de l'IP
$location = @json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
if ($location && $location['status'] === 'success') {
    file_put_contents('ips_geoloc.log', 
        "$ip | {$location['city']}, {$location['country']} | {$location['isp']}\n", 
        FILE_APPEND);
}
?>