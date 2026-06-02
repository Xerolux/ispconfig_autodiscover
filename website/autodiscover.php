<?php
/**
 * Microsoft Autodiscover Endpoint
 * POST /autodiscover/autodiscover.xml
 */

if (!file_exists(__DIR__ . '/config.php')) {
    http_response_code(503);
    die('Configuration not found');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Autodiscover.php';

header('Content-Type: application/xml; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo '<?xml version="1.0"?><error>Method Not Allowed</error>';
        exit;
    }

    $xml_input = file_get_contents('php://input');
    if (empty($xml_input)) {
        http_response_code(400);
        echo '<?xml version="1.0"?><error>Empty request</error>';
        exit;
    }

    $autodiscover = new Autodiscover();
    $response = $autodiscover->handleRequest($xml_input);
    echo $response;

} catch (Exception $e) {
    http_response_code(500);
    if (DEBUG) error_log('Autodiscover error: ' . $e->getMessage());
    echo '<?xml version="1.0"?><Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006"><Response><Error><ErrorCode>ServerError</ErrorCode></Error></Response>';
}
