<?php
/**
 * Mozilla Autoconfig Handler
 */

class Autoconfig
{
    private $db;

    public function __construct()
    {
        $this->connectDB();
    }

    public function getConfig(string $domain): string
    {
        try {
            if (!$this->isDomainEnabled($domain)) {
                return $this->errorResponse('Domain not found');
            }

            return $this->buildConfig($domain);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    private function buildConfig(string $domain): string
    {
        $config = $this->getDomainConfig($domain);

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><clientConfig version="1.1"></clientConfig>');

        $domain_elem = $xml->addChild('domain');
        $domain_elem->addChild('domain', $domain);

        // IMAP
        $imap = $xml->addChild('incomingServer');
        $imap->addAttribute('type', 'imap');
        $imap->addChild('hostname', $config['imap']['host']);
        $imap->addChild('port', (string)$config['imap']['port']);
        $imap->addChild('socketType', $this->getSocketType($config['imap']['security']));
        $imap->addChild('username', '%EMAILLOCALPART%');
        $imap->addChild('authentication', 'plain');

        // POP3 (optional)
        if (!empty($GLOBALS['MAIL_CONFIG']['pop3']['enabled'])) {
            $pop3 = $GLOBALS['MAIL_CONFIG']['pop3'];
            $pop3_elem = $xml->addChild('incomingServer');
            $pop3_elem->addAttribute('type', 'pop3');
            $pop3_elem->addChild('hostname', $pop3['host']);
            $pop3_elem->addChild('port', (string)$pop3['port']);
            $pop3_elem->addChild('socketType', $this->getSocketType($pop3['security']));
            $pop3_elem->addChild('username', '%EMAILLOCALPART%');
            $pop3_elem->addChild('authentication', 'plain');
            $pop3_elem->addChild('leaveMessagesOnServer', 'true');
        }

        // SMTP
        $smtp = $xml->addChild('outgoingServer');
        $smtp->addAttribute('type', 'smtp');
        $smtp->addChild('hostname', $config['smtp']['host']);
        $smtp->addChild('port', (string)$config['smtp']['port']);
        $smtp->addChild('socketType', $this->getSocketType($config['smtp']['security']));
        $smtp->addChild('username', $config['smtp']['auth_required'] ? '%EMAILLOCALPART%' : '');
        $smtp->addChild('authentication', $config['smtp']['auth_required'] ? 'plain' : 'none');
        $smtp->addChild('addThisServer', 'true');

        // Provider
        $provider = $xml->addChild('provider');
        $provider->addAttribute('id', 'ispconfig');
        $provider->addChild('domain', $domain);

        return $xml->asXML();
    }

    private function getSocketType(string $security): string
    {
        return match ($security) {
            'SSL' => 'SSL',
            'STARTTLS' => 'STARTTLS',
            default => 'plain',
        };
    }

    private function isDomainEnabled(string $domain): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM domain WHERE domain = ? AND autodiscover = 'y'");
        $stmt->execute([$domain]);
        return $stmt->rowCount() > 0;
    }

    private function getDomainConfig(string $domain): array
    {
        global $MAIL_CONFIG;

        $stmt = $this->db->prepare("SELECT * FROM autodiscover_config WHERE domain = ? AND enabled = 'y'");
        $stmt->execute([$domain]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return [
                'imap' => [
                    'host' => $result['imap_host'],
                    'port' => $result['imap_port'],
                    'security' => $result['imap_security'],
                ],
                'smtp' => [
                    'host' => $result['smtp_host'],
                    'port' => $result['smtp_port'],
                    'security' => $result['smtp_security'],
                    'auth_required' => $result['smtp_auth_required'] === 'y',
                ],
            ];
        }

        return [
            'imap' => $MAIL_CONFIG['imap'],
            'smtp' => $MAIL_CONFIG['smtp'],
        ];
    }

    private function errorResponse(string $message): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><clientConfig version="1.1"></clientConfig>');
        $provider = $xml->addChild('provider');
        $provider->addAttribute('id', 'error');
        $provider->addChild('message', $message);
        return $xml->asXML();
    }

    private function connectDB(): void
    {
        try {
            $this->db = new PDO(
                'mysql:host=' . ISPCONFIG_DB_HOST . ';dbname=' . ISPCONFIG_DB_NAME,
                ISPCONFIG_DB_USER,
                ISPCONFIG_DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
}
