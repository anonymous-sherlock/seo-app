<?php
require_once __DIR__ . '/vendor/autoload.php';

spl_autoload_register(function ($className) {
    $className = ltrim($className, '\\');
    $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . $fileName . '.php';

    if (file_exists($filePath)) {
        require_once $filePath;
    }
});
