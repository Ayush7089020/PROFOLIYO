<?php
// db_connect.php

require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=sql300.epizy.com;dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>