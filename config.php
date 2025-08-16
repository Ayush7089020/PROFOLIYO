<?php
// config.php

require_once 'load_env.php';
loadEnv(__DIR__ . '/../.env');

// ✅ अब सारे ज़रूरी keys चेक कर रहे हैं including DB config
$requiredKeys = [
    'GOOGLE_CLIENT_ID',
    'GOOGLE_CLIENT_SECRET',
    'GOOGLE_REDIRECT_URI',
    'DOMAIN',
    'SECRET_KEY',
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASS',
    'EMAIL',
    'RECAPTCHA_SECRET',
    'APP_PASSWORD'
];

foreach ($requiredKeys as $key) {
    if (!isset($_ENV[$key])) {
        die("Missing required env key: $key");
    }
}

// 🔐 Constants define कर दिए
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID']);
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET']);
define('GOOGLE_REDIRECT_URI', $_ENV['GOOGLE_REDIRECT_URI']);
define('DOMAIN', $_ENV['DOMAIN']);
define('SECRET_KEY', $_ENV['SECRET_KEY']);
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('EMAIL', $_ENV['EMAIL']);
define('RECAPTCHA_SECRET', $_ENV['RECAPTCHA_SECRET']);
define('APP_PASSWORD', $_ENV['APP_PASSWORD']);
