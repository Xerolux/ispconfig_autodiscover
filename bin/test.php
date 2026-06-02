#!/usr/bin/env php
<?php
/**
 * CLI Test Script für ispconfig Autodiscover Service
 *
 * Usage: php bin/test.php [autodiscover|autoconfig] [email|domain]
 */

require_once __DIR__ . '/../bootstrap.php';

$cmd = $argv[1] ?? 'help';
$param = $argv[2] ?? null;

switch ($cmd) {
    case 'autodiscover':
        testAutodiscover($param);
        break;

    case 'autoconfig':
        testAutoconfig($param);
        break;

    case 'api-test':
        testAPI();
        break;

    case 'config':
        testConfig();
        break;

    default:
        showHelp();
}

function testAutodiscover(?string $email): void
{
    if (!$email || strpos($email, '@') === false) {
        echo "Error: Email address required (user@domain.com)\n";
        exit(1);
    }

    echo "Testing Microsoft Autodiscover for: $email\n\n";

    $config = require __DIR__ . '/../config/config.php';
    $api = new \Autodiscover\IspConfigAPI($config);
    $cache = new \Autodiscover\Cache($config);
    $provider = new \Autodiscover\AutodiscoverProvider($config, $api, $cache);

    $xml_request = <<<XML
<?xml version="1.0"?>
<Autodiscover>
    <Request>
        <EMailAddress>$email</EMailAddress>
    </Request>
</Autodiscover>
XML;

    echo "Request:\n";
    echo $xml_request . "\n\n";

    echo "Response:\n";
    $response = $provider->parseRequest($xml_request);
    echo formatXML($response) . "\n";
}

function testAutoconfig(?string $domain): void
{
    if (!$domain) {
        echo "Error: Domain required (domain.com or user@domain.com)\n";
        exit(1);
    }

    if (strpos($domain, '@') !== false) {
        [, $domain] = explode('@', $domain);
    }

    echo "Testing Mozilla Autoconfig for: $domain\n\n";

    $config = require __DIR__ . '/../config/config.php';
    $api = new \Autodiscover\IspConfigAPI($config);
    $cache = new \Autodiscover\Cache($config);
    $provider = new \Autodiscover\AutoconfigProvider($config, $api, $cache);

    echo "Response:\n";
    $response = $provider->getConfiguration($domain);
    echo formatXML($response) . "\n";
}

function testAPI(): void
{
    echo "Testing ispconfig API Connection...\n\n";

    $config = require __DIR__ . '/../config/config.php';
    $api = new \Autodiscover\IspConfigAPI($config);

    echo "API URL: " . $config['ispconfig']['api_url'] . "\n";
    echo "Username: " . $config['ispconfig']['api_username'] . "\n";

    if ($api->login()) {
        echo "✓ Login erfolgreich!\n\n";

        $server_info = $api->getServerInfo();
        if ($server_info) {
            echo "Server Info:\n";
            echo json_encode($server_info, JSON_PRETTY_PRINT) . "\n";
        }

        $api->logout();
    } else {
        echo "✗ Login fehlgeschlagen!\n";
        echo "  Bitte Credentials in config.php prüfen.\n";
        exit(1);
    }
}

function testConfig(): void
{
    echo "Checking Configuration...\n\n";

    $required_files = [
        'config/config.php',
        'bootstrap.php',
        'src/AutodiscoverProvider.php',
        'src/AutoconfigProvider.php',
        'src/IspConfigAPI.php',
        'src/Cache.php',
    ];

    foreach ($required_files as $file) {
        $path = __DIR__ . '/../' . $file;
        $exists = file_exists($path) ? '✓' : '✗';
        echo "$exists $file\n";
    }

    echo "\nDirectories:\n";
    $dirs = ['cache', 'logs', 'config'];
    foreach ($dirs as $dir) {
        $path = __DIR__ . '/../' . $dir;
        $writable = is_writable($path) ? '✓ writable' : '✗ not writable';
        $exists = is_dir($path) ? '✓ exists' : '✗ missing';
        echo "  $dir: $exists, $writable\n";
    }

    echo "\nPHP Extensions:\n";
    $extensions = ['curl', 'simplexml', 'json'];
    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext) ? '✓' : '✗';
        echo "  $loaded $ext\n";
    }

    echo "\nPHP Version: " . phpversion() . "\n";
}

function formatXML(string $xml): string
{
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    if (@$dom->loadXML($xml)) {
        return $dom->saveXML();
    }

    return $xml;
}

function showHelp(): void
{
    echo <<<HELP
ispconfig Autodiscover Service - Test Tool

Usage:
  php bin/test.php <command> [parameter]

Commands:
  autodiscover <email>     Test Autodiscover (z.B. user@domain.com)
  autoconfig <domain>      Test Autoconfig (z.B. domain.com oder user@domain.com)
  api-test                 Test ispconfig API connection
  config                   Check configuration & system

Examples:
  php bin/test.php autodiscover user@example.com
  php bin/test.php autoconfig example.com
  php bin/test.php api-test
  php bin/test.php config

HELP;
    exit(0);
}
