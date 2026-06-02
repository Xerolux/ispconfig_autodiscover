<?php
/**
 * Mozilla Autoconfig Endpoint
 *
 * GET /.well-known/autoconfig/mail/config-v1.1.xml?emailaddress=user@domain.com
 */

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo '<?xml version="1.0"?><error>Method Not Allowed</error>';
        exit;
    }

    $email = $_GET['emailaddress'] ?? $_GET['domain'] ?? null;

    if (empty($email)) {
        http_response_code(400);
        echo '<?xml version="1.0"?><clientConfig version="1.1"><error>Missing emailaddress parameter</error></clientConfig>';
        exit;
    }

    // Extract domain from email or use as-is if it's a domain
    if (strpos($email, '@') !== false) {
        [, $domain] = explode('@', $email);
    } else {
        $domain = $email;
    }

    $config = require_once __DIR__ . '/../config/config.php';
    $api = new \Autodiscover\IspConfigAPI($config);
    $cache = new \Autodiscover\Cache($config);
    $provider = new \Autodiscover\AutoconfigProvider($config, $api, $cache);

    $response = $provider->getConfiguration($domain);

    if ($config['debug']) {
        error_log("Autoconfig request for domain: $domain");
    }

    echo $response;

} catch (\Exception $e) {
    http_response_code(500);
    echo '<?xml version="1.0"?><clientConfig version="1.1"><error>' . htmlspecialchars($e->getMessage()) . '</error></clientConfig>';

    if ($config['debug']) {
        error_log("Autoconfig error: " . $e->getMessage());
    }
}
