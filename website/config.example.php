<?php
/**
 * ispconfig Autodiscover Module - Website Configuration
 *
 * Kopiere diese Datei zu config.php und passe an
 */

// ispconfig Database
define('ISPCONFIG_DB_HOST', 'localhost');
define('ISPCONFIG_DB_USER', 'dbispconfig');
define('ISPCONFIG_DB_PASS', 'dbpassword');
define('ISPCONFIG_DB_NAME', 'dbispconfig');

// Mail Server Settings
$MAIL_CONFIG = [
    'imap' => [
        'host' => 'mail.example.com',
        'port' => 993,
        'security' => 'SSL', // SSL, STARTTLS, NONE
    ],
    'smtp' => [
        'host' => 'mail.example.com',
        'port' => 465,
        'security' => 'SSL', // SSL, STARTTLS, NONE
        'auth_required' => true,
    ],
    'pop3' => [
        'enabled' => false,
        'host' => 'mail.example.com',
        'port' => 995,
        'security' => 'SSL',
    ],
];

// Caching (für Performance)
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600); // 1 hour
define('CACHE_DIR', __DIR__ . '/cache');

// Debug Mode
define('DEBUG', false);
