# Installation Guide - ispconfig Autodiscover Service

## 1. System-Anforderungen

- **PHP:** 7.4 oder höher
- **Extensions:** cURL, SimpleXML
- **Web-Server:** Apache mit mod_rewrite ODER Nginx
- **ispconfig:** 3.x mit REST API Zugriff
- **SSL/TLS:** Gültiges Zertifikat (für Clients erforderlich)

## 2. Installation

### Schritt 1: Repository klonen

```bash
# Als root oder mit sudo
cd /var/www
git clone https://github.com/xerolux/ispconfig_autodiscover.git autodiscover
cd autodiscover
chmod 755 public cache logs
```

### Schritt 2: Konfiguration

```bash
cp config/config.example.php config/config.php
chmod 600 config/config.php
nano config/config.php
```

**Wichtige Einstellungen:**

```php
'ispconfig' => [
    'api_url' => 'https://your-ispconfig-server:8080/api/index.php',
    'api_username' => 'admin',
    'api_password' => 'admin_password',
    'verify_ssl' => true, // false nur für Self-Signed Certs
],

'mail' => [
    'imap' => [
        'host' => 'mail.example.com',
        'port' => 993,
        'security' => 'SSL',
    ],
    'smtp' => [
        'host' => 'mail.example.com',
        'port' => 465,
        'security' => 'SSL',
        'auth_required' => true,
    ],
],
```

### Schritt 3: Web-Server konfigurieren

#### Apache

```bash
a2enmod rewrite
systemctl restart apache2
```

Virtual Host Konfiguration:

```apache
<VirtualHost *:443>
    ServerName domain.com
    ServerAlias *.domain.com autodiscover.domain.com
    
    DocumentRoot /var/www/autodiscover/public
    
    <Directory /var/www/autodiscover>
        AllowOverride All
        Require all granted
    </Directory>
    
    SSLEngine on
    SSLCertificateFile /path/to/cert.crt
    SSLCertificateKeyFile /path/to/key.key
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 443 ssl;
    server_name domain.com *.domain.com autodiscover.domain.com;
    
    root /var/www/autodiscover;
    index index.php;
    
    ssl_certificate /path/to/cert.crt;
    ssl_certificate_key /path/to/key.key;
    
    # Autodiscover endpoints
    location ~ /autodiscover/autodiscover\.xml {
        try_files $uri @autodiscover;
    }
    
    location ~ /\.well-known/autoconfig/mail/config-v1\.1\.xml {
        try_files $uri @autoconfig;
    }
    
    location @autodiscover {
        rewrite ^/autodiscover/(.*)$ /public/autodiscover.php last;
    }
    
    location @autoconfig {
        rewrite ^/\.well-known/autoconfig/mail/(.*)$ /public/autoconfig.php last;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Schritt 4: Berechtigungen

```bash
chown -R www-data:www-data /var/www/autodiscover
chmod 755 /var/www/autodiscover
chmod 700 /var/www/autodiscover/config
chmod 755 /var/www/autodiscover/cache
chmod 755 /var/www/autodiscover/logs
```

### Schritt 5: Verzeichnisse erstellen

```bash
mkdir -p /var/www/autodiscover/cache
mkdir -p /var/www/autodiscover/logs
chmod 755 /var/www/autodiscover/{cache,logs}
```

## 3. Testing

### Autodiscover Test

```bash
curl -X POST -d '<?xml version="1.0"?><Autodiscover><Request><EMailAddress>user@domain.com</EMailAddress></Request></Autodiscover>' \
  -H "Content-Type: application/xml" \
  https://domain.com/autodiscover/autodiscover.xml
```

### Autoconfig Test

```bash
curl "https://domain.com/.well-known/autoconfig/mail/config-v1.1.xml?emailaddress=user@domain.com"
```

### Browser Test

- Microsoft: `https://domain.com/autodiscover/autodiscover.xml`
- Mozilla: `https://domain.com/.well-known/autoconfig/mail/config-v1.1.xml?domain=domain.com`

## 4. Troubleshooting

### "Configuration file not found"

```bash
cp config/config.example.php config/config.php
# Dann bearbeiten
nano config/config.php
```

### ispconfig API Fehler

```bash
# Debug einschalten in config.php
'debug' => true,

# Log anschauen
tail -f logs/autodiscover.log
```

**Credentials prüfen:**
- ispconfig Admin-Account in config.php
- API erlaubt? ispconfig >> System >> API >> Aktiviert?

### Rewrite Rules funktionieren nicht (Apache)

```bash
# mod_rewrite ist aktiviert?
a2enmod rewrite

# .htaccess wird gelesen?
# VirtualHost muss "AllowOverride All" haben

# Test:
curl -v https://domain.com/autodiscover/autodiscover.xml
# Sollte nicht 404 sein
```

### Nginx 404 Fehler

```bash
# Nginx Config prüfen
nginx -t

# Reload
systemctl reload nginx

# Logs:
tail -f /var/log/nginx/error.log
```

## 5. Production Checklist

- [ ] SSL/TLS Zertifikat gültig
- [ ] config.php gesichert (600 Berechtigungen)
- [ ] ispconfig API credentials korrekt
- [ ] Mail-Server erreichbar von Autodiscover-Server
- [ ] DNS SRV Records erstellt (optional)
- [ ] Caching aktiviert
- [ ] Logs monitored
- [ ] Backup erstellt

## 6. DNS Records (Optional)

```
_autodiscover._tcp.domain.com    IN SRV 0 0 443 autodiscover.domain.com
autodiscover.domain.com          IN A   YOUR.IP.ADDRESS
```

## 7. Firewall / Security

- Nur Port 443 (HTTPS) erlaubt
- Autodiscover-Subdomain gegen brute-force schützen (mod_evasive/fail2ban)
- ispconfig API Port nur für Autodiscover-Server erreichbar

## 8. Updates

```bash
cd /var/www/autodiscover
git pull origin main
# Keine config.php-Änderungen, diese wird ignoriert
```
