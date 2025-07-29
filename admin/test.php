<?php
$pdo = new PDO("mysql:host=localhost;dbname=u667977963_golden_dessert;charset=utf8mb4", 
               'u667977963_berru_nico', 
               'm@bddSQL25');
var_dump($pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn());