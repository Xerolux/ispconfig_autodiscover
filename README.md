# ispconfig Autodiscover Module

Ein modernes ispconfig Module für automatische Mail-Client Konfiguration.

## Features

- ✅ **Microsoft Autodiscover** - Outlook, Evolution, etc.
- ✅ **Mozilla Autoconfig** - Thunderbird, K-9 Mail, etc.
- ✅ **ispconfig Integration** - Direkt in ispconfig Admin Panel
- ✅ **Per-Domain Aktivierung** - Checkbox pro Domain
- ✅ **Postfix + Dovecot** - Mail-Server Support
- ✅ **Updates-proof** - Separate Installation

## Installation

```bash
# 1. Dateirechte setzen
chown -R ispconfig:ispconfig interface
chmod -R 750 interface

# 2. Kopieren nach ispconfig
cp -prf interface /usr/local/ispconfig

# 3. Website kopieren
mkdir -p /var/www/autodiscover
cp -prf website /var/www/autodiscover

# 4. Konfigurieren
cp /var/www/autodiscover/website/config.example.php /var/www/autodiscover/website/config.php
nano /var/www/autodiscover/website/config.php

# 5. Web-Server konfigurieren
chown -R www-data:www-data /var/www/autodiscover
chmod 755 /var/www/autodiscover
```

## Verwendung

1. ispconfig Admin Panel >> **Tools** >> **Autodiscover**
2. Pro Domain: Häkchen setzen zum Aktivieren
3. Mail-Clients erkennen Konfiguration automatisch

## Endpoints

- `https://domain.com/autodiscover/autodiscover.xml` - Microsoft
- `https://domain.com/.well-known/autoconfig/mail/config-v1.1.xml` - Mozilla

## Konfiguration

`/var/www/autodiscover/website/config.php`:
- IMAP Server (Host, Port, SSL/STARTTLS)
- SMTP Server (Host, Port, SSL/STARTTLS)
- Authentifizierung

## Support

- Alle ispconfig 3.x Versionen
- PHP 7.4+
- Postfix + Dovecot
