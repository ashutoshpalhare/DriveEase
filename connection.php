<?php

function Connect()
{
    $config = [];
    $localConfigPath = __DIR__ . '/config/config.local.php';

    if (is_file($localConfigPath)) {
        $localConfig = require $localConfigPath;

        if (is_array($localConfig)) {
            $config = $localConfig;
        }
    }

    $readSetting = function ($key, $environmentVariable, $default = null) use ($config) {
        $environmentValue = getenv($environmentVariable);

        if ($environmentValue !== false) {
            return $environmentValue;
        }

        if (array_key_exists($key, $config) && is_string($config[$key])) {
            return $config[$key];
        }

        return $default;
    };

    $dbhost = $readSetting('host', 'DB_HOST', 'localhost');
    $dbuser = $readSetting('user', 'DB_USER');
    $dbpass = $readSetting('password', 'DB_PASSWORD');
    $dbname = $readSetting('name', 'DB_NAME', 'carrental');

    if ($dbuser === null || $dbpass === null) {
        throw new RuntimeException(
            'Database credentials are missing. Set DB_USER and DB_PASSWORD or create config/config.local.php from config/config.local.example.php.'
        );
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
    $conn->set_charset('utf8mb4');

    return $conn;
}
