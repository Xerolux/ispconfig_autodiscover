<?php
/**
 * Microsoft Autodiscover Endpoint
 *
 * POST /autodiscover/autodiscover.xml
 * Content-Type: application/xml
 */

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo '<?xml version="1.0"?><error>Method Not Allowed</error>';
        exit;
    }

    $xml_body = file_get_contents('php://input');

    if (empty($xml_body)) {
        http_response_code(400);
        echo '<?xml version="1.0"?><error>Empty request body</error>';
        exit;
    }

    $config = require_once __DIR__ . '/../config/config.php';
    $api = new \Autodiscover\IspConfigAPI($config);
    $cache = new \Autodiscover\Cache($config);
    $provider = new \Autodiscover\AutodiscoverProvider($config, $api, $cache);

    $response = $provider->parseRequest($xml_body);

    if ($config['debug']) {
        error_log("Autodiscover request processed");
    }

    echo $response;

} catch (\Exception $e) {
    http_response_code(500);
    echo '<?xml version="1.0"?><Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006"><Response><Error><ErrorCode>ServerError</ErrorCode><Message>' . htmlspecialchars($e->getMessage()) . '</Message></Error></Response>';

    if ($config['debug']) {
        error_log("Autodiscover error: " . $e->getMessage());
    }
}
