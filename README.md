# ispconfig Autodiscover Service

Ein professioneller Autodiscover-Service für ispconfig mit Unterstützung für Microsoft Autodiscover und Mozilla Autoconfig. Ermöglicht Mail-Clients (Thunderbird, Outlook, K-9 Mail, etc.) automatische Server-Konfiguration.

## Features

- ✅ **Microsoft Autodiscover** (`/autodiscover/autodiscover.xml`)
- ✅ **Mozilla Autoconfig** (`/.well-known/autoconfig/mail/config-v1.1.xml`)
- ✅ **ispconfig Integration** über REST API
- ✅ **Per-Domain Features** - aktivierbar/deaktivierbar
- ✅ **Postfix + Dovecot** Unterstützung
- ✅ **Caching** für Performance
- ✅ **Responsive & Fehlersicher**

## Installation

### Anforderungen

- PHP 7.4+
- cURL Extension (für ispconfig API)
- ispconfig Server
- Web-Server mit rewrite support

### Setup

1. **Repository klonen:**
```bash
git clone https://github.com/xerolux/ispconfig_autodiscover.git /var/www/autodiscover
cd /var/www/autodiscover
```

2. **Konfiguration erstellen:**
```bash
cp config/config.example.php config/config.php
# config.php anpassen (ispconfig API Credentials, etc.)
```

3. **Webserver konfigurieren:**

**Apache (.htaccess wird bereitgestellt):**
```bash
a2enmod rewrite
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name domain.com;
    root /var/www/autodiscover;
    
    location ~ /\.well-known/autoconfig/ {
        try_files $uri @autoconfig;
    }
    
    location /autodiscover/ {
        try_files $uri @autodiscover;
    }
    
    location @autodiscover {
        rewrite ^/autodiscover/(.*)$ /public/autodiscover.php last;
    }
    
    location @autoconfig {
        rewrite ^/\.well-known/autoconfig/mail/(.*)$ /public/autoconfig.php last;
    }
}
```

## Verwendung

Autodiscover ist per Default aktiv für alle Domains. Kann pro Domain in ispconfig aktiviert/deaktiviert werden.

## Manuelles Testen

```bash
# Microsoft Autodiscover
curl -X POST -d '<?xml version="1.0"?><Autodiscover><Request><EMailAddress>user@domain.com</EMailAddress></Request></Autodiscover>' \
  https://domain.com/autodiscover/autodiscover.xml

# Mozilla Autoconfig  
curl https://domain.com/.well-known/autoconfig/mail/config-v1.1.xml?emailaddress=user@domain.com
```

## Konfiguration

Siehe `config/config.example.php` für alle Optionen.

## API-Endpunkte

- `/autodiscover/autodiscover.xml` - Microsoft Autodiscover
- `/.well-known/autoconfig/mail/config-v1.1.xml` - Mozilla Autoconfig

## ispconfig Integration

Der Service queriert die ispconfig REST API für Domain- und Mail-Server-Informationen.