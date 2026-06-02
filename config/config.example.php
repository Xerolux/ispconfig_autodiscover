<?php
/**
 * ispconfig Autodiscover Service - Configuration
 *
 * Kopiere diese Datei zu config.php und passe die Werte an.
 */

return [
    // ispconfig API Settings
    'ispconfig' => [
        'api_url' => 'https://your-ispconfig-server:8080/api/index.php',
        'api_username' => 'admin',
        'api_password' => 'admin_password',
        'verify_ssl' => true, // false nur für Development!
    ],

    // Mail Server Settings (Postfix + Dovecot)
    'mail' => [
        'imap' => [
            'host' => 'mail.example.com',
            'port' => 993,
            'security' => 'SSL', // 'SSL', 'STARTTLS', oder 'NONE'
        ],
        'smtp' => [
            'host' => 'mail.example.com',
            'port' => 465,
            'security' => 'SSL', // 'SSL', 'STARTTLS', oder 'NONE'
            'auth_required' => true,
        ],
        'pop3' => [
            'enabled' => false,
            'host' => 'mail.example.com',
            'port' => 995,
            'security' => 'SSL',
        ],
    ],

    // Caching
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour in seconds
        'path' => __DIR__ . '/../cache',
    ],

    // Logging
    'logging' => [
        'enabled' => true,
        'level' => 'info', // 'debug', 'info', 'warning', 'error'
        'path' => __DIR__ . '/../logs',
    ],

    // Feature Flags - pro Domain aktivierbar
    'features' => [
        'autodiscover' => true,
        'autoconfig' => true,
        'alternative_servers' => false, // Fallback-Server anbieten
        'require_authentication' => false, // Auth für Autodiscover verlangen
    ],

    // Debug Mode
    'debug' => false,

    // Allowed Domains (leer = alle Domains erlaubt)
    'allowed_domains' => [],
];
