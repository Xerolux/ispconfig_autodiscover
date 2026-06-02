<?php

namespace Autodiscover;

class AutoconfigProvider
{
    private $config;
    private $api;
    private $cache;

    public function __construct(array $config, IspConfigAPI $api, Cache $cache)
    {
        $this->config = $config;
        $this->api = $api;
        $this->cache = $cache;
    }

    /**
     * Get Mozilla Autoconfig XML
     */
    public function getConfiguration(string $domain): string
    {
        $cache_key = "autoconfig_$domain";
        $cached = $this->cache->get($cache_key);

        if ($cached) {
            return $cached;
        }

        $domain_info = $this->api->getDomainInfo($domain);

        if (!$domain_info) {
            return $this->errorResponse('Domain not found');
        }

        $config = $this->buildXML($domain);
        $this->cache->set($cache_key, $config);

        return $config;
    }

    /**
     * Build Mozilla Autoconfig XML response
     */
    private function buildXML(string $domain): string
    {
        $imap_config = $this->config['mail']['imap'];
        $smtp_config = $this->config['mail']['smtp'];

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><clientConfig version="1.1"></clientConfig>');

        $domain_elem = $xml->addChild('domain');
        $domain_elem->addChild('domain', $domain);

        // Hostname-based lookup
        $hostname = $xml->addChild('hostname');
        $hostname->addAttribute('type', 'imap');
        $hostname->addChild('hostname', $imap_config['host']);

        // IMAP Configuration
        $imap_server = $xml->addChild('incomingServer');
        $imap_server->addAttribute('type', 'imap');
        $imap_server->addChild('hostname', $imap_config['host']);
        $imap_server->addChild('port', (string)$imap_config['port']);
        $imap_server->addChild('socketType', $this->getSocketType($imap_config['security']));
        $imap_server->addChild('username', '%EMAILLOCALPART%');
        $imap_server->addChild('authentication', 'plain');

        // POP3 (optional)
        if (!empty($this->config['mail']['pop3']['enabled'])) {
            $pop3_config = $this->config['mail']['pop3'];
            $pop3_server = $xml->addChild('incomingServer');
            $pop3_server->addAttribute('type', 'pop3');
            $pop3_server->addChild('hostname', $pop3_config['host']);
            $pop3_server->addChild('port', (string)$pop3_config['port']);
            $pop3_server->addChild('socketType', $this->getSocketType($pop3_config['security']));
            $pop3_server->addChild('username', '%EMAILLOCALPART%');
            $pop3_server->addChild('authentication', 'plain');
            $pop3_server->addChild('leaveMessagesOnServer', 'true');
        }

        // SMTP Configuration
        $smtp_server = $xml->addChild('outgoingServer');
        $smtp_server->addAttribute('type', 'smtp');
        $smtp_server->addChild('hostname', $smtp_config['host']);
        $smtp_server->addChild('port', (string)$smtp_config['port']);
        $smtp_server->addChild('socketType', $this->getSocketType($smtp_config['security']));
        $smtp_server->addChild('username', $smtp_config['auth_required'] ? '%EMAILLOCALPART%' : '');
        $smtp_server->addChild('authentication', $smtp_config['auth_required'] ? 'plain' : 'none');
        $smtp_server->addChild('addThisServer', 'true');
        $smtp_server->addChild('useGlobalPreferredServer', 'false');

        // Provider info
        $provider = $xml->addChild('provider');
        $provider->addAttribute('id', 'ispconfig');
        $provider->addChild('domain', $domain);
        $provider->addChild('displayName', ucfirst(explode('.', $domain)[0]));

        return $xml->asXML();
    }

    /**
     * Convert security type to socket type
     */
    private function getSocketType(string $security): string
    {
        return match ($security) {
            'SSL' => 'SSL',
            'STARTTLS' => 'STARTTLS',
            default => 'plain',
        };
    }

    /**
     * Error response XML
     */
    private function errorResponse(string $message): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><clientConfig version="1.1"></clientConfig>');
        $provider = $xml->addChild('provider');
        $provider->addAttribute('id', 'error');
        $provider->addChild('message', $message);

        return $xml->asXML();
    }
}
