<?php
/**
 * Microsoft Autodiscover Handler
 */

class Autodiscover
{
    private $db;
    private $domain;
    private $email;

    public function __construct()
    {
        $this->connectDB();
    }

    public function handleRequest(string $xml_input): string
    {
        try {
            $xml = simplexml_load_string($xml_input);
            if (!$xml || !isset($xml->Request->EMailAddress)) {
                return $this->errorResponse('Invalid request');
            }

            $this->email = (string)$xml->Request->EMailAddress;
            [, $this->domain] = explode('@', $this->email);

            if (!$this->isDomainEnabled()) {
                return $this->errorResponse('Domain not found');
            }

            return $this->buildResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    private function buildResponse(): string
    {
        $config = $this->getDomainConfig();

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006"></Autodiscover>');
        $response = $xml->addChild('Response');
        $account = $response->addChild('Account');
        $account->addChild('AccountType', 'email');
        $account->addChild('Action', 'settings');

        // IMAP
        $imap = $account->addChild('Protocol');
        $imap->addAttribute('Type', 'IMAP');
        $this->addProtocol($imap, $config['imap']);

        // SMTP
        $smtp = $account->addChild('Protocol');
        $smtp->addAttribute('Type', 'SMTP');
        $this->addProtocol($smtp, $config['smtp']);
        $smtp->addChild('SMTPUseAuth', $config['smtp']['auth_required'] ? 'on' : 'off');
        $smtp->addChild('SMTPAuthMethod', 'login');

        return $xml->asXML();
    }

    private function addProtocol(&$protocol, array $config): void
    {
        $protocol->addChild('Server', $config['host']);
        $protocol->addChild('Port', (string)$config['port']);

        $ssl_setting = match ($config['security']) {
            'SSL' => 'SSL',
            'STARTTLS' => 'TLS',
            default => 'off',
        };

        $protocol->addChild('SSL', $ssl_setting);
        $protocol->addChild('LoginName', '%EMAILLOCALPART%');
    }

    private function isDomainEnabled(): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM domain WHERE domain = ? AND autodiscover = 'y'");
        $stmt->execute([$this->domain]);
        return $stmt->rowCount() > 0;
    }

    private function getDomainConfig(): array
    {
        global $MAIL_CONFIG;

        $stmt = $this->db->prepare("SELECT * FROM autodiscover_config WHERE domain = ? AND enabled = 'y'");
        $stmt->execute([$this->domain]);
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
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006"></Autodiscover>');
        $response = $xml->addChild('Response');
        $error = $response->addChild('Error');
        $error->addAttribute('Time', date('c'));
        $error->addChild('ErrorCode', 'InvalidRequest');
        $error->addChild('Message', $message);
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
