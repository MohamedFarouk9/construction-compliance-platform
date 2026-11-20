<?php

use App\Kernel;

if (!file_exists(dirname(__DIR__).'/.env')) {
    if (file_exists(dirname(__DIR__).'/.env.example')) {
        $_SERVER['APP_RUNTIME_OPTIONS'] = ['dotenv_path' => '.env.example'];
    } else {
        $_SERVER['APP_RUNTIME_OPTIONS'] = ['disable_dotenv' => true];
    }
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
