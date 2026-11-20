<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$envFile = dirname(__DIR__).'/.env';
if (!file_exists($envFile)) {
    $envFile = dirname(__DIR__).'/.env.example';
}

if (file_exists($envFile) && method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv($envFile);
}

if ($_SERVER['APP_DEBUG'] ?? false) {
    umask(0000);
}
