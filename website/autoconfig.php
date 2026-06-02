<?php
/**
 * Mozilla Autoconfig Endpoint
 * GET /.well-known/autoconfig/mail/config-v1.1.xml?emailaddress=user@domain.com
 */

if (!file_exists(__DIR__ . '/config.php')) {
    http_response_code(503);
    die('Configuration not found');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Autoconfig.php';

header('Content-Type: application/xml; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo '<?xml version="1.0"?><clientConfig version="1.1"><error>Method Not Allowed</error></clientConfig>';
        exit;
    }

    $email = $_GET['emailaddress'] ?? $_GET['domain'] ?? null;
    if (empty($email)) {
        http_response_code(400);
        echo '<?xml version="1.0"?><clientConfig version="1.1"><error>Missing emailaddress parameter</error></clientConfig>';
        exit;
    }

    // Extract domain
    if (strpos($email, '@') !== false) {
        [, $domain] = explode('@', $email);
    } else {
        $domain = $email;
    }

    $autoconfig = new Autoconfig();
    $response = $autoconfig->getConfig($domain);
    echo $response;

} catch (Exception $e) {
    http_response_code(500);
    if (DEBUG) error_log('Autoconfig error: ' . $e->getMessage());
    echo '<?xml version="1.0"?><clientConfig version="1.1"><error>Server error</error></clientConfig>';
}
