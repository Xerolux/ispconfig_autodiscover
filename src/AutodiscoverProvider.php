<?php

namespace Autodiscover;

class AutodiscoverProvider
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
     * Parse Microsoft Autodiscover XML Request
     */
    public function parseRequest(string $xml_body): ?string
    {
        try {
            $xml = simplexml_load_string($xml_body);

            if (!$xml || !isset($xml->Request->EMailAddress)) {
                return $this->errorResponse('Invalid request format');
            }

            $email = (string)$xml->Request->EMailAddress;

            return $this->getConfiguration($email);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get configuration for email address
     */
    private function getConfiguration(string $email): string
    {
        [$username, $domain] = explode('@', $email);

        $cache_key = "autodiscover_$domain";
        $cached = $this->cache->get($cache_key);

        if ($cached) {
            return $cached;
        }

        $domain_info = $this->api->getDomainInfo($domain);

        if (!$domain_info) {
            return $this->errorResponse('Domain not found');
        }

        $config = $this->buildXML($domain, $email);
        $this->cache->set($cache_key, $config);

        return $config;
    }

    /**
     * Build Microsoft Autodiscover XML response
     */
    private function buildXML(string $domain, string $email): string
    {
        $imap_config = $this->config['mail']['imap'];
        $smtp_config = $this->config['mail']['smtp'];

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></Autodiscover>');

        $response = $xml->addChild('Response');

        // User info
        $user = $response->addChild('User');
        $user->addChild('DisplayName', $domain);
        $user->addChild('AutoDiscoverSMTPAddress', $email);

        // Account configuration
        $account = $response->addChild('Account');
        $account->addChild('AccountType', 'email');
        $account->addChild('Action', 'settings');

        // IMAP
        $protocol_imap = $account->addChild('Protocol');
        $protocol_imap->addAttribute('Type', 'IMAP');
        $this->addProtocolConfig($protocol_imap, $imap_config);

        // SMTP
        $protocol_smtp = $account->addChild('Protocol');
        $protocol_smtp->addAttribute('Type', 'SMTP');
        $this->addProtocolConfig($protocol_smtp, $smtp_config);
        $protocol_smtp->addChild('SMTPUseAuth', $smtp_config['auth_required'] ? 'on' : 'off');
        $protocol_smtp->addChild('SMTPAuthMethod', 'login');

        return $xml->asXML();
    }

    /**
     * Add protocol configuration to XML
     */
    private function addProtocolConfig(\SimpleXMLElement $protocol, array $config): void
    {
        $protocol->addChild('Server', $config['host']);
        $protocol->addChild('Port', (string)$config['port']);

        $ssl_setting = match ($config['security']) {
            'SSL' => 'SSL',
            'STARTTLS' => 'TLS',
            default => 'off',
        };

        $protocol->addChild('SSL', $ssl_setting);
        $protocol->addChild('EncryptionMethod', $ssl_setting === 'SSL' ? 'SSL' : ($ssl_setting === 'TLS' ? 'TLS' : 'None'));
        $protocol->addChild('LoginName', '%EMAILLOCALPART%');
    }

    /**
     * Error response XML
     */
    private function errorResponse(string $message): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006"></Autodiscover>');
        $response = $xml->addChild('Response');
        $error = $response->addChild('Error');
        $error->addAttribute('Time', date('c'));
        $error->addAttribute('Id', uniqid());
        $error->addChild('ErrorCode', 'InvalidRequest');
        $error->addChild('Message', $message);

        return $xml->asXML();
    }
}
